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
class Trust
{
	public function __construct( $member, $section, $uid=0 )
	{
		$this->process( $member, $section, $uid );
	}
	private function process( $user, $section, $uid )
	{
		if ( empty($section) )
			$this->showMyDeals( $user );
		else if ( $section == 'tran' )
			$this->showMyTrans( $user );
		else if ( $section == 'trade' )
			$this->stockTrade( $user );
		else if ( $section == 'canceltt' )
		{
			global $_G;
			$this->cancelDeal( $user, $_G['gp_did'] );
		}
	}
	private function stockTrade( $user )
	{
		global $kfsclass, $_G, $db_usertrade;
		if ( $db_usertrade == '1' )
		{
			if ( $_G['gp_code'] && $_G['gp_tradetype'] == 'b' )
				$this->buyStock($user, $_G['gp_code'], $_G['gp_price_buy'], $_G['gp_num_buy']);
			else if ( $_G['gp_code'] && $_G['gp_tradetype'] == 's' )
				$this->sellStock($user, $_G['gp_code'], $_G['gp_price_sell'], $_G['gp_num_sell']);
			else
				$this->showTradeForm($user, $_G['gp_code']);
		}
		else
			showmessage('��ʱֹͣ���ף������Ժ�����');
	}
	private function showMyDeals( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qd = DB::query("SELECT * FROM ".DB::table('kfss_deal')." WHERE uid='{$user['uid']}' ORDER BY did DESC");
		while ( $rsd = DB::fetch($qd) )
		{
			if ( $rsd['direction'] == 1 )
				$rsd['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rsd['direction'] == 2 )
				$rsd['direction'] = '<span style="color:#008000">����</span>';
			else
				$rsd['direction'] = '<span style="color:#0000FF">�쳣</span>';
			if ( $rsd['time_deal'] )
				$rsd['time_deal']	= dgmdate($rsd['time_deal'],'Y-m-d H:i:s');
			else
				$rsd['time_deal']	= '-';
			if ( $rsd['ok'] == 0 )
			{
				$rsd['ok'] = 'δ�ɽ�';
				$rsd['op'] = "<form name=\"form1\" action=\"$baseScript&mod=member&act=trustsmng\" method=\"post\"><input type=\"hidden\" name=\"section\" value=\"canceltt\" /><input type=\"hidden\" name=\"did\" value=\"$rsd[did]\" /><button type=\"submit\" name=\"submit\" value=\"true\" class=\"pn pnc\"><em>����</em></button></form>";
			}
			else if ( $rsd['ok'] == 1 )
			{
				$rsd['ok'] = '<span style="color:#008000">�ɽ�</span>';
				//$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			else if ( $rsd['ok'] == 2 )
			{
				$rsd['ok'] = '<span style="color:#FFA500">���ֳɽ�</span>';
				$rsd['op'] = "<form name=\"form1\" action=\"$baseScript&mod=member&act=trustsmng\" method=\"post\"><input type=\"hidden\" name=\"section\" value=\"canceltt\" /><input type=\"hidden\" name=\"did\" value=\"$rsd[did]\" /><button type=\"submit\" name=\"submit\" value=\"true\" class=\"pn pnc\"><em>����</em></button></form>";
			}
			else if ( $rsd['ok'] == 3 )
			{
				$rsd['ok'] = '<span style="color:#0000FF">�û�����</span>';
				//$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			else if ( $rsd['ok'] == 4 )
			{
				$rsd['ok'] = '<span style="color:#A52A2A">ϵͳ����</span>';
				//$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			else
			{
				$rsd['ok'] = '<span style="color:#FF0000">�쳣</span>';
				//$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			$ddb[] = $rsd;
		}
		include template('simstock:member_trustsmng');
	}
	private function showMyTrans( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qt = DB::query("SELECT * FROM ".DB::table('kfss_transaction')." WHERE uid='{$user['uid']}' ORDER BY tid DESC");
		while ( $rst = DB::fetch($qt) )
		{
			if ( $rst['direction'] == 1 )
				$rst['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rst['direction'] == 2 )
				$rst['direction'] = '<span style="color:#008000">����</span>';
			else
				$rst['direction'] = '<span style="color:#0000FF">�쳣</span>';
			if ( $rst['ttime'] )
				$rst['ttime']	= dgmdate($rst['ttime'],'Y-m-d H:i:s');
			else
				$rst['ttime']	= '-';
			$tdb[] = $rst;
		}
		include template('simstock:member_trustsmng');
	}
	private function cancelDeal( $user, $deal_id )
	{
		global $baseScript, $db_dutyrate, $db_dutymin, $kfsclass;
		$qd = DB::fetch_first("SELECT * FROM ".DB::table('kfss_deal')." WHERE did='$deal_id' AND uid='{$user['uid']}'");
		if ( $qd )
		{
			$quantLeft = $qd['quant_deal'] - $qd['quant_tran'];
			if ( $quantLeft > 0 && $qd['hide'] == 0 )
			{
				if ( $qd['ok'] == 0 && $qd['quant_deal'] == $quantLeft )
				{
					$worth	= $qd['price_deal'] * $qd['quant_deal'];
				}
				else if ( $qd['ok'] == 2 )
				{
					$worth	= $qd['price_deal'] * $quantLeft;		//���ֳɽ�
				}
				else
				{
					showmessage('��ί�е�����״̬�쳣���޷�������');
				}
				if ( $qd['direction'] == 1 )
				{					
					// �޸�Bug��û�м�ȥ�����ʽ�
					DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$worth}, fund_war=fund_war-{$worth} WHERE uid='{$user['uid']}'");
					DB::query("UPDATE ".DB::table('kfss_deal')." SET ok='3' WHERE did='{$qd[did]}'");
					showmessage('ί�������Ʊ�����ɹ���', "$baseScript&mod=member&act=trustsmng");
				}
				else if ( $qd['direction'] == 2 )
				{
					DB::query("UPDATE ".DB::table('kfss_deal')." SET ok='3' WHERE did='{$qd[did]}'");
					// �޸�Bug: ����û�нⶳ��Ʊ
					DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_war=stocknum_war-{$quantLeft}, stocknum_ava=stocknum_ava+{$quantLeft} WHERE uid='{$user['uid']}' AND code='{$qd['code']}' ");
					showmessage('ί��������Ʊ�����ɹ���', "$baseScript&mod=member&act=trustsmng");
				}
				else
				{
					showmessage('��ί�е����������쳣���޷�������');
				}
			}
			else
			{
				showmessage('��ί�е���ȫ���ɽ������ѹ��ڣ��޷�������');
			}
		}
		else
		{
			showmessage('��Ч��ί�е���');
		}
	}
}
?>
