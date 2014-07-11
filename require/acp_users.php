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
class Users
{
	public function getUserList()
	{
		global $baseScript, $_G;
		$username		= $_G['gp_username'];
		$search			= $_G['gp_search'];
		$usernamechk	= $_G['gp_usernamechk'];
		$page			= $_G['gp_page'];
		if ( $search  == '1' )
		{
			if ( $username == '' )
			{
				cpmsg('��������Ҫ���ҵĹ�������', '', 'error');
			}
			else
			{
				if ( $usernamechk == '1' )
					$sql = "WHERE username='$username'";
				else
					$sql = "WHERE username LIKE '%$username%'";
			}
		}
		else
		{
			$sql = '';
		}
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfss_user')." $sql");
		$readperpage = 30;
		if ( $cnt > 0 )
		{
			if ( $page < 1 )
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
			$pages = foxpage($page, $numofpage, "$baseScript&mod=userset&");
			$userdb = array();
			$query = DB::query("SELECT * FROM ".DB::table('kfss_user')." $sql LIMIT $start,$readperpage");
			while ( $rs = DB::fetch($query) )
			{
				if ( $rs['locked'] == 0 )
					$rs['locked']	= '<font color="#008000">����</font>';
				else
					$rs['locked']	= '<font color="#FF0000">����</font>';
				$rs['capital'] = number_format($rs['capital'],2);
				$rs['asset'] = number_format($rs['asset'],2);
				$userdb[] = $rs;
			}
		}
		return array($userdb, $cnt, $readperpage, $pages);
	}
	public function editUser($uid)
	{
		global $baseScript;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
		if ( !$rs )
		{
			$baseScript .= '&mod=userset';
			cpmsg('û���ҵ�ָ���Ĺ���', $baseScript, 'error');
		}
		$rs['capital']		= number_format($rs['capital'],2);
		$rs['capital_ava']	= number_format($rs['capital_ava'],2);
		$rs['asset']		= number_format($rs['asset'],2);
		$rs['stocksort']	= intval($rs['stocksort']);
		$rs['todaybuy']		= intval($rs['todaybuy']);
		$rs['todaysell']	= intval($rs['todaysell']);
		if ( $rs['locked'] == 1 )
		{
			$userlock = 'checked';
		}
		else
		{
			$userunlock = 'checked';
		}
		return array($rs, $userlock, $userunlock);
	}
	public function updateUser()
	{
		global $baseScript, $_G;
		$uid		= $_G['gp_uid'];
		$username	= $_G['gp_username'];
		$capital	= $_G['gp_capital'];
		$capital_ava= $_G['gp_capital_ava'];
		$asset		= $_G['gp_asset'];
		$stocksort	= $_G['gp_stocksort'];
		$todaybuy	= $_G['gp_todaybuy'];
		$todaysell	= $_G['gp_todaysell'];
		$userstate	= $_G['gp_userstate'];
		$capital	= str_replace(',','',$capital);
		$capital_ava= str_replace(',','',$capital_ava);
		$asset		= str_replace(',','',$asset);
		$rs = DB::fetch_first("SELECT username FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
		if ( !$rs )
			cpmsg('û���ҵ�ָ���Ĺ���', '', 'error');
		if ( $capital == '' || !is_numeric($capital) )
			cpmsg('�ʻ��ʽ������������', '', 'error');
		if ( $capital_ava == '' || !is_numeric($capital_ava) )
			cpmsg('�����ʽ������������', '', 'error');
		if ( $asset == '' || !is_numeric($asset) )
			cpmsg('���ʲ�������������', '', 'error');
		if ( $stocksort == '' || !is_numeric($stocksort) || $stocksort < 0 )
			cpmsg('�ֹ����������һ���Ǹ���', '', 'error');
		if ( $todaybuy == '' || !is_numeric($todaybuy) || $todaybuy < 0 )
			cpmsg('�������������һ���Ǹ���', '', 'error');
		if ( $todaysell == '' || !is_numeric($todaysell) || $todaysell < 0 )
			cpmsg('��������������һ���Ǹ���', '', 'error');
		DB::query("UPDATE ".DB::table('kfss_user')." SET capital='$capital', capital_ava='$capital_ava', asset='$asset', stocksort='$stocksort', todaybuy='$todaybuy', todaysell='$todaysell', locked='$userstate' WHERE uid='$uid'");
		DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('�û�����', '$username', '{$_G[username]}', '�༭���� {$rs['username']} ��Ϣ', '$_G[timestamp]', '$_G[clientip]')");
		$baseScript .= "&mod=userset&section=edituser&uid=$uid";
		cpmsg('������Ϣ�޸����', $baseScript, 'succeed');
	}
	public function deleteUser($uid)
	{
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
		if ( !$rs )
			cpmsg('û���ҵ�ָ�����û�', '', 'error');
		else
			return $rs;
	}
	public function exeDeleteUser()
	{
		global $baseScript, $_G;
		$uid		= $_G['gp_uid'];
		$reason		= $_G['gp_reason'];
		$baseScript .= '&mod=userset';
		$rs = DB::fetch_first("SELECT username FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
		if ( !$rs )
		{
			cpmsg('δ�ҵ���Ҫɾ�����û�', $baseScript, 'error');
		}
		else
		{
			if ( empty($reason) || strlen($reason) > 250 )
				cpmsg('�������ɲ���Ϊ�գ��ҳ��Ȳ��ܴ��� 250 �ֽ�', '', 'error');
			DB::query("DELETE FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
			DB::query("DELETE FROM ".DB::table('kfss_customer')." WHERE uid='$uid'");
			DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('�û�����', '$rs[username]', '{$_G[username]}', '$reason', '$_G[timestamp]', '$_G[clientip]')");
			$baseScript = 'act=userset';
			cpmsg('ɾ���û��ɹ�', $baseScript, 'succeed');
		}
	}
}
?>
