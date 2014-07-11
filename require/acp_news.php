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
class News
{
	public function getNewsList()
	{
		global $db_otherpp, $baseScript;
		$newsdb = array();
		$query = DB::query("SELECT * FROM ".DB::table('kfss_news')." ORDER BY nid  DESC");
		$i = 0;
		while ( $rt = DB::fetch($query) )
		{
			$i++;
			$rt['order']	= $i;
			$rt['subject']	= cutstr(strip_tags($rt['subject']), 50);
			$rt['addtime']	= dgmdate($rt['addtime']);
			$rt['operate'] = "<a href=\"?$baseScript&mod=news&section=editnews&nid=$rt[nid]\">�༭</a>";
			$newsdb[] = $rt;
		}
		return $newsdb;
	}
	public function saveNewNews()
	{
		global $_G, $baseScript;
		$newsubject	= trim($_G['gp_newsubject']);
		$newcontent	= trim($_G['gp_newcontent']);
		if ( empty($newsubject) || empty($newcontent) )
		{
			cpmsg('���ű�����������ݲ���Ϊ��', '', 'error');
		}
		DB::query("INSERT INTO ".DB::table('kfss_news')."(subject, content, author, addtime) VALUES ('$newsubject', '$newcontent', '{$_G[username]}', '$_G[timestamp]')");
		$baseScript .= '&mod=news';
		cpmsg('������ӳɹ�', $baseScript, 'succeed');
	}
	public function getNewsInfo($nid)
	{
		$news = DB::fetch_first("SELECT * FROM ".DB::table('kfss_news')." WHERE nid='$nid'");
		return $news;
	}
	public function updateNews()
	{
		global $_G, $baseScript;
		$subject	= trim($_G['gp_subject']);
		$content	= trim($_G['gp_content']);
		if ( empty($subject) || empty($content) )
		{
			cpmsg('���ű�����������ݲ���Ϊ��', '', 'error');
		}
		DB::query("UPDATE ".DB::table('kfss_news')." SET subject='$subject', content='$content' WHERE nid='{$_G[gp_nid]}'");
		$baseScript .= '&mod=news';
		cpmsg('���Ÿ��³ɹ�', $baseScript, 'succeed');
	}
	public function deleteNews($nid)
	{
		global $baseScript;
		DB::query("DELETE FROM ".DB::table('kfss_news')." WHERE nid='$nid'");
		$baseScript .= '&mod=news';
		cpmsg('����ɾ���ɹ�', $baseScript, 'succeed');
	}
}
?>
