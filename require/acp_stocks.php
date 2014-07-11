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
class Stocks
{
	public function getStockList()
	{
		global $baseScript, $_G;
		$stname		= $_G['gp_stname'];
		$search		= $_G['gp_search'];
		$stnamechk	= $_G['gp_stnamechk'];
		$page		= $_G['gp_page'];
		if ( $search == '1' )
		{
			if ( $stname == '' )
			{
				$baseScript .= '&mod=stockset';
				cpmsg('��������Ҫ���ҵĹ�Ʊ����', $baseScript, 'error');
			}
			else
			{
				$sql = "$stname";
			}
			if ( $stnamechk == '1' )
				$sql = "WHERE stockname='$stname'";
			else
				$sql = "WHERE stockname LIKE '%$stname%'";
		}
		else
		{
			$sql = '';
		}
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfss_stock')." $sql");
		$readperpage = 30;
		if ( $cnt > 0 )
		{
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
			$pages = foxpage($page, $numofpage, "?$baseScript&mod=stockset&");
			if ( $page > $numofpage )
			{
				$page = $numofpage;
				$start-=1;
			}
			$start = ( $page - 1 ) * $readperpage;
			$stockdb = array();
			$query = DB::query("SELECT * FROM ".DB::table('kfss_stock')." $sql LIMIT $start,$readperpage");
			while ( $rs = DB::fetch($query) )
			{
				$rs['openprice']	= number_format($rs['openprice'],2);
				$rs['currprice']	= number_format($rs['currprice'],2);
				if ( $rs['state'] == 0 )
					$rs['state'] = '����';
				else if ( $rs['state'] == 1 )
					$rs['state'] = '<font color="#808080">ͣ��</font>';
				else if ( $rs['state'] == 2 )
					$rs['state'] = '<font color="#FF0000">��ͣ</font>';
				else if ( $rs['state'] == 3 )
					$rs['state'] = '<font color="#008000">��ͣ</font>';
				else if ( $rs['state'] == 4 )
					$rs['state'] = '<font color="#0000FF">�¹�</font>';
				$stockdb[] = $rs;
			}
		}
		return array($stockdb,$cnt,$readperpage,$pages);
	}
	public function getStockInfo($code)
	{
		global $_G;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfss_stock')." WHERE code='$code'");
		if ( !$rs )
		{
			cpmsg('û���ҵ�ָ�������й�˾', '', 'error');
		}
		else
		{
			$rs['openprice'] = number_format($rs['openprice'],2);
			$rs['currprice'] = number_format($rs['currprice'],2);
			$rs['highprice'] = number_format($rs['highprice'],2);
			$rs['lowprice'] = number_format($rs['lowprice'],2);
			$ifselected = false;
			$capath = opendir('source/plugin/simstock/image/ca/');
			while ( $cafile = readdir($capath) )
			{
				if ( preg_match('/\.(jpg)$/i',$cafile) )
				{
					if ( $cafile == $rs['comphoto'] )
					{
						$ifselected = true;
						$cas .= "<option value=\"$cafile\" selected>$cafile</option>";
					}
					else
					{
						$caselected = (!$ifselected && $cafile=='0.jpg') ? ' selected' : '';
						$cas .= "<option value=\"$cafile\"$caselected>$cafile</option>";
					}
				}
			}
			closedir($capath);
			loadcache('plugin');
			$db_introducemax	= $_G['cache']['plugin']['simstock']['introducemax'];
			return array($rs,$cas,$db_introducemax);
		}
	}
	public function updateStock()
	{
		global $baseScript, $_G;
		$code			= $_G['gp_code'];
		$stname			= $_G['gp_stname'];
		$stnameold		= $_G['gp_stnameold'];
		$openprice		= $_G['gp_openprice'];
		$currprice		= $_G['gp_currprice'];
		$lowprice		= $_G['gp_lowprice'];
		$highprice		= $_G['gp_highprice'];
		$issuenum		= $_G['gp_issuenum'];
		$todaybuynum	= $_G['gp_todaybuynum'];
		$todaysellnum	= $_G['gp_todaysellnum'];
		$todaytradenum	= $_G['gp_todaytradenum'];
		$comphoto		= $_G['gp_comphoto'];
		$comintro		= $_G['gp_comintro'];
		$locked			= $_G['gp_locked'];
		$baseScript .= "&mod=stockset&section=editstock&code=$code";
		if ( $openprice == '' || !is_numeric($openprice) )
			cpmsg('���̼۸������������', '', 'error');
		if ( $currprice == '' || !is_numeric($currprice) )
			cpmsg('��ǰ�۸������������', '', 'error');
		if ( $lowprice == '' || !is_numeric($lowprice) )
			cpmsg('��ͼ۸������������', '', 'error');
		if ( $highprice == '' || !is_numeric($highprice) )
			cpmsg('��߼۸������������', '', 'error');
		if ( $issuenum == '' || !is_numeric($issuenum) )
			cpmsg('������������������ֵ', '', 'error');
		if ( $todaybuynum == '' || !is_numeric($todaybuynum) )
			cpmsg('�����������������ֵ', '', 'error');
		if ( $todaysellnum == '' || !is_numeric($todaysellnum) )
			cpmsg('������������������ֵ', '', 'error');
		if ( $todaytradenum == '' || !is_numeric($todaytradenum) )
			cpmsg('���ճɽ�����������ֵ', '', 'error');
		loadcache('plugin');
		$db_esnamemin		= $_G['cache']['plugin']['simstock']['esnamemin'];
		$db_esnamemax		= $_G['cache']['plugin']['simstock']['esnamemax'];
		$db_introducemax 	= $_G['cache']['plugin']['simstock']['introducemax'];
		$rs = DB::result_first("SELECT * FROM ".DB::table('kfss_stock')." WHERE code='$code'");
		if ( !$rs )
			cpmsg('û���ҵ�ָ�������й�˾', '', 'error');
		if ( $stname == '' || strlen($stname) < $db_esnamemin || strlen($stname) > $db_esnamemax )
			cpmsg("��Ʊ���Ƴ��Ȳ���С�� {$db_esnamemin} �ֽڻ��ߴ��� {$db_esnamemax} �ֽ�", '', 'error');
		if ( $comphoto == '' )
			cpmsg('��˾��Ƭ����Ϊ��', '', 'error');
		if ( $comintro == '' || strlen($comintro) > $db_introducemax )
			cpmsg("��˾��鲻��Ϊ�ջ򳬹� $db_introducemax �ֽ�", '', 'error');
		if ( $locked <> '0' && $locked <> '1' )
			cpmsg('��Ʊ״̬����', '', 'error');
		if ( $stname<>$stnameold )
		{
			$baseScript .= "&mod=stockset&section=editstock&code=$code";
			$rs = DB::result_first("SELECT stockname FROM ".DB::table('kfss_stock')." WHERE stockname='$stname'");
			$rs && cpmsg('��Ʊ�����ѱ�������Ʊʹ��', $baseScript, 'error');
			$rs = DB::result_first("SELECT stockname FROM ".DB::table('kfss_apply')." WHERE stockname='$stname' AND state<>2");
			$rs && cpmsg('��Ʊ�����ѱ�������Ʊʹ��', $baseScript, 'error');
		}
		$openprice	= str_replace(',', '', $openprice);
		$currprice	= str_replace(',', '', $currprice);
		DB::query("UPDATE ".DB::table('kfss_stock')." SET stockname='$stname', issuenum='$issuenum', openprice='$openprice', currprice='$currprice', lowprice='$lowprice', highprice='$highprice', todaybuynum='$todaybuynum', todaysellnum='$todaysellnum', todaytradenum='$todaytradenum', comphoto='$comphoto', comintro='$comintro', state='$locked' WHERE code='$code'");
		$logContent = "�༭��Ʊ��{$stnameold}����Ϣ";
		if ( $stname <> $stnameold )
		{
			$logContent .= "�������޸�Ϊ $stname";
		}
		DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username2, descrip, timestamp, ip) VALUES('��Ʊ����', '{$_G[username]}', '$logContent', '$_G[timestamp]', '$_G[clientip]')");
		cpmsg('��Ʊ��Ϣ�޸����', $baseScript, 'succeed');
	}
	public function deleteStock($code)
	{
		$rs = DB::fetch_first("SELECT code,stockname FROM ".DB::table('kfss_stock')." WHERE code='$code'");
		if ( !$rs )
			cpmsg('û���ҵ�ָ���Ĺ�Ʊ��¼', '' ,'error');
		else
			return $rs;
	}
	public function exeDeleteStock()
	{
		global $baseScript, $_G;
		$code		= $_G['gp_code'];
		$logtxt		= $_G['gp_logtxt'];
		$messagetxt	= $_G['gp_messagetxt'];
		$capital	= $_G['gp_capital'];
		$use		= $_G['gp_use'];
		$stockName	= DB::query("SELECT stockname FROM ".DB::table('kfss_stock')." WHERE code='$code'");
		$baseScript .= "&mod=stockset&section=delstock&code=$code";
		if ( empty($stockName) )
		{
			cpmsg('δ�ҵ���Ҫɾ���Ĺ�Ʊ', '', 'error');
		}
		else
		{
			if ( empty($logtxt) || strlen($logtxt) > 250 )
			{
				cpmsg('�������ɲ���Ϊ�գ��ҳ��Ȳ��ܳ��� 250 �ֽڣ�', $baseScript, 'error');
			}
			else
			{
				if ( $capital == 1 )
					$delstock = '���׳���Ʊ��';
				else
					$delstock = '��ע����Ʊ��';
				$logtxt = "ɾ����Ʊ {$stockName}��" . $logtxt.$delstock;
			}
			if ( $use == '1' && ( empty($messagetxt) || strlen($messagetxt) > 250 ) )
				cpmsg('��ѡ����ʹ����Ϣ�������ܣ�����д��Ϣ���ݣ��䳤�ȿ����� 250 �ֽ�����', $baseScript, 'error');
			if ( $capital == '1' )
			{
				$query = DB::query("SELECT uid, stocknum FROM ".DB::table('kfss_customer')." WHERE code='$code'");
				while ( $rc = DB::fetch($query) )
				{
					$mystnum		= 0;
					$mystcost		= 0;
					$mystvalue		= 0;
					$totalfund		= 0;
					$query = DB::query("SELECT c.*, s.currprice FROM ".DB::table('kfss_customer')." c INNER JOIN ".DB::table('kfss_stock')." s ON c.code=s.code WHERE c.uid={$rc[uid]} AND c.code<>'$code'");
					while ( $rsst = DB::fetch($query) )
					{
						$mystnum	= $mystnum + $rsst['stocknum'];
						$mystcost	= $mystcost + $rsst['stocknum'] * $rsst['averageprice'];
						$mystvalue	= $mystvalue + $rsst['stocknum'] * $rsst['currprice'];
						$totalfund	= $totalfund + $rsst['stocknum'] * $rsst['currprice'];
					}
					$addmoney = round($rc['stocknum']*$rs['currprice'],3);
					DB::query("UPDATE ".DB::table('kfss_user')." SET capital=capital+'$addmoney', asset=capital+".round($totalfund,3).", stocksort=stocksort-1, stocknum='$mystnum', stockcost='$mystcost', stockvalue='$mystvalue' WHERE uid='{$rc['uid']}'");
				}
			}
			DB::query("DELETE FROM ".DB::table('kfss_customer')." WHERE code='$code'");
			DB::query("DELETE FROM ".DB::table('kfss_stock')." WHERE code='$code'");
			if ( $use == '1' )
				DB::query("INSERT INTO ".DB::table('kfss_news')." (content,color,addtime) VALUES('$messagetxt','#FF0000','$_G[timestamp]')");
			DB::query("INSERT INTO ".DB::table('kfss_smlog')." (type, username2, descrip, timestamp, ip) VALUES('��Ʊ����','{$_G[username]}','$logtxt','$_G[timestamp]','$_G[clientip]')");
			$kfsclass = new kfsclass;
			$kfsclass->resetuid();
			$baseScript .= '&mod=stockset';
			cpmsg('��Ʊɾ���ɹ�', $baseScript, 'succeed');
		}
	}
	public function resetuid()
	{
		$query = DB::query("SELECT code FROM ".DB::table('kfss_stock')." ORDER BY code");
		$uid = 1;
		while ( $rs = DB::fetch($query) )
		{
			DB::query("UPDATE ".DB::table('kfss_stock')." SET uid='$uid' WHERE code='$rs[code]'");
			$uid++;
		}
	}
}
?>
