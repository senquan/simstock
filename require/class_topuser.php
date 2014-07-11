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
class Topuser
{
	public function showTopUser()
	{
		global $baseScript, $_G, $db_smname, $db_marketpp, $hkimg, $page;
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfss_user'));
		if ( $cnt > 0 )
		{
			$readperpage = is_numeric($db_marketpp) && $db_marketpp > 0 ? $db_marketpp : 20;
			$page = $_G['gp_page'];
			$sort = $_G['gp_sort'];
			if ( $page <= 1 )
			{
				$page = 1;
				$start = 0;
			}
			$numofpage = ceil($cnt/$readperpage);
			if ( $page > $numofpage )
			{
				$page = $numofpage;
				$start-=1;
			}
			$start = ( $page - 1 ) * $readperpage;
			$pages = foxpage($page,$numofpage,"$baseScript&mod=system&act=topuser&".(empty($sort)?"":"sort=".$sort."&"));
			//sort
			$d = "DESC";
			$sorticon = "<b class=\"icon_down\"></b>";
			if(substr($sort,0,1)=="_") {
				$d = "ASC";
				$sort = substr($sort,1);
				$sorticon = "<b class=\"icon_up\"></b>";
			}
			$sorts = array("profit","profit_d1","profit_d5","profit_d7","profit_m1","profit_m3","profit_y1","trade_times");
			if ( !in_array($sort, $sorts) ) $sort = "profit";
			$sortstr = $sort ." ". $d;
			
			$sorturl = "$baseScript&mod=system&act=topuser&".(empty($page)?"":"page=".$page."&")."sort=".($d=="DESC"?"_":"");
			
			$topdb = $this->getTopUser($start, $readperpage, $sortstr);
			$uorder = $this->getUserOrder($_G['uid']);
		}
		$ranktime = DB::result_first("SELECT ranktime FROM ".DB::table('kfss_sminfo')." WHERE id=1");
		$ranktime = dgmdate($ranktime);
		include template('simstock:topuser');
	}
	private function getTopUser($start=0, $readperpage, $sort="")
	{
		global $baseScript;
		$topdb = array();	
		if(empty($sort)) $sort = "profit DESC";	
		$query = DB::query("SELECT uid, forumuid, username, fund_ini, fund_ava, fund_war, fund_stock, profit, profit_d1, profit_d5, profit_d7, profit_m1, profit_m3, profit_y1, trade_times, trade_ok_times, rank
					FROM ".DB::table('kfss_user')."
					WHERE locked<>'1'
					ORDER BY ".$sort." LIMIT $start,$readperpage");
		while ( $rs = DB::fetch($query) )
		{
			$rs['profit_ratio']		= number_format($rs['profit'],2);
			$rs['hold_ratio']		= number_format($rs['fund_stock']/($rs['fund_ava']+$rs['fund_war']+$rs['fund_stock'])*100,2);
			$rs['profit_d1_ratio']	= number_format($rs['profit_d1'],2);
			$rs['profit_d5_ratio']	= number_format($rs['profit_d5'],2);
			$rs['profit_d7_ratio']	= number_format($rs['profit_d7'],2);
			$rs['profit_m1_ratio']	= number_format($rs['profit_m1'],2);
			$rs['profit_m3_ratio']	= number_format($rs['profit_m3'],2);
			$rs['profit_y1_ratio']	= number_format($rs['profit_y1'],2);
			$rs['trade_ok_ratio']	= $rs['trade_ok_times']/$rs['trade_times']*100;
			$topdb[] = $rs;
		}
		return $topdb;
	}
	//获取用户订阅信息
	private function getUserOrder($uid)
	{
		$uorder = array();
		$query = DB::query("SELECT tid, expires
							FROM ".DB::table('kfss_order')." a
							INNER JOIN ".DB::table('kfss_user')." b ON a.uid = b.uid
							WHERE a.status =1 AND b.forumuid = {$uid} AND a.expires > ".time());
		while ( $rs = DB::fetch($query) )
		{
			$uorder[$rs['tid']] = $rs['expires'];
		}
		return $uorder;
	}
}
?>
