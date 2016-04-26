CREATE TABLE IF NOT EXISTS `{$prefix}express{$suffix}` (
`eid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '快递id' ,
`at_id`  varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '附件id' ,
`flag`  tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态, 1=待领取，2=已领取' ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id' ,
`username`  char(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '收件人姓名' ,
`get_time`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '领取时间' ,
`status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态, 1=初始化，2=已更新, 3=已删除' ,
`created`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间' ,
`updated`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`deleted`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间' ,
PRIMARY KEY (`eid`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='快递基本表';


CREATE TABLE IF NOT EXISTS `{$prefix}express_mem{$suffix}` (
`mid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id' ,
`eid`  int(10) UNSIGNED NOT NULL COMMENT '快递id' ,
`uid`  int(10) UNSIGNED NOT NULL COMMENT '用户id' ,
`username`  char(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名' ,
`flag`  tinyint(2) UNSIGNED NOT NULL COMMENT '1=接件人;2:收件人；3:代领人；4:发件人' ,
`status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '记录状态, 1=初始化，2=已更新，3=已删除' ,
`created`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间' ,
`updated`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`deleted`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间' ,
PRIMARY KEY (`mid`),
INDEX `eid` (`eid`),
INDEX `uid` (`uid`) 
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='快递扩展表';


CREATE TABLE IF NOT EXISTS `{$prefix}express_setting{$suffix}` (
`key`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '变量名' ,
`value`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '值' ,
`type`  tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '缓存类型, 0:非数组, 1:数组' ,
`comment`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '说明' ,
`status`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '记录状态, 1初始化，2=已更新, 3=已删除' ,
`created`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间' ,
`updated`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`deleted`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间' ,
PRIMARY KEY (`key`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='快递助手设置表';
