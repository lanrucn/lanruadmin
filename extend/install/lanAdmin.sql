-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2020-01-09 02:26:23
-- 服务器版本： 5.6.37-log
-- PHP Version: 7.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `lanruadmin`
--

-- --------------------------------------------------------

--
-- 表的结构 `lan_admin`
--

CREATE TABLE `lan_admin` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT '' COMMENT '管理员名',
  `password` varchar(255) DEFAULT '' COMMENT '密码',
  `salt` varchar(10) DEFAULT '' COMMENT '加密密钥',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1正常0禁用',
  `login_failure` int(11) DEFAULT '0' COMMENT '失败次数',
  `login_time` int(11) DEFAULT '0' COMMENT '最后登录时间',
  `login_ip` varchar(32) DEFAULT '' COMMENT '最后登录IP',
  `token` varchar(100) DEFAULT '',
  `addtime` int(11) DEFAULT '0' COMMENT '添加时间',
  `edittime` int(11) DEFAULT 0 COMMENT '编辑时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员表';

--
-- 转存表中的数据 `lan_admin`
--

INSERT INTO `lan_admin` (`id`, `name`, `password`, `salt`, `status`, `login_failure`, `login_time`, `login_ip`, `token`, `addtime`, `edittime`) VALUES
(1, 'admin', 'fc5d43f1ec8d72e099aeb6df99ca1397', 'UiuLMJ', 1, 0, 1578504471, '49.81.198.234', '4ed8d085-8109-4828-8bed-c9945d58f9de', 0, 1578504471);

-- --------------------------------------------------------

--
-- 表的结构 `lan_admin_group`
--

CREATE TABLE `lan_admin_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `pid` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT '' COMMENT '组名',
  `rules` text COMMENT '规则ID',
  `status` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '状态',
  `addtime` int(11) DEFAULT '0' COMMENT '创建时间',
  `edittime` int(11) DEFAULT '0' COMMENT ' 编辑时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员分组';

--
-- 转存表中的数据 `lan_admin_group`
--

INSERT INTO `lan_admin_group` (`id`, `pid`, `name`, `rules`, `status`, `addtime`, `edittime`) VALUES
(1, 0, '超级管理员', '*', 1, 1577166053, 1577166053),
(2, 1, '普通管理员', '1,2', 1, 1577166048, 1577294631);

-- --------------------------------------------------------

--
-- 表的结构 `lan_admin_group_access`
--

CREATE TABLE `lan_admin_group_access` (
  `admin_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `admin_group_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限分组表';

--
-- 转存表中的数据 `lan_admin_group_access`
--

INSERT INTO `lan_admin_group_access` (`admin_id`, `admin_group_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `lan_admin_log`
--

CREATE TABLE `lan_admin_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员ID',
  `name` varchar(255) DEFAULT '' COMMENT '管理员名字',
  `url` varchar(255) DEFAULT '' COMMENT '操作页面',
  `title` varchar(255) DEFAULT '' COMMENT '日志标题',
  `body` varchar(255) DEFAULT '' COMMENT '内容',
  `ip` varchar(50) DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) DEFAULT '' COMMENT 'User-Agent',
  `addtime` int(11) UNSIGNED ZEROFILL DEFAULT '00000000000' COMMENT '操作时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员日志表';

--
-- 表的结构 `lan_admin_rule`
--

CREATE TABLE `lan_admin_rule` (
  `id` int(11) UNSIGNED NOT NULL,
  `ismenu` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '0为菜单,1为权限节点',
  `pid` int(11) UNSIGNED DEFAULT '0' COMMENT '父ID',
  `name` varchar(255) DEFAULT '' COMMENT '规则名称',
  `rule` varchar(255) DEFAULT '' COMMENT '规则',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `weight` int(11) DEFAULT '100' COMMENT '权重',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `addtime` int(11) UNSIGNED DEFAULT '0',
  `edittime` int(11) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lan_admin_rule`
--

INSERT INTO `lan_admin_rule` (`id`, `ismenu`, `pid`, `name`, `rule`, `icon`, `remark`, `weight`, `status`, `addtime`, `edittime`) VALUES
(1, 1, 0, '控制台', 'index', 'fa fa-dashboard', '', 100, 1, 1577268828, 1577268828),
(2, 0, 1, '查看', 'index/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577270578),
(3, 1, 0, '系统配置', 'general', 'fa fa-cogs', '', 100, 1, 1577268828, 1577268828),
(4, 1, 3, '基本配置', 'general/config', 'fa fa-dashboard', '', 100, 1, 1577268828, 1577268828),
(5, 0, 4, '查看', 'general/config/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(6, 0, 4, '添加', 'general/config/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(7, 0, 4, '编辑', 'general/config/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(8, 0, 4, '添加', 'general/config/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(9, 0, 4, '删除', 'general/config/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(10, 1, 3, '附件管理', 'general/attachment', 'fa fa-file-image-o', '', 100, 1, 1577268828, 1577268828),
(11, 0, 10, '查看', 'general/attachment/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(12, 0, 10, '添加', 'general/attachment/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(13, 0, 10, '编辑', 'general/attachment/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(14, 0, 10, '添加', 'general/attachment/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(15, 0, 10, '删除', 'general/attachment/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(16, 1, 3, '个人设置', 'general/profile', 'fa fa-user', '', 100, 1, 1577268828, 1577268828),
(17, 0, 16, '查看', 'general/profile/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(18, 0, 16, '添加', 'general/profile/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(19, 0, 16, '编辑', 'general/profile/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(20, 0, 16, '添加', 'general/profile/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(21, 0, 16, '删除', 'general/profile/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(22, 1, 0, '权限管理', 'auth', 'fa fa-group', '', 100, 1, 1577268828, 1577268828),
(23, 1, 22, '管理员管理', 'auth/admin', 'fa fa-user', '', 100, 1, 1577268828, 1577268828),
(24, 0, 23, '查看', 'auth/admin/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(25, 0, 23, '添加', 'auth/admin/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(26, 0, 23, '编辑', 'auth/admin/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(27, 0, 23, '添加', 'auth/admin/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(28, 0, 23, '删除', 'auth/admin/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(29, 1, 22, '日志管理', 'auth/adminlog', 'fa fa-list-alt', '', 100, 1, 1577268828, 1577268828),
(30, 0, 29, '查看', 'auth/adminlog/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(31, 0, 29, '添加', 'auth/adminlog/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(32, 0, 29, '编辑', 'auth/adminlog/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(33, 0, 29, '添加', 'auth/adminlog/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(34, 0, 29, '删除', 'auth/adminlog/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(35, 1, 22, '管理角色', 'auth/group', 'fa fa-group', '', 100, 1, 1577268828, 1577268828),
(36, 0, 35, '查看', 'auth/group/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(37, 0, 35, '添加', 'auth/group/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(38, 0, 35, '编辑', 'auth/group/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(39, 0, 35, '添加', 'auth/group/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(40, 0, 35, '删除', 'auth/group/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(41, 1, 22, '菜单管理', 'auth/rule', 'fa fa-bars', '', 100, 1, 1577268828, 1577268828),
(42, 0, 41, '查看', 'auth/rule/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(43, 0, 41, '添加', 'auth/rule/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(44, 0, 41, '编辑', 'auth/rule/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(45, 0, 41, '添加', 'auth/rule/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(46, 0, 41, '删除', 'auth/rule/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(47, 1, 0, '会员管理', 'user', 'fa fa-list', '', 100, 1, 1577268828, 1578049083),
(48, 1, 47, '会员列表', 'user/user', 'fa fa-user', '', 100, 1, 1577268828, 1577268828),
(49, 0, 48, '查看', 'user/user/index', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(50, 0, 48, '添加', 'user/user/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(51, 0, 48, '编辑', 'user/user/edit', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(52, 0, 48, '添加', 'user/user/add', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(53, 0, 48, '删除', 'user/user/del', 'fa fa-circle-o', '', 100, 1, 1577268828, 1577268828),
(54, 1, 47, '会员组管理', 'user/group', 'fa fa-users', '', 100, 1, 1577268828, 1577268828),
(55, 0, 54, '查看', 'user/group/index', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(56, 0, 54, '添加', 'user/group/add', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(57, 0, 54, '编辑', 'user/group/edit', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(58, 0, 54, '添加', 'user/group/add', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(59, 0, 54, '删除', 'user/group/del', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(60, 1, 47, '会员权限', 'user/rule', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(61, 0, 60, '查看', 'user/rule/index', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(62, 0, 60, '添加', 'user/rule/add', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(63, 0, 60, '编辑', 'user/rule/edit', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(64, 0, 60, '添加', 'user/rule/add', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(65, 0, 60, '删除', 'user/rule/del', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(66, 1, 0, '插件管理', 'addon', 'fa fa-rocket', '', 100, 1, 1577268829, 1577268829),
(67, 0, 66, '查看', 'addon/index', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(68, 0, 66, '安装', 'addon/install', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(69, 0, 66, '配置', 'addon/config', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829),
(70, 0, 66, '删除', 'addon/del', 'fa fa-circle-o', '', 100, 1, 1577268829, 1577268829);

-- --------------------------------------------------------

--
-- 表的结构 `lan_attachment`
--

CREATE TABLE `lan_attachment` (
  `id` int(11) UNSIGNED NOT NULL,
  `admin_id` int(11) UNSIGNED DEFAULT '0' COMMENT '管理员ID',
  `user_id` int(11) UNSIGNED DEFAULT '0' COMMENT '会员ID',
  `url` varchar(255) DEFAULT '' COMMENT '物理路径',
  `type` varchar(30) DEFAULT '' COMMENT '类型',
  `filesize` int(10) UNSIGNED DEFAULT '0' COMMENT '文件大小',
  `sha1` varchar(100) DEFAULT '',
  `addtime` int(10) DEFAULT '0' COMMENT '创建日期',
  `edittime` int(10) UNSIGNED DEFAULT '0' COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='附件表\n';

--
-- 表的结构 `lan_command`
--

CREATE TABLE `lan_command` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `params` varchar(1500) NOT NULL DEFAULT '' COMMENT '参数',
  `command` varchar(1500) NOT NULL DEFAULT '' COMMENT '命令',
  `content` text COMMENT '返回结果',
  `executetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '执行时间',
  `createtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` enum('successed','failured') NOT NULL DEFAULT 'failured' COMMENT '状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='在线命令表' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `lan_config`
--

CREATE TABLE `lan_config` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text NOT NULL COMMENT '变量值',
  `content` text NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统配置' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `lan_config`
--

INSERT INTO `lan_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`) VALUES
(1, 'name', 'basic', '站点名称', '', 'string', '', '', '', ''),
(2, 'configgroup', 'dictionary', '配置分组', '', 'array', '{\"basic\":\"基础配置\",\"email\":\"邮件配置\",\"dictionary\":\"字典配置\",\"user\":\"会员配置\"}', '', '', ''),
(3, 'beian', 'basic', '备案号', '', 'string', '', '', '', ''),
(4, 'mail_type', 'email', '邮件发送方式', '选择邮件发送方式', 'select', '1', '[\"Please select\",\"SMTP\",\"Mail\"]', '', ''),
(5, 'mail_smtp_host', 'email', 'SMTP服务器', '错误的配置发送邮件会导致服务器超时', 'string', 'smtp.qq.com', '', '', ''),
(6, 'mail_smtp_port', 'email', 'SMTP端口', '(不加密默认25,SSL默认465,TLS默认587)', 'string', '465', '', '', ''),
(7, 'mail_smtp_user', 'email', 'SMTP用户名', '（填写完整用户名）', 'string', '10000', '', '', ''),
(8, 'mail_smtp_pass', 'email', 'SMTP密码', '（填写您的密码）', 'string', 'password', '', '', ''),
(9, 'mail_verify_type', 'email', 'SMTP验证方式', '（SMTP验证方式[推荐SSL]）', 'select', '2', '[\"None\",\"TLS\",\"SSL\"]', '', ''),
(10, 'mail_from', 'email', '发件人邮箱', '', 'string', '10000@qq.com', '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `lan_user`
--

CREATE TABLE `lan_user` (
  `id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED DEFAULT '1' COMMENT '组别ID',
  `name` varchar(60) DEFAULT '' COMMENT '用户名',
  `email` varchar(100) DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(30) DEFAULT '' COMMENT '手机号',
  `password` varchar(32) DEFAULT '' COMMENT '密码',
  `salt` char(6) DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `score` int(10) UNSIGNED DEFAULT '0' COMMENT ' 积分',
  `balance` double(10,2) UNSIGNED DEFAULT '0.00' COMMENT '余额',
  `level` int(10) UNSIGNED DEFAULT '0' COMMENT '等级',
  `gender` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '性别0未知 1男 2 女',
  `birthday` char(10) DEFAULT '' COMMENT '生日',
  `login_ip` varchar(32) DEFAULT '' COMMENT '登录IP',
  `login_time` int(10) UNSIGNED DEFAULT '0' COMMENT '登录时间',
  `prev_ip` varchar(32) DEFAULT '' COMMENT ' 上次登录 IP',
  `prev_time` int(10) UNSIGNED DEFAULT '0' COMMENT '上次登录时间',
  `join_ip` varchar(32) DEFAULT '' COMMENT '加入IP',
  `status` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '状态 0禁用 1正常',
  `successions` int(10) DEFAULT '1' COMMENT '连续登录天数',
  `maxsuccessions` int(10) UNSIGNED DEFAULT '1' COMMENT '最大连续登录天数',
  `addtime` int(10) UNSIGNED DEFAULT '0',
  `edittime` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_ems`
--

CREATE TABLE `lan_user_ems` (
  `id` int(11) UNSIGNED NOT NULL,
  `event` varchar(30) DEFAULT '' COMMENT '事件',
  `email` varchar(100) DEFAULT '' COMMENT '邮箱',
  `code` varchar(10) DEFAULT '' COMMENT '验证码',
  `times` int(10) UNSIGNED DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(50) DEFAULT '' COMMENT 'IP',
  `addtime` int(10) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_group`
--

CREATE TABLE `lan_user_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT '' COMMENT '组名',
  `icon` varchar(255) DEFAULT '' COMMENT '组别图标',
  `rules` text COMMENT '规则ID',
  `status` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '状态',
  `addtime` int(11) DEFAULT '0' COMMENT '创建时间',
  `edittime` int(11) DEFAULT '0' COMMENT ' 编辑时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员分组';

--
-- 转存表中的数据 `lan_user_group`
--

INSERT INTO `lan_user_group` (`id`, `name`, `icon`, `rules`, `status`, `addtime`, `edittime`) VALUES
(1, '普通会员', '/uploads/20200104/a56b3dbe9369436efe4d152f40233de2.jpg', '5,4,2,1', 1, 1578077564, 1578078263);

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_money_log`
--

CREATE TABLE `lan_user_money_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '0' COMMENT '会员ID',
  `balance` decimal(10,2) UNSIGNED DEFAULT '0.00' COMMENT '变更余额',
  `before` decimal(10,2) UNSIGNED DEFAULT '0.00' COMMENT '变更前余额',
  `after` decimal(10,2) UNSIGNED DEFAULT '0.00' COMMENT '变更后余额',
  `memo` varchar(255) DEFAULT '' COMMENT '备注',
  `addtime` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员积分变动表\n';

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_rule`
--

CREATE TABLE `lan_user_rule` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '0为菜单,1为权限节点',
  `pid` int(11) UNSIGNED DEFAULT '0' COMMENT '父ID',
  `name` varchar(255) DEFAULT '' COMMENT '规则名称',
  `rule` varchar(255) DEFAULT '' COMMENT '规则',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) DEFAULT '0' COMMENT '是否为菜单',
  `weight` int(11) DEFAULT '100' COMMENT '权重',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `addtime` int(11) UNSIGNED DEFAULT '0',
  `edittime` int(11) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `lan_user_rule`
--

INSERT INTO `lan_user_rule` (`id`, `type`, `pid`, `name`, `rule`, `icon`, `remark`, `ismenu`, `weight`, `status`, `addtime`, `edittime`) VALUES
(1, 0, 0, 'API接口', 'api', 'fa fa-plug', '', 1, 1, 1, 1578075939, 1578075939),
(2, 0, 1, '会员模块', 'api/user', 'fa fa-circle-o', '', 1, 2, 1, 1578075966, 1578075966),
(3, 0, 2, '会员中心', 'api/user/index', 'fa fa-circle-o', '', 1, 3, 1, 1578075989, 1578075989),
(4, 0, 2, '注册', 'api/user/register', 'fa fa-circle-o', '', 1, 4, 1, 1578076011, 1578076011),
(5, 0, 2, '登录', 'api/user/login', 'fa fa-circle-o', '', 1, 5, 1, 1578076029, 1578076029);

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_score_log`
--

CREATE TABLE `lan_user_score_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '0' COMMENT '会员ID',
  `score` int(10) UNSIGNED DEFAULT '0' COMMENT '变更积分',
  `before` int(10) UNSIGNED DEFAULT '0' COMMENT '变更前积分',
  `after` int(10) UNSIGNED DEFAULT '0' COMMENT '变更后积分',
  `memo` varchar(255) DEFAULT '' COMMENT '备注',
  `addtime` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员积分变动表\n';

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_sms`
--

CREATE TABLE `lan_user_sms` (
  `id` int(11) UNSIGNED NOT NULL,
  `event` varchar(30) DEFAULT '' COMMENT '事件',
  `mobile` varchar(30) DEFAULT '' COMMENT '手机号',
  `code` varchar(10) DEFAULT '' COMMENT '验证码',
  `times` int(10) UNSIGNED DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(50) DEFAULT '' COMMENT 'IP',
  `addtime` int(10) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `lan_user_token`
--

CREATE TABLE `lan_user_token` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT '0' COMMENT '会员ID',
  `token` varchar(50) DEFAULT '' COMMENT 'Token',
  `expire_time` int(10) UNSIGNED DEFAULT '0' COMMENT '过期时间',
  `addtime` int(10) UNSIGNED DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lan_admin`
--
ALTER TABLE `lan_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_admin_group`
--
ALTER TABLE `lan_admin_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_admin_group_access`
--
ALTER TABLE `lan_admin_group_access`
  ADD UNIQUE KEY `admin_group` (`admin_id`,`admin_group_id`);

--
-- Indexes for table `lan_admin_log`
--
ALTER TABLE `lan_admin_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `lan_admin_rule`
--
ALTER TABLE `lan_admin_rule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_attachment`
--
ALTER TABLE `lan_attachment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_command`
--
ALTER TABLE `lan_command`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `lan_config`
--
ALTER TABLE `lan_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lan_user`
--
ALTER TABLE `lan_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `mobile` (`mobile`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `lan_user_ems`
--
ALTER TABLE `lan_user_ems`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_user_group`
--
ALTER TABLE `lan_user_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_user_money_log`
--
ALTER TABLE `lan_user_money_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_user_rule`
--
ALTER TABLE `lan_user_rule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_user_score_log`
--
ALTER TABLE `lan_user_score_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_user_sms`
--
ALTER TABLE `lan_user_sms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lan_user_token`
--
ALTER TABLE `lan_user_token`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `lan_admin`
--
ALTER TABLE `lan_admin`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_admin_group`
--
ALTER TABLE `lan_admin_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_admin_log`
--
ALTER TABLE `lan_admin_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_admin_rule`
--
ALTER TABLE `lan_admin_rule`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_attachment`
--
ALTER TABLE `lan_attachment`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_command`
--
ALTER TABLE `lan_command`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `lan_config`
--
ALTER TABLE `lan_config`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user`
--
ALTER TABLE `lan_user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_ems`
--
ALTER TABLE `lan_user_ems`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_group`
--
ALTER TABLE `lan_user_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_money_log`
--
ALTER TABLE `lan_user_money_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_rule`
--
ALTER TABLE `lan_user_rule`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_score_log`
--
ALTER TABLE `lan_user_score_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_sms`
--
ALTER TABLE `lan_user_sms`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lan_user_token`
--
ALTER TABLE `lan_user_token`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
