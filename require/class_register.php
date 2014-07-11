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
class Register
{
	private $passed = false;
	private $foxinfo = array();
	public function show_reg_form()
	{
		global $baseScript, $_G, $user, $db_smname, $db_minmoney, $db_mincredit, $db_minpost, $db_allowregister, $db_credittype, $hkimg;
		if ( !$user['id'] )
		{
			if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
				$creditid	= $db_credittype;
			else
				$creditid	= $_G['setting']['creditstrans'];
			$credit = $_G['setting']['extcredits'][$creditid];
			$this->foxinfo['money']['type']	= $credit['title'];
			$this->foxinfo['money']['unit']	= $credit['unit'];
			$this->foxinfo['money']['num']	= getuserprofile('extcredits'.$creditid) ? getuserprofile('extcredits'.$creditid) : 0;
			$this->foxinfo['posts']['num']	= getuserprofile('posts') ? getuserprofile('posts') : 0;
			$this->foxinfo['mobile'] = getuserprofile('mobile');
			$sechash = 'S'.$_G['sid'];
			$ask = 0;
			if ( $this->foxinfo['money']['num'] < $db_minmoney )
			{
				$this->foxinfo['m'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['m'] = 1;
			}
			if ( $_G['member']['credits'] < $db_mincredit )
			{
				$this->foxinfo['c'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['c'] = 1;
			}
			if ( $this->foxinfo['posts']['num'] < $db_minpost )
			{
				$this->foxinfo['p'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['p'] = 1;
			}
			//�Ƿ��ֻ���
			if ( empty($this->foxinfo['mobile']) )
			{
				$this->foxinfo['b'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['b'] = 1;
			}
			if ( $ask < 4 )
				$this->passed = false;
			else
				$this->passed = true;
			include template('simstock:register');exit;
		}
		else
		{
			showmessage('���ڹ������Ѿ������������ظ�ע�ᣡ');
		}
   	}
	public function create_account()
	{
		global $baseScript, $_G, $db_allowregister, $db_minmoney, $db_mincredit, $db_minpost, $db_smname, $db_credittype, $db_initialmoney;
	   	if ( !is_numeric($_G['uid']) || $_G['uid'] <= 0 )
	   	{
			showmessage('�ο��޷��ڹ��п��������¼��̳', "$baseScript");
		}
		else
		{
			if ( $db_allowregister <> '1' )
			{
				showmessage('��������ͣ����');
			}
			else
			{
				$userId = DB::result_first("SELECT uid FROM ".DB::table('kfss_user')." WHERE forumuid='$_G[uid]'");
				if ( $userId )
				{
					showmessage('���ڹ������Ѿ������������ظ�ע�ᣡ');
				}
				else
				{
					if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
						$creditid	= $db_credittype;
					else
						$creditid	= $_G['setting']['creditstrans'];
					$credit = $_G['setting']['extcredits'][$creditid];
					$this->foxinfo['money']['type']	= $credit['title'];
					$this->foxinfo['money']['unit']	= $credit['unit'];
					$this->foxinfo['money']['num']	= getuserprofile('extcredits'.$creditid) ? getuserprofile('extcredits'.$creditid) : 0;
					$this->foxinfo['mobile'] = getuserprofile('mobile');
					if ( $this->foxinfo['money']['num'] < $db_minmoney )
					{
						showmessage("�������������˿�������{$this->foxinfo['money']['type']}Ϊ $db_minmoney {$this->foxinfo['money']['unit']}�������ڲ�����Ҫ����ʱ���ܿ���");
					}
					if ( $_G['member']['credits'] < $db_mincredit )
					{
						showmessage("�������������˿������ٻ���Ϊ $db_mincredit �������ڲ�����Ҫ����ʱ���ܿ���");
					}
					if ( getuserprofile('posts') < $db_minpost )
					{
						showmessage("�������������˿������ٷ�����Ϊ $db_minpost ����������Ҫ����ʱ���ܿ���");
					}
					if ( empty($this->foxinfo['mobile']) )
					{
						showmessage("�����б�����ֻ�������ܿ��� ����������Ҫ����ʱ���ܿ���");
					}
					
					DB::query("INSERT INTO ".DB::table('kfss_user')." (forumuid, username, fund_ini, fund_ava, fund_war, fund_last, regtime, lasttradetime, locked) VALUES('$_G[uid]', '$_G[username]', '$db_initialmoney', '$db_initialmoney', '0', '$db_initialmoney', '$_G[timestamp]', '$_G[timestamp]', '0')");
					showmessage("���п����ɹ���<br /> ".$db_smname." ��ӭ���ĵ����������͸��� ".$db_initialmoney." Ԫ�����ʽ�", "$baseScript");
				}
			}
		}
	}
}
?>
