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
class Tools
{
	public function kfsmReset()
	{
		global $baseScript;
		$kfsclass = new kfsclass;
		$kfsclass->kfssReset();
		$baseScript .= '&mod=tools';
		cpmsg('股市重新启动成功', $baseScript, 'succeed');
	}
	public function udRank()
	{
		global $baseScript;
		$kfsclass = new kfsclass;
		$kfsclass->updateRank();
		$baseScript .= '&mod=tools';
		cpmsg('大赛榜单更新成功', $baseScript, 'succeed');
	}
	public function newSeason()
	{
		global $_G, $baseScript;
		$baseScript .= '&mod=tools';
		
		$kfsclass = new kfsclass;
		$kfsclass->updateRank();
		$current_season_id = 1;
		$current_season_seq = 1;
		$qs = DB::query("SELECT sequence, id FROM ".DB::table('kfss_season')." ORDER BY sequence DESC LIMIT 0,1");
		if ( $rs = DB::fetch($qs) ) {
			if($rs['sequence']>0) {
				$current_season_seq = $rs['sequence'];
				$current_season_id = $rs['id'];
			}
		}
		
		DB::query("INSERT INTO ".DB::table('kfss_seasonlog')." (seasonid, forumuid, rank, profit, username, trade_times, trade_ok_times )
					SELECT '".$current_season_id."' as seasonid, forumuid, rank, profit, username, trade_times, trade_ok_times FROM ".DB::table('kfss_user')."
					WHERE profit >0 LIMIT 0,10");
		
		$count_users = 0;
		$qu = DB::query("SELECT count(uid) as cnt FROM ".DB::table('kfss_user'));
		if ( $rsu = DB::fetch($qu) )
			$count_users = $rsu['cnt'];
			
		DB::query("UPDATE ".DB::table('kfss_season')." SET users={$count_users}, endtime='$_G[timestamp]' WHERE id={$current_season_id}");
		
		DB::query("TRUNCATE TABLE ".DB::table('kfss_customer'));
		DB::query("TRUNCATE TABLE ".DB::table('kfss_deal'));
		DB::query("TRUNCATE TABLE ".DB::table('kfss_exclog'));
		DB::query("TRUNCATE TABLE ".DB::table('kfss_fundlog'));
		DB::query("TRUNCATE TABLE ".DB::table('kfss_order'));
		DB::query("UPDATE ".DB::table('kfss_sminfo')." SET ranktime=0, todaydate=0");
		DB::query("TRUNCATE TABLE ".DB::table('kfss_transaction'));
		DB::query("TRUNCATE TABLE ".DB::table('kfss_user'));
		
		$next_season_seq = $current_season_seq+1;
		$subject = '第'.$next_season_seq.'届炒股大赛';
		DB::query("INSERT INTO ".DB::table('kfss_season')." (sequence, subject, starttime, endtime, users )
					VALUES ( ".$next_season_seq.", '{$subject}', {$_G[timestamp]}, 0, 0 )");
		
		cpmsg('开启新赛季成功，所有用户数据已重置。', $baseScript, 'succeed');
	}
}
?>
