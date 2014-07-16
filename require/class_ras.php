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
class Ras
{
	public function __construct($section)
	{
		$section=='check' && self::checkOrder();
	}
	private static function checkOrder()
	{
		$rs = DB::fetch_first("SELECT stockcode FROM ".DB::table('kfss_sminfo')." WHERE id=1");
		if ( $rs['stockcode'] )
		{
			$p = strpos($rs['stockcode'],'|');
			if ( $p === false )
			{
				$code = $rs['stockcode'];
				$newCodes = '';
				$newCodes2 = $code;
			}
			else
			{
				$code = substr($rs['stockcode'], 0, $p);
				$newCodes = substr($rs['stockcode'], strpos($rs['stockcode'],'|')+1);
				$newCodes2 = $newCodes."|".$code;
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://hq.sinajs.cn/list=$code");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$str_js = curl_exec($ch);
			curl_close($ch);
			$data = substr(substr($str_js, 21), 0, -3);
			$data = explode(',',$data);
			if(self::udd($code,$data))
				DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$newCodes' WHERE id=1");
			else 
				DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$newCodes2' WHERE id=1");	// ������ܳɽ�����Ѵ˹�Ʊ���ڶ�����󣬵��´δ��
		}
	}
	private static function udd($code,$data)
	{
		global $_G;
		$currPrice = $data[3];
		$sellPrice = $data[7];	//��һ��
		$buyPrice = $data[6];	//��һ��
		
		if ( is_numeric($currPrice) && $currPrice > 0 )
		{
			$query = DB::query("SELECT * FROM ".DB::table('kfss_deal')." WHERE code='$code' AND ok='0' AND hide='0'");			
			while ( $drs = DB::fetch($query) )
			{
				$quantDeal = intval($drs['quant_deal']);
				if($quantDeal>10000){
					//udd log
					$remark = "QUERY:".$_SERVER['QUERY_STRING']." REF:".$_G['referer'];
					$exceptData = array(
						'uid'		=> $drs['uid'],
						'uname'		=> $drs['username'],
						'action'	=> $drs['direction'],	// ί������
						'stockcode'	=> $drs['code'],
						'amount'	=> $quantDeal,
						'price'		=> $drs['price_deal'],
						'logtime'	=> $_G['timestamp'],
						'ip'		=> $_G['clientip'],
						'remark'	=> $remark
					);
					DB::insert('kfss_exclog', $exceptData);
				}				
				
				if($quantDeal>1000000) return false;
				
				$worthDeal		= $drs['price_deal'] * $quantDeal;
				$worthLast		= $currPrice * $quantDeal;
				$commission		= $worthLast * 0.001;	// Ӷ����������ȡ
				$transferFee	= $worthLast * 0.001;	// �����ѣ���������ȡ
				$rsc = DB::fetch_first("SELECT cid, stocknum_ava, stocknum_war, averageprice FROM ".DB::table('kfss_customer')." WHERE uid='{$drs['uid']}' AND code='{$drs['code']}'");
				
				//��ͣ���Ʊ��������
				if ( $drs['direction'] == 1 && $drs['price_deal'] >= $currPrice && $sellPrice>0)
				{
					if ( !$rsc )
					{
						$priceCost = round( ($worthLast+$commission+$transferFee)/$quantDeal, 2 );
						$psData = array(
							'uid'			=> $drs['uid'],
							'username'		=> $drs['username'],
							'code'			=> $drs['code'],
							'stockname'		=> $drs['stockname'],
							'buyprice'		=> $priceCost,
							'averageprice'	=> $priceCost,
							'stocknum_ava'	=> 0,
							'stocknum_war'	=> $quantDeal,
							'buytime'		=> $_G['timestamp']
						);
						DB::insert('kfss_customer', $psData);
					}
					else
					{
						$leftNum	= $rsc['stocknum_ava'] + $rsc['stocknum_war'];
						$priceCost	= round( ( $currPrice * $quantDeal + $rsc['averageprice'] * $leftNum ) / ( $quantDeal + $leftNum), 2 );
						DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_war=stocknum_war+".$quantDeal.", buyprice='$priceCost', averageprice='$priceCost', buytime='{$_G[timestamp]}' WHERE cid='{$rsc['cid']}'");
					}
					//�����û����ϣ������ʽ������ʽ��ʻ���ֵ(fund_last ����+�����ʽ�+�ֹ���ֵ)
					DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava-$commission-$transferFee, fund_war=fund_war-{$worthDeal}, fund_stock=fund_stock+{$worthLast}, lasttradetime='{$_G['timestamp']}' WHERE uid='{$drs['uid']}'");
					DB::query("UPDATE ".DB::table('kfss_deal')." SET price_tran='{$currPrice}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='{$drs['did']}'");
					DB::query("INSERT INTO ".DB::table('kfss_transaction')."(uid, code, stockname, direction, quant, price, amount, did, ttime) VALUES('{$drs['uid']}', '{$drs['code']}', '{$drs['stockname']}', 1, '{$quantDeal}', '{$currPrice}', '$worthLast', '{$drs['did']}', '{$_G[timestamp]}')");
					return true;
				}
				//��ͣ���Ʊ��������
				else if ( $drs['direction'] == 2 && $drs['price_deal'] <= $currPrice && $buyPrice>0 )
				{
					$stocknum_remain = $rsc['stocknum_war'] - $quantDeal;
					if($stocknum_remain>=0) {
						$stampDuty = $worthLast * 0.001;	// ӡ��˰��������ȡ
						DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_war=".$stocknum_remain.", selltime='{$_G[timestamp]}' WHERE cid='{$rsc['cid']}'");
						$leftNum = DB::result_first("SELECT stocknum_ava FROM ".DB::table('kfss_customer')." WHERE cid='{$rsc['cid']}'");
						$trade_ok = $leftNum == 0 ? 1 : 0;
						DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$worthLast}-$commission-$transferFee-$stampDuty, fund_stock=fund_stock-{$worthLast}, lasttradetime='{$_G['timestamp']}', trade_ok_times=trade_ok_times+{$trade_ok} WHERE uid='{$drs['uid']}'");
						DB::query("UPDATE ".DB::table('kfss_deal')." SET quant_tran=".$quantDeal.", price_tran='{$drs['price_deal']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='{$drs['did']}'");
						DB::query("INSERT INTO ".DB::table('kfss_transaction')."(uid, code, stockname, direction, quant, price, amount, did, ttime) VALUES('{$drs['uid']}', '{$drs['code']}', '{$drs['stockname']}', 2, '{$quantDeal}', '{$currPrice}', '$worthLast', '{$drs['did']}', '{$_G[timestamp]}')");
					}
					return true;
				}
			}
		}		
		return false;
	}
}
?>
