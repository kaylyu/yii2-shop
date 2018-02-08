
drop database if exists yii2_shop;
create database yii2_shop default character set utf8 collate utf8_unicode_ci;
use yii2_shop;

drop table if exists `shop_admin`;
create table `shop_admin` (
	`adminid` bigint UNSIGNED not null auto_increment,
	`adminuser` varchar(32) not null default '',
	`adminpass` char(64) not null default '',
	`adminemail` varchar(50) not null default '',
	`logintime`	int unsigned not null default 0,
	`loginip` bigint not null default 0,
	`createtime` int unsigned not null default 0,
	primary key(`adminid`),
	unique shop_admin_adminuser_adminpass(`adminuser`, `adminpass`),
	unique shop_admin_adminuser_adminemail(`adminuser`, `adminemail`)
)engine=InnoDB default charset=utf8;

insert into `shop_admin`(`adminuser`,`adminpass`,`adminemail`,`createtime`) values
('admin',md5('123'),'641753081@qq.com',UNIX_TIMESTAMP())
;

drop table if exists `shop_user`;
create table `shop_user` (
  `userid` bigint unsigned not null auto_increment,
  `username` varchar(32) not null default '',
  `userpass` char(64) not null default '',
  `useremail` varchar(64) not null default '',
  `createtime` int unsigned not null default 0,
  `uptime` int unsigned not null default 0,
  `loginip` bigint not null default 0,
  `openid` varchar(64) not null default '',
  primary key(`userid`),
  unique shop_user_username_userpass(`username`,`userpass`),
  unique shop_user_useremal_userpass(`useremail`,`userpass`)
)engine=InnoDB default charset=utf8;

drop table if exists `shop_profile`;
create table `shop_profile`(
	`id` bigint unsigned not null auto_increment comment '主键ID',
	`truename` varchar(32) not null default '',
	`age` tinyint unsigned not null default 0,
	`sex` enum('0','1','2') not null default '0',
	`birthday` date not null default '2017-06-17 10:59:36',
	`nickname` varchar(32) not null default '',
	`company` varchar(128) not null default '',
	`userid` bigint unsigned not null default 0,
	`createtime` int unsigned not null default 0,
	primary key(`id`),
	unique shop_profile_userid(`userid`)
)engine=InnoDB default charset=utf8;

drop table if exists `shop_category`;
create table `shop_category`(
  `cateid` bigint unsigned not null auto_increment,
  `title` varchar(32) not null DEFAULT '',
  `parentid` bigint unsigned not null DEFAULT 0,
  `createtime` int unsigned not null DEFAULT 0,
  `adminid` int unsigned not null DEFAULT  0,
  primary key (`cateid`),
  key shop_category_parentid(`parentid`)
)engine=InnoDB DEFAULT  charset=utf8;

drop table if exists `shop_product`;
create table `shop_product`(
  `productid` bigint unsigned not null auto_increment,
  `cateid` bigint unsigned not null DEFAULT 0,
  `title` varchar(200) not null DEFAULT '',
  `descr` text,
  `num` bigint unsigned not null DEFAULT 0,
  `price` decimal(10,2) not null DEFAULT '00000000.00',
  `cover` VARCHAR (200) not null default '',
  `pics` text,
  `issale` enum('0','1') not null DEFAULT '0',
  `saleprice` decimal(10,2) not null DEFAULT '00000000.00',
  `ishot` enum('0','1') not null DEFAULT '0',
  `ison` enum('0','1') not null DEFAULT '0',
  `istui` enum('0','1') not null DEFAULT '0',
  `createtime` int unsigned not null DEFAULT 0,
  `updatetime` int unsigned not null DEFAULT 0,
  PRIMARY key (`productid`),
  index shop_product_cateid(`cateid`)
)engine=InnoDB default charset=utf8;

drop table if exists `shop_cart`;
create table `shop_cart`(
  `cartid` bigint unsigned not null auto_increment,
  `productid` bigint unsigned not null DEFAULT 0,
  `productnum` int unsigned not null DEFAULT  0,
  `price` decimal(10,2) not null DEFAULT '00000000.00',
  `userid` bigint unsigned not null default 0,
  `createtime` int unsigned not null DEFAULT 0,
  `updatetime` int unsigned not null DEFAULT 0,
  PRIMARY key(`cartid`),
  index shop_cart_productid(`productid`),
  key shop_cart_userid(`userid`)
)engine=InnoDB DEFAULT charset=utf8;

drop table if exists `shop_order`;
create table `shop_order`(
  `orderid` bigint unsigned not null auto_increment,
  `userid` bigint unsigned not null DEFAULT 0,
  `addressid` bigint unsigned not null DEFAULT 0,
  `amount`  decimal(10,2) not null DEFAULT '0.00',
  `status`  int unsigned not null DEFAULT 0,
  `expressid` int unsigned NOT  null DEFAULT 0,
  `expressno` VARCHAR (50) not null DEFAULT '',
  `tradeno` VARCHAR (100) not null DEFAULT '',
  `tradeext` text,
  `createtime` int unsigned not null DEFAULT 0,
  `updatetime` timestamp not null DEFAULT CURRENT_TIMESTAMP on update current_timestamp,
  primary key(`orderid`),
  key shop_order_userid(`userid`),
  key shop_order_addressid(`addressid`),
  key shop_order_expressid(`expressid`)
)engine=InnoDB DEFAULT charset='utf8';

drop table if exists `shop_order_detail`;
create table `shop_order_detail`(
  `detailid` bigint unsigned not null auto_increment primary KEY ,
  `productid` bigint unsigned not null DEFAULT 0,
  `price` decimal (10,2) not null default '0.00',
  `productnum` int unsigned not null DEFAULT 0,
  `orderid` bigint not null DEFAULT 0,
  `createtime` int unsigned not null DEFAULT 0,
  index shop_order_detail_productid(`productid`),
  index shop_order_detail_orderid(`orderid`)
)engine=InnoDB default charset='utf8';

drop table if exists `shop_address`;
create table `shop_address`(
  `addressid` bigint unsigned not null auto_increment primary key,
  `firstname` VARCHAR (32) not null DEFAULT '',
  `lastname` VARCHAR (32) not null DEFAULT '',
  `company` VARCHAR (100) not null DEFAULT '',
  `address` text,
  `postcode` char(6) not null DEFAULT '',
  `email` VARCHAR (100) not null DEFAULT '',
  `telephone` VARCHAR (20) not null DEFAULT '',
  `userid` bigint unsigned not null DEFAULT 0,
  `createtime` int unsigned not null DEFAULT 0,
  index shop_address_userid(`userid`)
)engine=InnoDB default charset='utf8';
