create table IF NOT EXISTS {$prefix}blessing_redpack{$suffix}
(
   `id`                   int(10) unsigned not null auto_increment comment '自增ID',
   `m_uid`                int(10) unsigned not null default 0 comment '发红包的用户uid',
   `m_username`           varchar(54) not null comment '发红包的用户名称',
   `actname`              varchar(100) not null default '' comment '红包活动名称',
   `remark`               varchar(255) not null default '' comment '备注',
   `type`                 tinyint(1) unsigned not null default 0 comment '红包分配方式：1=排队分区随机; 2=平均分配; 3=定点红包; 4=自由红包;',
   money                int(10) unsigned default 0 comment '红包单个金额(只有固定金额的红包时，此字段为必填), 单位: 分',
   total                int(10) unsigned not null default 0 comment '红包活动总金额, 单位: 分',
   remainder            int(11) not null default 0 comment '剩余红包总额, 单位: 分',
   redpacks             int(10) unsigned not null default 0 comment '红包总数',
   times                int(10) unsigned not null default 0 comment '红包已被领取个数',
   starttime            int(10) unsigned not null default 0 comment '红包开始时间',
   endtime              int(10) unsigned not null default 0 comment '活动结束时间',
   nickname             varchar(32) not null default '' comment '提供方名称',
   sendname             varchar(32) not null default '' comment '红包发送者名称',
   wishing              varchar(128) not null default '' comment '红包祝福语',
   logoimgurl           varchar(128) not null default '' comment '商户logo的url',
   sharecontent         varchar(255) not null default '' comment '分享文案',
   shareimgurl          varchar(128) not null default '' comment '分享的图片url',
   highest              int(10) unsigned not null default 0 comment '当前最高红包',
   persons              text comment '祝福人',
   content              text comment '祝福语',
   chat_bg              int(10) comment '聊天页面背景图id',
   receive_bg           int(10) comment '领取页面背景图id',
   share_num            int(10) default 0 comment '分享数',
   see_num              int(10) default 0 comment '查看数',
   invite_content       varchar(100) comment '邀请语',
   msg_status           tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '消息推送状态：0-未推送；1-已推送',
   status               tinyint(2) unsigned not null default 1 comment '数据状态：1=新建;2=已更新;3=已删除',
   created              int(10) unsigned not null default 0 comment '创建时间',
   updated              int(10) unsigned not null default 0 comment '更新时间',
   deleted              int(10) unsigned not null default 0 comment '删除时间',
   primary key (`id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='红包主表';

create table IF NOT EXISTS {$prefix}blessing_redpack_department{$suffix}
(
   id                   int(10) unsigned not null auto_increment comment '自增ID',
   redpack_id           int(10) unsigned not null comment '红包ID',
   dep_id               text not null comment '部门id 为 0 时, 全体成员可看',
   dep_name             text comment '部门名称',
   receive_uid          text comment '接收人id',
   receive_uname        text comment '接收人名称',
   status               tinyint(3) unsigned not null default 1 comment '记录状态, 1=初始化，2=已更新, 3=已删除',
   created              int(10) unsigned not null default 0 comment '创建时间',
   updated              int(10) unsigned not null default 0 comment '更新时间',
   deleted              int(10) unsigned not null default 0 comment '删除时间',
   primary key (`id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='红包和部门关联表';

create table IF NOT EXISTS {$prefix}blessing_redpack_log{$suffix}
(
   id                   int(10) unsigned not null auto_increment comment '自增ID',
   redpack_id           int(10) unsigned not null  comment '所属的红包活动ID',
   m_uid                int(10) unsigned not null  comment '用户UID，如果是外部人员领取其为0',
   m_username           varchar(54) not null comment '用户名称',
   dep_name             varchar(20) comment '部门名称',
   dep_id               int(10) comment '部门id',
   money                mediumint(5) unsigned not null default 0 comment '领到的钱数。单位：分',
   ip                   varchar(15) not null default '0.0.0.0' comment 'IP地址',
   mch_billno           varchar(28) not null default '' comment '红包订单号',
   redpack_status       tinyint(1) comment '红包状态: 1=待拆;2=支付失败;3=已领取;4=已支付;9=待抢',
   redpack_time         int(10) comment '红包领取时间',
   return_code          varchar(16) comment '微信支付通讯返回状态码',
   return_msg           varchar(128) comment '微信支付通讯返回信息',
   result_code          varchar(16) comment '微信支付业务结果',
   err_code             varchar(32) comment '微信支付错误代码',
   err_code_des         varchar(128) comment '微信支付错误代码描述',
   payment_no           varchar(32) comment '微信支付成功返回的订单号',
   payment_time         varchar(20) comment '微信支付成功反回时间',
   ranking              int(10) comment '排名',
   status               tinyint(2) unsigned not null default 1 comment '数据状态：1=新建;2=已更新;3=已删除',
   created              int(10) unsigned not null default 0 comment '创建时间',
   updated              int(10) unsigned not null default 0 comment '更新时间',
   deleted              int(10) unsigned not null default 0 comment '删除时间',
   primary key (`id`)
)
ENGINE=InnoDB AUTO_INCREMENT=284 DEFAULT CHARSET=utf8 COMMENT='红包明细表';


create table IF NOT EXISTS {$prefix}blessing_redpack_setting{$suffix}
(
   `key`                varchar(50) not null comment '变量名',
   `value`              text not null comment '值',
   `type`               tinyint(3) unsigned not null default 0 comment '缓存类型, 0:非数组, 1:数组',
   comment              text not null comment '说明',
   status               tinyint(3) unsigned not null default 1 comment '记录状态, 1初始化，2=已更新, 3=已删除',
   created              int(10) unsigned not null default 0 comment '创建时间',
   updated              int(10) unsigned not null default 0 comment '更新时间',
   deleted              int(10) unsigned not null default 0 comment '删除时间',
   primary key (`key`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='红包设置表';

CREATE TABLE IF NOT EXISTS {$prefix}blessing_redpack_member{$suffix}
(
  id                    int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  m_uid                 int(10) unsigned not null default 0 comment '用户主键',
  redpack_id            int(10) unsigned NOT NULL COMMENT '红包主键',
  m_username             varchar(20) DEFAULT NULL COMMENT '姓名',
  m_mobilephone         varchar(20) NOT NULL COMMENT '手机',
  `position`              varchar(20) NOT NULL COMMENT '职位',
  department_name       varchar(20) NOT NULL COMMENT '部门',
  is_new                tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否是新人 0=是; 1=否',
  status                tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新建;2=已更新;3=已删除',
  created               int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  updated               int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  deleted               int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (id)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='红包活动用户表';