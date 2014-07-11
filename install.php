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
$version = '1.0.0';
$sql = <<<EOF
DROP TABLE IF EXISTS pre_kfss_customer;
CREATE TABLE pre_kfss_customer (
  cid mediumint(8) unsigned zerofill NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(20) NOT NULL default '',
  `code` char(8) NOT NULL default '',
  stockname varchar(20) NOT NULL default '',
  stocknum_ava int(20) unsigned NOT NULL default '0',
  stocknum_war int(20) unsigned NOT NULL default '0',
  buyprice decimal(14,2) unsigned NOT NULL default '0.00',
  averageprice decimal(14,2) unsigned NOT NULL default '0.00',
  buytime int(10) unsigned NOT NULL default '0',
  selltime int(10) unsigned NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (cid),
  KEY uid (uid,`code`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfss_deal;
CREATE TABLE pre_kfss_deal (
  did smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  `code` char(8) NOT NULL default '',
  stockname varchar(20) NOT NULL DEFAULT '',
  price_deal decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant_deal int(10) unsigned NOT NULL DEFAULT '0',
  time_deal int(10) unsigned NOT NULL DEFAULT '0',
  price_tran decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant_tran int(10) unsigned NOT NULL DEFAULT '0',
  time_tran int(10) unsigned NOT NULL DEFAULT '0',
  direction tinyint(1) NOT NULL DEFAULT '0',
  ok tinyint(1) NOT NULL DEFAULT '0',
  hide tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (did)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfss_exclog;
CREATE TABLE pre_kfss_exclog (
  lid int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  uname varchar(20) NOT NULL DEFAULT '',
  action tinyint(1) NOT NULL DEFAULT '0',
  stockcode char(8) NOT NULL DEFAULT '',
  amount int(14) unsigned NOT NULL DEFAULT '0',
  price decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  logtime int(10) unsigned NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (lid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfss_news;
CREATE TABLE pre_kfss_news (
  nid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL DEFAULT '',
  content mediumtext,
  color char(6) NOT NULL DEFAULT '',
  author varchar(15) NOT NULL DEFAULT '',
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (nid)
) ENGINE=MyISAM;

INSERT INTO pre_kfss_news (subject, content, color, author, addtime) VALUES ('欢迎使用 Kilofox SimStock V{$version} for Discuz! X2', '欢迎使用千狐 SimStock 虚拟股市系统！\nSimStock 是一款运用面向对象思想编写的 PHP 软件，是专为国内主流 PHP 论坛而开发的插件产品。该版本为 Discuz! 插件版。\n获得更多资讯，请您关注官方网站——[url=http://www.kilofox.net]Kilofox.Net[/url]', '', 'Kilofox.Net', '{$_G[timestamp]}');


DROP TABLE IF EXISTS pre_kfss_sminfo;
CREATE TABLE pre_kfss_sminfo (
  id smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  stockcode mediumtext,
  todaydate int(10) NOT NULL DEFAULT '0',
  ranktime int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

INSERT INTO pre_kfss_sminfo (id, todaydate, ranktime) VALUES(1, '{$_G[timestamp]}', '{$_G[timestamp]}');


DROP TABLE IF EXISTS pre_kfss_transaction;
CREATE TABLE pre_kfss_transaction (
  tid int(10) unsigned NOT NULL AUTO_INCREMENT,
  code char(8) NOT NULL DEFAULT '',
  stockname varchar(20) NOT NULL DEFAULT '',
  direction tinyint(1) unsigned NOT NULL DEFAULT '0',
  did int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  price decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant int(10) unsigned NOT NULL DEFAULT '0',
  amount decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  ttime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfss_user;
CREATE TABLE pre_kfss_user (
  uid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  forumuid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  fund_ini decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  fund_ava decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  fund_war decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  fund_last decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  profit decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  profit_d1 decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  profit_d5 decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  trade_times smallint(6) unsigned NOT NULL DEFAULT '0',
  trade_ok_times smallint(6) unsigned NOT NULL DEFAULT '0',
  rank tinyint(3) unsigned NOT NULL DEFAULT '0',
  regtime int(10) unsigned NOT NULL DEFAULT '0',
  lasttradetime int(10) unsigned NOT NULL DEFAULT '0',
  locked boolean NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_kfss_order;
CREATE TABLE pre_kfss_order (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid int(11) unsigned NOT NULL,
  tid int(11) unsigned NOT NULL,
  type tinyint(2) unsigned DEFAULT '0',
  status tinyint(1) DEFAULT '0',
  expires int(10) unsigned DEFAULT NULL,
  utime int(11) unsigned DEFAULT NULL,
  ctime int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_kfss_orderlog;
CREATE TABLE pre_kfss_orderlog (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  uid INT(11) UNSIGNED NOT NULL ,
  tid INT(11) UNSIGNED NOT NULL ,
  price DECIMAL(8,2) UNSIGNED NOT NULL DEFAULT 0.00 ,
  ok TINYINT(1) NOT NULL DEFAULT 0 ,
  ordertime INT(10) UNSIGNED NULL ,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_kfss_fundlog;
CREATE  TABLE pre_kfss_fundlog (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  uid INT(11) NOT NULL ,
  fund_current DECIMAL(14,2) NOT NULL DEFAULT '0.00' ,
  rank tinyint(3) unsigned NOT NULL DEFAULT '0',
  logtime INT(10) NOT NULL ,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

ALTER TABLE pre_kfss_user CHANGE COLUMN `profit` `profit` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  ,
		CHANGE COLUMN `profit_d1` `profit_d1` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  ,
		CHANGE COLUMN `profit_d5` `profit_d5` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  ;

ALTER TABLE pre_kfss_user ADD COLUMN `profit_d7` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  AFTER `profit_d5` ,
		ADD COLUMN `profit_m1` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  AFTER `profit_d7` ,
		ADD COLUMN `fund_stock` DECIMAL(14,2) UNSIGNED NOT NULL DEFAULT '0.00'  AFTER `fund_last`;

DROP TABLE IF EXISTS pre_kfss_season;
CREATE  TABLE pre_kfss_season (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  sequence MEDIUMINT(8) NOT NULL DEFAULT 0 ,
  subject VARCHAR(100) NOT NULL DEFAULT '' ,
  starttime INT(10) UNSIGNED NOT NULL ,
  endtime INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
  users INT(10) NOT NULL DEFAULT 0 ,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

INSERT INTO pre_kfss_season (sequence, subject, starttime, endtime, users) VALUES(1, '首届炒股大赛', '{$_G[timestamp]}', 0, 0);

DROP TABLE IF EXISTS pre_kfss_seasonlog;
CREATE  TABLE pre_kfss_seasonlog (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  seasonid INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  forumuid INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  rank TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 ,
  profit DECIMAL(14,2) UNSIGNED NOT NULL DEFAULT '0.00' ,
  username VARCHAR(20) NOT NULL DEFAULT '' ,
  trade_times SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0 ,
  trade_ok_times SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0 ,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

ALTER TABLE `pre_kfss_user` ADD COLUMN `profit_m3` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  AFTER `profit_m1` ,
		ADD COLUMN `profit_y1` DECIMAL(14,2) NOT NULL DEFAULT '0.00'  AFTER `profit_m3` ;

CREATE  TABLE pre_kfss_smlog (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  type TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 ,
  username2 VARCHAR(20) NOT NULL ,
  descrip VARCHAR(255) NULL ,
  timestamp INT(10) UNSIGNED NOT NULL ,
  ip VARCHAR(20) NOT NULL ,
  PRIMARY KEY (id)
);

EOF;
runquery($sql);
$finish = TRUE;
?>
