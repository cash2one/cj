CREATE TABLE `{$prefix}note{$suffix}` (
  `note_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '笔记id',
  `m_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '课程 所属 用户id',
  `m_username` varchar(64) NOT NULL COMMENT '用户名',
  `cid` int(11) unsigned NOT NULL COMMENT '所属课程id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '课程标题',
  `content` text NOT NULL COMMENT '笔记内容',
  `attachments` text NOT NULL COMMENT '附件：音图和附件序列化数组',
  `c_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `u_time` int(10) unsigned NOT NULL COMMENT '最后一次更新时间',
  `scan_num` tinyint(6) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  PRIMARY KEY (`note_id`,`m_uid`,`cid`),
  KEY `m_uid` (`m_uid`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='笔记表';



