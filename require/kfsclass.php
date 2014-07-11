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
class kfsclass
{
	public $version	= '1.0.0';
	public $build_date = '2011-09-21';
	public $website = '<a href="http://www.kilofox.net" target="_blank">www.Kilofox.Net</a>';
	public function auto_run()
	{
		global $_G;
		if ( $_G['adminid'] <> '1' )
			$this->checkMarketState();	//�رչ����ж�
		$td			= DB::fetch_first("SELECT todaydate, ranktime FROM ".DB::table('kfss_sminfo'));
		$lastDay	= dgmdate($td['todaydate'], 'd');
		$currDay	= dgmdate($_G['timestamp'], 'd');
		$currHour = dgmdate($_G['timestamp'], 'H');
		
		if ( $currHour >= 9 && $currHour <= 15 && dgmdate($td['ranktime'], 'H') <> $currHour ) // ����ʱ��ÿСʱ��������
		{
			self::updateRank($lastDay, $currDay);
		}
		if ( $lastDay <> $currDay && $currHour >= 15 && $_G['timestamp'] > $td['ranktime'] + 300 ) // ÿ�����к�ִ�� ���뱣֤updateRank()��ִ����� ��updateRank()5���Ӻ�ִ��
		{
			self::kfssReset();
		}
		require_once 'class_ras.php';
		new Ras('check');
	}
	private function checkMarketState()
	{
		global $_G, $db_smifopen, $db_whysmclose;
		if ( $db_smifopen == '1' )
		{
			showmessage($db_whysmclose);
		}
	}
	public static function kfssReset()
	{
		//����ϵͳ
		global $_G;
		loadcache('plugin');
		$db_trustlog	= $_G['cache']['plugin']['simstock']['trustlog'];
		$db_tradecharge	= $_G['cache']['plugin']['simstock']['tradecharge'];
		$db_stampduty	= $_G['cache']['plugin']['simstock']['stampduty'];
		DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode = '', todaydate='$_G[timestamp]'");
		DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+fund_war, fund_war=0");			// �ʽ����
		DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_ava=stocknum_ava+stocknum_war, stocknum_war=0 WHERE stocknum_war>0");	// ��Ʊ����
		DB::query("UPDATE ".DB::table('kfss_deal')." SET ok='4' WHERE hide='0' AND (ok='0' OR ok='2')");	// ϵͳ����
		DB::query("UPDATE ".DB::table('kfss_deal')." SET hide='1' WHERE hide='0'");							// ɾ���ɶ���
		$trustLogNum = is_numeric($db_trustlog) && $db_trustlog > 0 ? $db_trustlog*86400 : 2592000;
		DB::query("DELETE FROM ".DB::table('kfss_deal')." WHERE time_deal < $trustLogNum");
		DB::query("DELETE FROM ".DB::table('kfss_transaction')." WHERE ttime < $trustLogNum");
		DB::query("DELETE FROM ".DB::table('kfss_customer')." WHERE stocknum_ava=0 AND stocknum_war=0");
		
		//�ǽ����ղ�����
		$currentWeekDate = date('w');
		if($currentWeekDate==6 || $currentWeekDate ==7) return;
		//��һʱ��߼�
		$weekStartTime = strtotime(date("Y-m-d", strtotime("-".($currentWeekDate-1)." day")));
		$monthStartTime = strtotime(date( "Y-m")."-01");
		if((($t = strtotime(date("Y")."-10-01")) && $currentTime>=$t)
			|| (($t = strtotime(date("Y")."-07-01")) && $currentTime>=$t)
			|| (($t = strtotime(date("Y")."-04-01")) && $currentTime>=$t)
			|| (($t = strtotime(date("Y")."-01-01")) && $currentTime>=$t))
				$seasonStartTime = $t;
		$yearStartTime = strtotime(date( "Y")."-01-01");
		
		//������ʷ������Ϣ
		$qu = DB::query("SELECT uid, fund_ini, fund_ava, fund_stock, rank, profit FROM ".DB::table('kfss_user')." ORDER BY profit DESC LIMIT 0,200");
		while ( $rs = DB::fetch($qu) )
		{
			$current_total = $rs['fund_ava'] + $rs['fund_stock'];
			//���뵱�쾻ֵ
			DB::query("INSERT INTO ".DB::table('kfss_fundlog')." (uid, fund_current, rank, logtime ) VALUES ({$rs['uid']}, {$current_total}, {$rs['rank']}, {$_G[timestamp]})");
			
			//�ֱ���������桢�����桢���������������
			$profitD7Ratio = $rs['profit'];
			$profitM1Ratio = $rs['profit'];
			$profitM3Ratio = $rs['profit'];
			$profitY1Ratio = $rs['profit'];
			$d7max = 0;							//���������һ�ξ�ֵʱ��
			$m1max = 0;
			$m3max = 0;
			$y1max = 0;
			$max = 0;
			$logs = array();
			//��ȡ�û����о�ֵ��¼
			$qf = DB::query("SELECT fund_current, logtime FROM ".DB::table('kfss_fundlog')." WHERE uid='{$rs['uid']}' ORDER BY logtime ASC");
			$i = 0;
			while ( $rsc = DB::fetch($qf) )
			{
				if($i == 0) $max = $rsc['logtime'];
				$logs[$rsc['logtime']] = $rsc['fund_current'];
				if($rsc['logtime']<$yearStartTime) $y1max = $rsc['logtime'];
				if($rsc['logtime']<$seasonStartTime) $m3max = $rsc['logtime'];
				if($rsc['logtime']<$monthStartTime) $m1max = $rsc['logtime'];
				if($rsc['logtime']<$weekStartTime) $d7max = $rsc['logtime'];
				$i++;
			}
			if($d7max == 0) $d7max = $max;
			if(isset($logs[$d7max]))
				$profitD7Ratio = round(( $current_total - $logs[$d7max] ) / $logs[$d7max] * 100,2);
			if($m1max == 0) $m1max = $max;
			if(isset($logs[$m1max]))
				$profitM1Ratio = round(( $current_total - $logs[$m1max] ) / $logs[$m1max] * 100,2);
			if($m3max == 0) $m3max = $max;
			if(isset($logs[$m3max]))
				$profitM3Ratio = round(( $current_total - $logs[$m3max] ) / $logs[$m3max] * 100,2);
			if($y1max == 0) $y1max = $max;
			if(isset($logs[$y1max]))
				$profitY1Ratio = round(( $current_total - $logs[$y1max] ) / $logs[$y1max] * 100,2);

			//���µ��û���
			DB::query("UPDATE ".DB::table('kfss_user')." SET fund_last = {$current_total}, profit_d7='{$profitD7Ratio}', profit_m1='{$profitM1Ratio}', profit_m3='{$profitM3Ratio}', profit_y1='{$profitY1Ratio}' WHERE uid='{$rs['uid']}'");
			
		}
	}
	//������ld �ϴ�ִ��resetʱ�� cd ��ǰʱ��
	public static function updateRank($ld,$cd)
	{
		//��������
		//�����������ʸ�������
		global $_G;
		
		//��������ѽ��㾻ֵ ��kfssReset()��ִ�У����ٸ�������
		if($ld == $cd) return;		
				
		DB::query("UPDATE ".DB::table('kfss_sminfo')." SET ranktime='$_G[timestamp]'");
		
		//��ȡ���гֲֹ�Ʊ
		$userStock = array();
		$stocks = array();	//��Ҫ��ѯ��ǰ�۵Ĺ�Ʊ
		$qc = DB::query("SELECT * FROM ".DB::table('kfss_customer'));
		while ( $rsc = DB::fetch($qc) )
		{
			$userStock[$rsc['uid']][] = $rsc;
			$stocks[$rsc['code']] = true;
		}
		$stockPrice = self::getHqFromSina(array_keys($stocks));
		if(!$stockPrice) return;
		foreach($stockPrice as $price)
			$stocks[$price['code']] = $price['price']==0?$price['close_price']:$price['price'];

		$qu = DB::query("SELECT uid, fund_ini, fund_ava, fund_war, fund_last FROM ".DB::table('kfss_user')." ORDER BY profit DESC LIMIT 0,200");
		$ranks = array();	// ������Ϣ
		while ( $rs = DB::fetch($qu) )
		{
			$stockFund = 0;
			if(isset($userStock[$rs['uid']]))
			{				
				foreach ( $userStock[$rs['uid']] as $us )
				{
					$stockFund += ($us['stocknum_ava'] + $us['stocknum_war']) * $stocks[$us['code']];
				}
			}
			$total			= $stockFund + $rs['fund_ava'] + $rs['fund_war'];
			$profitRatio	= round(( $total - $rs['fund_ini'] ) / $rs['fund_ini'] * 100,2);
			if ( $rs['fund_last'] == 0 )
				$profitD1Ratio	= $profitRatio;
			else
				$profitD1Ratio	= round(( $total - $rs['fund_last'] ) / $rs['fund_last'] * 100,2);
				
			$ranks[$profitRatio.""] = array( 'uid' => $rs['uid'], 'd1' => $profitD1Ratio, 'sf' => $stockFund);
		}
		$i=1;
		foreach($ranks as $val=>$u) {
			DB::query("UPDATE ".DB::table('kfss_user')." SET rank='$i', profit='$val', profit_d1='{$u['d1']}', fund_stock='{$u['sf']}' WHERE uid='{$u['uid']}'");
			$i++;
		}
	}
	
	protected function getHqFromSina( $arrCode )
	{
		$url = "http://hq.sinajs.cn/list=".implode( ",", $arrCode );
		$output = file_get_contents($url);
		if(empty($output)) return false;
		preg_match_all("|_(.[^_]*)=\"(.[^\;]*)\"|", $output, $matches);
		$arrOut= array();
		for($i=0; $i<count($matches[2]); $i++) {
			
			if(!empty($matches[2][$i])) {
				$arrParts = explode( ",", $matches[2][$i]);
				$arrOut[$i]['code'] = $matches[1][$i];
				$arrOut[$i]['price'] = $arrParts[3];
				$arrOut[$i]['open_price'] = $arrParts[1];
				$arrOut[$i]['close_price'] = $arrParts[2];
				$arrOut[$i]['max'] = $arrParts[4];
				$arrOut[$i]['min'] = $arrParts[5];
				$arrOut[$i]['volume'] = $arrParts[8];
				$arrOut[$i]['turnover'] = $arrParts[9];
			}
		}
		if(count($arrOut)>0) return $arrOut;
		else return false;
	}
}
function foxpage( $page, $numofpage, $url )
{
	$total = $numofpage;
	if ( $numofpage <= 1 || !is_numeric($page) )
	{
		return;
	}
	else
	{
		$pages = "<div class=\"pg\">";
		$flag = 0;
		for ( $i=$page-3; $i<=$page-1; $i++ )
		{
			if ( $i<1 ) continue;
			$pages.="<a href=\"{$url}page=$i\">$i</a>";
		}
		$pages.="<strong>$page</strong>";
		if ( $page < $numofpage )
		{
			for ( $i=$page+1; $i<=$numofpage; $i++ )
			{
				$pages.="<a href=\"{$url}page=$i\">$i</a>";
				$flag++;
				if ( $flag==4 ) break;
			}
		}
		$pages.="<a href=\"{$url}page=$numofpage\" class=\"nxt\">... {$total}</a></div><span class=\"pgb y\"><a href=\"{$url}page=1\">1 ...</a></span>";
		return $pages;
	}
}
?>
