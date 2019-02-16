# 技术文档

## 平台

服务端：ThinkPHP框架 + MySQL数据库

管理端：Web（ThinkPHP(PHP)+ jQuery(JS) + MDUI(CSS+JS) + bootstrap）

预定端：Web、Android(Java)

会议室前端：Qt框架 + 人脸识别SDK

## 环境

Server运行环境：PHPStudy



# MySQL数据库

（服务端）

运行`application/sql/install.sql`来安装

## 会议室 rooms

| 说明     | 字段名      | 类型    |
| -------- | ----------- | ------- |
| 索引     | room_id     | int     |
| 管理员   | admin_id    | int     |
| 名称     | name        | varchar |
| 栋       | building    | int     |
| 楼       | floor       | int     |
| 间       | num         | int     |
| 最大人数 | max         | int     |
| 话筒     | microphone  | boolean |
| 投影仪   | projection  | boolean |
| 价格     | price       | int     |
| 使用中   | using       | boolean |
| 维修中   | maintaining | boolean |
| 创建时间 | create_time | bigint  |
| 修改时间 | update_time | bigint  |

每个会议室都有位置，选择相距尽量远的会议室

如果栋楼都为空，那么主要用来判断的就是 num

**使用中**为这个会议室现在是不是正在使用（暂时没什么用）

## 管理员 admins

| 说明     | 字段名      | 类型    |
| -------- | ----------- | ------- |
| 索引     | admin_id    | int     |
| 账号     | username    | varchar |
| 密码     | password    | varchar |
| 昵称     | nickname    | varchar |
| 权限     | permission  | int     |
| 创建时间 | create_time | bigint  |
| 修改时间 | update_time | bigint  |

**权限**用来限制管理员账号是否能肆意修改

## 用户 users

| 说明     | 字段名      | 类型    |
| -------- | ----------- | ------- |
| 索引     | users_id    | int     |
| 账号     | username    | varchar |
| 密码     | password    | varchar |
| 昵称     | nickname    | varchar |
| 手机     | mobile      | varchar |
| 邮箱     | email       | varchar |
| 公司     | company     | varchar |
| 职位     | post        | varchar |
| 信用度   | credit      | int     |
| 创建时间 | create_time | bigint  |
| 修改时间 | update_time | bigint  |

每次借出结束后都会生成一个信用度，表示借出情况、损坏状况、整洁度等

如果出问题，会减少信用度

**信用度**高的，优先借出（如果有冲突的话）



## 租借表 lease

| 说明       | 字段名        | 类型     |
| ---------- | ------------- | -------- |
| 索引       | lease_id      | int      |
| 房间号     | room_id       | int      |
| 管理员     | admin_id      | int      |
| 借出人     | user_id       | int      |
| 开始时间   | start_time    | bigint   |
| 结束时间   | finish_time   | bigint   |
| 主题       | theme         | varchar  |
| 用途       | usage         | longtext |
| 留言       | message       | longtext |
| 场地打扫   | sweep         | boolean  |
| 现场招待   | entertain     | boolean  |
| 使用后环境 | circumstance  | varchar  |
| 管理员评分 | admin_score   | int      |
| 用户评分   | user_score    | int      |
| 信用度变化 | credit_change | int      |
| 创建时间   | create_time   | bigint   |
| 修改时间   | update_time   | bigint   |

从**开始时间**借出，结束时间必须结束，可以提前结束（真正结束时间）

留言可以说明是否需要饮品、座椅安排、其他特殊要求

使用后会进行评分、修改信用度，如果损坏情况严重，会降信用

可以申请场地打扫、现场招待服务，这需要会议室公司自行联系服务提供方



## 笔记表 notes

| 说明     | 字段名      | 类型     |
| -------- | ----------- | -------- |
| 索引     | note_id     | int      |
| 租借号   | lease_id    | int      |
| 用户号   | user_id     | int      |
| 内容     | content     | longtext |
| 备注     | remark      | longtext |
| 创建时间 | create_time | bigint   |
| 修改时间 | update_time | bigint   |



# ThinkPHP框架

Server 端采用 ThinkPHP5.0 框架



# jQuery框架





# MDUI框架





# Qt框架

采用 Qt5.11.0 框架



