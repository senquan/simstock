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
class Trusts
{
	public function getDealList()
	{
		global $db;
		$i = 0;
		$qd = DB::query("SELECT d.*, u.uid, u.username FROM ".DB::table('kfss_deal')." d LEFT JOIN ".DB::table('kfss_user')." u ON d.uid=u.uid ORDER BY d.did DESC");
		while ( $rsd = DB::fetch($qd) )
		{
			$i++;
			$rsd['no'] = $i;
			if ( $rsd['direction'] == 1 )
				$rsd['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rsd['direction'] == 2 )
				$rsd['direction'] = '<span style="color:#008000">����</span>';
			else
				$rsd['direction'] = '<span style="color:#0000FF">�쳣</span> <a href="http://www.kilofox.net" target="_blank">����</a>';
			if ( $rsd['time_deal'] )
				$rsd['time_deal'] = dgmdate($rsd['time_deal'],'Y-m-j H:i:s');
			else
				$rsd['time_deal'] = '-';
			if ( $rsd['time_tran'] )
				$rsd['time_tran'] = dgmdate($rsd['time_tran'],'Y-m-j H:i:s');
			else
				$rsd['time_tran'] = '-';
			if ( $rsd['ok'] == 0 )
				$rsd['ok'] = 'δ�ɽ�';
			else if ( $rsd['ok'] == 1 )
				$rsd['ok'] = '<span style="color:#008000">�ɽ�</span>';
			else if ( $rsd['ok'] == 2 )
				$rsd['ok'] = '<span style="color:#FFA500">���ֳɽ�</span>';
			else if ( $rsd['ok'] == 3 )
				$rsd['ok'] = '<span style="color:#0000FF">�û�����</span>';
			else if ( $rsd['ok'] == 4 )
				$rsd['ok'] = '<span style="color:#A52A2A">ϵͳ����</span>';
			else
				$rsd['ok'] = '<span style="color:#FF0000">�쳣</span> <a href="http://www.kilofox.net" target="_blank">����</a>';
			$ddb[] = $rsd;
		}
		return $ddb;
	}
	public function getTranList()
	{
		global $db;
		$i = 0;
		$qt = DB::query("SELECT t.*, u.uid, u.username FROM ".DB::table('kfss_transaction')." t LEFT JOIN ".DB::table('kfss_user')." u ON t.uid=u.uid ORDER BY t.tid DESC");
		while ( $rst = DB::fetch($qt) )
		{
			$i++;
			$rst['no'] = $i;
			if ( $rst['direction'] == 1 )
				$rst['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rst['direction'] == 2 )
				$rst['direction'] = '<span style="color:#008000">����</span>';
			else
				$rst['direction'] = '<span style="color:#0000FF">�쳣</span> <a href="http://www.kilofox.net" target="_blank">����</a>';
			if ( $rst['ttime'] )
				$rst['ttime'] = dgmdate($rst['ttime'],'Y-m-j H:i:s');
			else
				$rst['ttime'] = '-';
			$tdb[] = $rst;
		}
		return $tdb;
	}
	public function deleteDeals()
	{
		global $baseScript, $_G;
		$did	= $_G['gp_did'];
		$value	= $_G['gp_value'];
		$ttlnum = count($did);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $did as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM ".DB::table('kfss_deal')." WHERE did IN ($delid)");
			DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username2, descrip, timestamp, ip) VALUES('ί�м�¼����', '{$_G[username]}', 'ɾ��ί�м�¼ {$ttlnum} ��', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=trusts';
		cpmsg("�ѳɹ�ɾ�� {$ttlnum} ��ί�м�¼��", $baseScript, 'succeed');
	}
	public function deleteTrans()
	{
		global $baseScript, $_G;
		$tid	= $_G['gp_tid'];
		$value	= $_G['gp_value'];
		$ttlnum = count($tid);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $tid as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM ".DB::table('kfss_transaction')." WHERE tid IN ($delid)");
			DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username2, descrip, timestamp, ip) VALUES('�ɽ���¼����', '{$_G[username]}', 'ɾ���ɽ���¼ {$ttlnum} ��', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=trusts';
		cpmsg("�ѳɹ�ɾ�� {$ttlnum} ���ɽ���¼��", $baseScript, 'succeed');
	}
	public function doEx()
	{
		global $baseScript, $_G;
		
		$code	= $_G['gp_code'];
		if(strlen($code)!=6) cpmsg('��Ʊ�������', '', 'error');
		$f = substr($code,0,1);
		if("6"==$f) $code = "sh".$code;
		else $code = "sz".$code;
		$exdate	= $_G['gp_exdate'];
		if(!empty($exdate)){
			$extime = dmktime($exdate);
			if($extime==0) cpmsg('��Ȩ��Ϣ���ڸ�ʽ����', '', 'error');
		}
		$stock	= $_G['gp_stock'];
		$cash	= $_G['gp_cash'];
		if ( !is_numeric($stock) )
			cpmsg('�͹���������������', '', 'error');
		if ( !is_numeric($cash) )
			cpmsg('�ֺ���������������', '', 'error');
		if( empty($cash) && empty($stock) )  cpmsg('�ֺ���͹ɲ���ͬʱΪ��', '', 'error');
		
		$exnum = 0;	
		$affect_customer = array();
		$qc = DB::query("SELECT cid, code, uid, stocknum_ava, stocknum_war, buyprice, averageprice FROM ".DB::table('kfss_customer')." WHERE code = '{$code}'");
		while ( $rst = DB::fetch($qc) )
		{
			if(!empty($exdate)){
				$td = DB::fetch_first("SELECT count(tid) cnt FROM ".DB::table('kfss_transaction')." WHERE code='{$rst['code']}' AND uid = {$rst['uid']}  AND ttime>".$extime);
				if($td['cnt']>0) cpmsg('�û��ڳ�Ȩ��Ϣ���й����ף�Ϊ�˱�֤������ȷ���������д˲�����', '', 'error');
			}
			$new_buyprice = $rst['buyprice'];
			$new_averageprice = $rst['averageprice'];
			$new_stocknum_ava = $rst['stocknum_ava'];
			$new_stocknum_war = $rst['stocknum_war'];
			if($stock>0) {
				
				$new_stocknum_ava = $rst['stocknum_ava'] + $rst['stocknum_ava']*$stock/10;
				$new_stocknum_war = $rst['stocknum_war'] + $rst['stocknum_war']*$stock/10;
				//
				$new_buyprice = ($rst['stocknum_ava']+$rst['stocknum_war'])*$rst['buyprice']/($new_stocknum_ava+$new_stocknum_war);
				$new_averageprice = ($rst['stocknum_ava']+$rst['stocknum_war'])*$rst['averageprice']/($new_stocknum_ava+$new_stocknum_war);
			}
			
			if($cash>0) {
				
				$addcash = ($rst['stocknum_ava'] + $rst['stocknum_ava'])*$cash/10;
				$new_buyprice = (($new_stocknum_ava+$new_stocknum_ava)*$new_buyprice+$addcash)/($new_stocknum_ava+$new_stocknum_ava);
				$new_averageprice = (($new_stocknum_ava+$new_stocknum_ava)*$new_averageprice+$addcash)/($new_stocknum_ava+$new_stocknum_ava);
				
				//�����˻����
				DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$addcash}, lasttradetime='{$_G['timestamp']}' WHERE uid='{$rst['uid']}'");
			}
			DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_ava={$new_stocknum_ava}, stocknum_war={$new_stocknum_war}, buyprice={$new_buyprice}, averageprice={$new_averageprice}, buytime='{$_G[timestamp]}' WHERE cid='{$rst['cid']}'");
			DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username2, descrip, timestamp, ip) VALUES('�ֺ��Ϣ����', '{$_G[username]}', '�Թ�Ʊ {$code} �ֺ��Ȩ���͹� {$stock}������ {$cash}��Ӱ���û��ֲ� {$rst['cid']}', '$_G[timestamp]', '$_G[clientip]')");
			$exnum++;
		}
		
		cpmsg("�ѳɹ��� {$exnum} ���û��ֲֽ��зֺ���Ϣ����", $baseScript, 'succeed');
	}
}
?>
