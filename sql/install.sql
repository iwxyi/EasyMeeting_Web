SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- 创建房间表
DROP TABLE IF EXISTS `room`;
CREATE TABLE `room` (
	`room_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`admin_id` int(11) COMMENT '会议室管理员（负责人）',
	`name` varchar(255) DEFAULT '匿名' COMMENT '会议室名',
	`building` int(11) DEFAULT 0 COMMENT '栋',
	`floor` int(11) DEFAULT 0 COMMENT '楼',
	`num` int(11) DEFAULT 0 COMMENT '间',
	`max` int(11) DEFAULT 30 COMMENT '最大人数',
	`microphone` boolean DEFAULT true COMMENT '话筒',
	`projection` boolean DEFAULT true COMMENT '投影仪',
	`price` int(11) DEFAULT 0 COMMENT '价格（单位：小时）',
	`using` boolean DEFAULT false COMMENT '是否正在使用中',
	`maintaining` boolean DEFAULT false COMMENT '维修中',
	`create_time` bigint COMMENT '创建时间',
	`update_time` bigint COMMENT '修改时间',
	PRIMARY KEY(`room_id`)
)ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- 创建管理员表
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
	`admin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(255) NOT NULL COMMENT '用户账号',
	`password` varchar(255) NOT NULL COMMENT '用户密码',
	`nickname` varchar(255) COMMENT '用户昵称',
	`permission` int(11) DEFAULT 1 COMMENT '权限，1为能修改',
	`create_time` bigint COMMENT '创建时间',
	`update_time` bigint COMMENT '修改时间',
	PRIMARY KEY(`admin_id`)
)ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- 创建用户表
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
	`user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(255) NOT NULL COMMENT '用户账号',
	`password` varchar(255) NOT NULL COMMENT '用户密码',
	`nickname` varchar(255) COMMENT '用户昵称',
	`mobile` varchar(255) COMMENT '手机号',
	`email` varchar(255) COMMENT '邮箱',
	`company` varchar(255) COMMENT '公司',
	`post` varchar(255) COMMENT '职位',
	`credit` int(11) DEFAULT 100 COMMENT '信用度',
	`create_time` bigint COMMENT '创建时间',
	`update_time` bigint COMMENT '修改时间',
	PRIMARY KEY(`user_id`)
)ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- 创建租借表
DROP TABLE IF EXISTS `lease`;
CREATE TABLE `lease` (
	`lease_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`room_id` int(11) NOT NULL COMMENT '房间号',
	`admin_id` int(11) COMMENT '管理员号',
	`user_id` int(11) NOT NULL COMMENT '用户号',
	`start_time` bigint NOT NULL COMMENT '开始时间',
	`end_time` bigint NOT NULL COMMENT '最晚时间',
	`finish_time` bigint COMMENT '结束时间',
	`theme` varchar(255) DEFAULT '' COMMENT '会议主题',
	`usage` longtext COMMENT '用途说明',
	`message` longtext COMMENT '留言（饮品、座位安排）',
	`sweep` boolean DEFAULT false COMMENT '申请场地清理服务',
	`entertain` boolean DEFAULT false COMMENT '申请招待安排服务',
	`remote` boolean DEFAULT false COMMENT '申请远程会议服务（录像）',
	`circumstance` varchar(255) COMMENT '使用后环境情况说明（损坏情况等）',
	`admin_score` int(11) COMMENT '管理员评分(1-5)',
	`user_score` int(11) COMMENT '用户评分(1-5)',
	`credit_change` int(11) DEFAULT 100 COMMENT '用户获得信用（可正负）',
	`create_time` bigint COMMENT '创建时间',
	`update_time` bigint COMMENT '修改时间',
	PRIMARY KEY(`lease_id`)
)ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- 创建签到表
DROP TABLE IF EXISTS `check`;
CREATE TABLE `check` (
	`check_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`lease_id` int(11) NOT NULL COMMENT '租约号',
	`user_id` int(11) NOT NULL COMMENT '用户号',
	`checked` boolean DEFAULT false COMMENT '已经到达',
	`leave` boolean DEFAULT false COMMENT '已经离开',
	`create_time` bigint COMMENT '创建时间',
	`update_time` bigint COMMENT '修改时间',
	PRIMARY KEY(`check_id`)
)ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- 创建笔记表
DROP TABLE IF EXISTS `note`;
CREATE TABLE `note` (
	`note_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`lease_id` int(11) NOT NULL COMMENT '租借号',
	`user_id` int(11) NOT NULL COMMENT '笔记所属用户号',
	`content` longtext DEFAULT '' COMMENT '笔记内容',
	`remark` longtext DEFAULT '' COMMENT '笔记备注',
	`create_time` bigint COMMENT '创建时间',
	`update_time` bigint COMMENT '修改时间',
	PRIMARY KEY(`note_id`)
)ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- 插入测试数据
BEGIN;

-- 管理员
INSERT INTO admin (username, password, nickname) values ('admin0', '0', '主管理员');
INSERT INTO admin (username, password, nickname) values ('admin1', '1', 'admin1');
INSERT INTO admin (username, password, nickname) values ('admin2', '2', 'admin2');
INSERT INTO admin (username, password, nickname) values ('admin3', '3', 'admin3');

-- 会议室
INSERT INTO room (admin_id, name) values (1, '会议室1号');
INSERT INTO room (admin_id, name) values (1, '会议室2号');
INSERT INTO room (admin_id, name) values (1, '会议室3号');
INSERT INTO room (admin_id, name) values (1, '会议室4号');
INSERT INTO room (admin_id, name) values (1, '会议室5号');
INSERT INTO room (admin_id, name) values (1, '会议室6号');
INSERT INTO room (admin_id, name) values (1, '会议室7号');
INSERT INTO room (admin_id, name) values (1, '会议室8号');

-- 用户
INSERT INTO user (username, password, nickname) values ('user0', '0', '用户0');
INSERT INTO user (username, password, nickname) values ('user1', '1', '用户1');
INSERT INTO user (username, password, nickname) values ('user2', '2', '用户2');
INSERT INTO user (username, password, nickname) values ('user3', '3', '用户3');
INSERT INTO user (username, password, nickname) values ('user4', '4', '用户4');
INSERT INTO user (username, password, nickname) values ('user5', '5', '用户5');

-- 租约
INSERT INTO lease (room_id, user_id, start_time, finish_time, theme, `usage`, message, sweep, entertain, remote)
	values ('1', '0', '1550304000', '1550311200', '开发会议', '讨论智能会议室', '', false, false, false);
INSERT INTO lease (room_id, user_id, start_time, finish_time, theme, `usage`, message, sweep, entertain, remote)
	values ('1', '0', '1550304000', '1550311200', '吃饭', '就是吃个饭', '需要准备一口锅', false, false, false);
INSERT INTO lease (room_id, user_id, start_time, finish_time, theme, `usage`, message, sweep, entertain, remote)
	values ('2', '3', '1550304000', '1550311200', '开会', '特殊会议', '带上吃饭的交货', false, false, false);

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;