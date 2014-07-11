<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-09-21
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
!defined('IN_DISCUZ') && exit('Access Denied');
class Ajax
{
	public function __construct($section)
	{
		method_exists($this,$section) && $this->$section();
	}
	private function account()
	{
		global $_G;
		$uAccount	= self::getUserAccount($_G['gp_uid']);
		$uStocks	= self::getUserStocks($_G['gp_uid']);
		echo 'var o={account:'.$uAccount.', stockHold:'.$uStocks.', order:'.$uStocks.'};';
	}
	private function buy()
	{
		global $_G, $db_holdlimit;
		$retMsgs = '';
		if(!self::isActive()) $retMsgs = '非交易时段。';
		else {
			if ( is_numeric($_G['gp_uid']) && $_G['gp_uid'] > 0 )
			{
				$user = self::getUserInfo($_G['gp_uid']);
				$needMoney = $_G['gp_price'] * $_G['gp_amount'];
				
				if ( $user['fund_ava'] < $needMoney )
				{
					$retMsgs = '用户可用资金不足';
				}
				else if ($user['lasttradetime'] + 5 > time())
				{
					$retMsgs = '高频交易禁止。';
				}
				else
				{
					//单只股票持仓限制
					$tradeLimit = false;
					$stocks = self::getUserStocks($_G['gp_uid'], false);
					if(isset($stocks[$_G['gp_code']])){
						
						$stock_val = ($stocks[$_G['gp_code']]['stocknum_ava'] + $stocks[$_G['gp_code']]['stocknum_war']) * $_G['gp_price'];
						if($stock_val + $needMoney > self::getLimitVar($user)) $tradeLimit = true;
						
					}else if( $needMoney > self::getLimitVar($user) ) {						
						$tradeLimit = true;
					}					
					
					if($tradeLimit) {
						$retMsgs = '单只股票不能买入过多，仓位限制比例：'.$db_holdlimit.'%';
					}else{
						
						DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava-{$needMoney}, fund_war=fund_war+{$needMoney}, lasttradetime='{$_G['timestamp']}' WHERE uid='{$user[uid]}'");
						$dealData = array(
							'uid'		=> $user['uid'],
							'username'	=> $user['username'],
							'code'		=> $_G['gp_code'],
							'stockname'	=> mb_convert_encoding($_G['gp_stockname'],'gbk','utf-8'),
							'direction'	=> '1',
							'quant_deal'=> $_G['gp_amount'],
							'price_deal'=> $_G['gp_price'],
							'time_deal'	=> $_G['timestamp'],
							'ok'		=> '0'
						);
						DB::insert('kfss_deal', $dealData);
						// 异常交易监测
						if ( $_G['gp_amount'] > 10000 )
						{
							$exceptData = array(
								'uid'		=> $user['uid'],
								'uname'		=> $user['username'],
								'action'	=> '1',	// 委托买入
								'stockcode'	=> $_G['gp_code'],
								'amount'	=> $_G['gp_amount'],
								'price'		=> $_G['gp_price'],
								'logtime'	=> $_G['timestamp'],
								'ip'		=> $_G['clientip']
							);
							DB::insert('kfss_exclog', $exceptData);
						}
						$codes = DB::result_first("SELECT stockcode FROM ".DB::table('kfss_sminfo')." WHERE id=1");
						$codes = $codes ? $_G['gp_code'] . '|' . $codes   : $_G['gp_code'];
						DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$codes' WHERE id=1");
						$retMsgs = '0';
					}
				}
			}
			else
			{
				$retMsgs = '用户 ID 不存在';
			}
		}
		echo "var ret='$retMsgs';";
	}
	private function sell()
	{
		global $_G;
		$retMsgs = '';
		if(!self::isActive()) $retMsgs = '非交易时段。';
		else {
			if ( is_numeric($_G['gp_uid']) && $_G['gp_uid'] > 0 )
			{
				$user = self::getUserInfo($_G['gp_uid']);
				$si = DB::fetch_first("SELECT cid, stocknum_ava, buytime FROM ".DB::table('kfss_customer')." WHERE uid='{$_G['gp_uid']}' AND code='{$_G['gp_code']}'");
				if ( is_numeric($si['stocknum_ava']) && $si['stocknum_ava'] > 0 )
				{
					// 当天买入的股票直接锁定，不再根据买入时间判断是否可以卖出，修正Bug：当天加仓买入股票，全部股票不可卖。
					if ( false ) //dgmdate($si['buytime'], 'd') == dgmdate($_G['timestamp'], 'd')
					{
						$retMsgs = '执行 T+1 规则，当日买入股票，最早下一交易日才能卖出';
					}
					else
					{
						if ( $si['stocknum_ava'] < $_G['gp_amount'] )
						{
							$retMsgs = '您没有足够的股票卖出';
						}
						else
						{
							$dealData = array(
								'uid'		=> $user['uid'],
								'username'	=> $user['username'],
								'code'		=> $_G['gp_code'],
								'stockname'	=> mb_convert_encoding($_G['gp_stockname'],'gbk','utf-8'),
								'direction'	=> '2',
								'quant_deal'=> $_G['gp_amount'],
								'price_deal'=> $_G['gp_price'],
								'time_deal'	=> $_G['timestamp'],
								'ok'		=> '0'
							);
							DB::insert('kfss_deal', $dealData);
							// 异常交易监测
							if ( $_G['gp_amount'] > 10000 )
							{
								$exceptData = array(
									'uid'		=> $user['uid'],
									'uname'		=> $user['username'],
									'action'	=> '2',	// 委托卖出
									'stockcode'	=> $_G['gp_code'],
									'amount'	=> $_G['gp_amount'],
									'price'		=> $_G['gp_price'],
									'logtime'	=> $_G['timestamp'],
									'ip'		=> $_G['clientip']
								);
								DB::insert('kfss_exclog', $exceptData);
							}
							DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_ava=stocknum_ava-{$_G['gp_amount']}, stocknum_war=stocknum_war+{$_G['gp_amount']} WHERE cid='{$si['cid']}'");
							$leftNum = DB::result_first("SELECT stocknum_ava FROM ".DB::table('kfss_customer')." WHERE cid='{$si['cid']}'");
							if ( $leftNum == 0 )
							{
								DB::query("UPDATE ".DB::table('kfss_user')." SET trade_times=trade_times+1 WHERE uid='{$user['uid']}'");
							}
							$codes = DB::result_first("SELECT stockcode FROM ".DB::table('kfss_sminfo')." WHERE id=1");
							$codes = $codes ? $codes . '|' . $_G['gp_code'] : $_G['gp_code'];
							DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$codes' WHERE id=1");
							$retMsgs = '0';
						}
					}
				}
				else
				{
					$retMsgs = '卖出数量错误';
				}
			}
			else
			{
				$retMsgs = '用户 ID 不存在';
			}
		}
		echo "var ret='$retMsgs';";
	}
	private static function isActive() {
		
		//return true;
		global $db_spclosedate;
		$isActive = false;
		$activeWeek = array(1,2,3,4,5);
		$activeSection = array("9:15-11:30","13:00-15:00");
		
		$currentWeek = date('w');
		if(!in_array( $currentWeek, $activeWeek)) return false;
		
		$dateStr = date('Y-m-d');
		$currentTime = time();
		foreach($activeSection as $section ) {
			$arr = explode( "-", $section);
			$from = $arr[0];
			$to = $arr[1];
			
			$timeFrom = strtotime($dateStr." ".$from.":00");
			$timeTo = strtotime($dateStr." ".$to.":00");
			if( $timeFrom <= $currentTime && $currentTime <= $timeTo ){
				$isActive = true;
			}
		}
		
		if($isActive) {
			if(strpos($db_spclosedate, $dateStr)>-1) return false;
			else return true;
		} else return false;
	}
	private static function getUserAccount($uid=0)
	{
		$jsData = '""';
		if ( is_numeric($uid) && $uid > 0 )
		{
			$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
			if ( $rs )
				$jsData = '{InitFund:"'.$rs['fund_ini'].'", AvailableFund:"'.$rs['fund_ava'].'", WarrantFund:"'.$rs['fund_war'].'", LastTotalFund:"'.$rs['fund_last'].'", d5ProfitRatio:"'.$rs['profit_d5'].'", TotalRank:"'.$rs['rank'].'", ProfitSellRatio:"'.($rs['trade_ok_times']/$rs['trade_times']*100).'"}';
		}
		return $jsData;
    }
    private static function getUserStocks($uid=0,$jsdata=true)
    {
    	$holds = '';
    	if ( is_numeric($uid) && $uid > 0 )
		{
			$query = DB::query("SELECT * FROM ".DB::table('kfss_customer')." WHERE uid='$uid'");
			if($jsdata)
			{
				while ( $rs = DB::fetch($query) )
				{
					if ( $rs['stocknum_ava'] || $rs['stocknum_war'] )
					{
						$holds .= '{StockCode:"'.$rs['code'].'", StockName:"'.$rs['stockname'].'", StockAmount:"'.($rs['stocknum_ava']+$rs['stocknum_war']).'", AvailSell:"'.$rs['stocknum_ava'].'", CostFund:"'.$rs['averageprice'].'"},';
					}
				}
				$holds && $holds = substr($holds, 0, -1);
			}else{
				$arrHolds = array();
				while ( $rs = DB::fetch($query) )
				{
					$arrHolds[$rs['code']] = $rs;
				}
				return $arrHolds;
			}
		}
		
		$jsData .= '['.$holds.']';
		return $jsData;
	}
	private static function getUserInfo($uid=0)
	{
		$data = '';
		if ( is_numeric($uid) && $uid > 0 )
		{
			$data = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
		}
		return $data;
    }
	private static function getLimitVar($user) {
		global $db_holdlimit;
		$limit_ava = ($user['fund_ava'] + $user['fund_war'] + $user['fund_stock'])*$db_holdlimit/100;
		return $limit_ava;
	}
}
?>
