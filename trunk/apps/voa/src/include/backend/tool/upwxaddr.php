<?php
/**
 * upwxaddr.php
 * 比对数据表
 * @uses php tool.php -n upwxaddr
 * $Author$
 * $Id$
 */
class voa_backend_tool_upwxaddr extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 10003; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query('use ep_'.$i);
				$db->query("UPDATE `oa_common_plugin` SET `cp_available`=0, `cp_status`=1 WHERE `cp_identifier`='inspect' AND `cp_available`=255");
				$db->query("ALTER TABLE `oa_common_shop` CHANGE `csp_name` `csp_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '门店名称', CHANGE `csp_address` `csp_address` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '门店位置', CHANGE `csp_lng` `csp_lng` DECIMAL(9,6) NOT NULL DEFAULT '0' COMMENT '所在经度', CHANGE `csp_lat` `csp_lat` DECIMAL(9,6) NOT NULL DEFAULT '0' COMMENT '所在纬度', CHANGE `cr_id` `cr_id` INT(10) NOT NULL DEFAULT '0' COMMENT '地区id', CHANGE `csp_status` `csp_status` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除', CHANGE `csp_created` `csp_created` INT(10) NOT NULL DEFAULT '0' COMMENT '创建时间', CHANGE `csp_updated` `csp_updated` INT(10) NOT NULL DEFAULT '0' COMMENT '更新时间', CHANGE `csp_deleted` `csp_deleted` INT(10) NOT NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("ALTER TABLE `oa_common_region` CHANGE `cr_parent_id` `cr_parent_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '地区上级id, 为0时, 是主地区', CHANGE `cr_name` `cr_name` VARCHAR(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '地区名称', CHANGE `cr_status` `cr_status` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除', CHANGE `cr_created` `cr_created` INT(10) NOT NULL DEFAULT '0' COMMENT '创建时间', CHANGE `cr_updated` `cr_updated` INT(10) NOT NULL DEFAULT '0' COMMENT '更新时间', CHANGE `cr_deleted` `cr_deleted` INT(10) NOT NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("ALTER TABLE `oa_inspect_tasks` CHANGE `it_alert_time` `it_alert_time` TIME NOT NULL COMMENT '提醒的时间点' AFTER `it_execution_status`, CHANGE `it_last_execution_time` `it_last_execution_time` INT(10) NOT NULL COMMENT '最后一次执行时间' AFTER `it_execution_status`, CHANGE `it_parent_id` `it_parent_id` INT(10) NOT NULL COMMENT '任务父id' AFTER `it_execution_status`");
				$db->query("ALTER TABLE `oa_inspect_tasks` CHANGE `it_title` `it_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '任务标题', CHANGE `it_description` `it_description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '任务描述', CHANGE `it_submit_uid` `it_submit_uid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务发起者uid', CHANGE `it_assign_uid` `it_assign_uid` INT(10) NOT NULL DEFAULT '0' COMMENT '用户id', CHANGE `it_finished_total` `it_finished_total` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已完成巡店数', CHANGE `it_start_date` `it_start_date` INT(10) NOT NULL DEFAULT '0' COMMENT '开始日期', CHANGE `it_end_date` `it_end_date` INT(10) NOT NULL DEFAULT '0' COMMENT '结束日期', CHANGE `it_repeat_frequency` `it_repeat_frequency` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '重复频率,no=不重复, 每天=day_(1-365), 每周=week_(1-7), 每月=mon_(1-31)', CHANGE `it_parent_id` `it_parent_id` INT(10) NOT NULL DEFAULT '0' COMMENT '任务父id', CHANGE `it_last_execution_time` `it_last_execution_time` INT(10) NOT NULL DEFAULT '0' COMMENT '最后一次执行时间', CHANGE `it_alert_time` `it_alert_time` TIME NOT NULL DEFAULT '0' COMMENT '提醒的时间点', CHANGE `it_created` `it_created` INT(10) NULL DEFAULT '0' COMMENT '创建时间', CHANGE `it_updated` `it_updated` INT(10) NULL DEFAULT '0' COMMENT '更新时间', CHANGE `it_deleted` `it_deleted` INT(10) NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("ALTER TABLE `oa_inspect_draft` CHANGE `insd_status` `insd_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常, 2=已更新，3=已删除'");
				$db->query("ALTER TABLE `oa_inspect_draft` CHANGE `m_openid` `m_openid` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名称', CHANGE `insd_subject` `insd_subject` VARCHAR(81) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '主题', CHANGE `insd_message` `insd_message` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容', CHANGE `insd_a_uid` `insd_a_uid` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '接收人uid, 以","分隔', CHANGE `insd_cc_uid` `insd_cc_uid` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '抄送人uid, 以","分隔'");
				$db->query("UPDATE oa_inspect_draft SET insd_status=3 WHERE insd_status=2;");
				$db->query("ALTER TABLE `oa_inspect_item` CHANGE `insi_parent_id` `insi_parent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上级打分项id', CHANGE `insi_describe` `insi_describe` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '打分项说明', CHANGE `insi_score` `insi_score` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '该项分数', CHANGE `insi_ordernum` `insi_ordernum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序值, 越大越靠前', CHANGE `insi_status` `insi_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除', CHANGE `insi_created` `insi_created` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间', CHANGE `insi_updated` `insi_updated` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间', CHANGE `insi_deleted` `insi_deleted` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("ALTER TABLE `oa_inspect_item` ADD `insi_rules_title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '规则的标题' AFTER `insi_describe`");
				$db->query("ALTER TABLE `oa_inspect_item` ADD `insi_score_title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '打分的标题' AFTER `insi_rules`");
				$db->query("ALTER TABLE `oa_inspect_item`  ADD `insi_hasselect` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '是否有单项, 1: 有; 0: 没有'  AFTER `insi_score`,  ADD `insi_select_title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '单项标题'  AFTER `insi_hasselect`,  ADD `insi_hasatt` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '是否有附件, 1: 有; 0: 没有'  AFTER `insi_select_title`,  ADD `insi_att_title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '附件标题'  AFTER `insi_hasatt`,  ADD `insi_hasfeedback` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '是否有反馈, 1: 有; 0: 没有'  AFTER `insi_att_title`,  ADD `insi_feedback_title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '反馈标题'  AFTER `insi_hasfeedback`");
				$db->query("ALTER TABLE `oa_inspect_item` ADD `insi_state` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1' COMMENT '使用状态, 1: 使用中; 2: 未使用' AFTER `insi_ordernum`, ADD INDEX (`insi_state`)");
				$db->query("UPDATE `oa_inspect_item` SET `insi_score`=5, `insi_hasatt`=1, `insi_hasfeedback`=1 WHERE `insi_parent_id`>0");
				$db->query("ALTER TABLE `oa_inspect` CHANGE `ins_note` `ins_note` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '巡店备注'");
				$db->query("ALTER TABLE `oa_inspect` CHANGE `ins_status` `ins_status` TINYINT(3) UNSIGNED NOT NULL COMMENT '记录状态, 1=新建, 2=已更新, 3=已删除'");
				$db->query("ALTER TABLE `oa_inspect` ADD `ins_type` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT '巡店状态, 1=待巡, 2=进行中，3=已巡' AFTER `ins_note`, ADD INDEX (`ins_type`)");
				$db->query("ALTER TABLE `oa_inspect` CHANGE `it_id` `it_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '巡店任务id', CHANGE `sponsor_uid` `sponsor_uid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务发起者uid', CHANGE `m_uid` `m_uid` INT(10) NOT NULL DEFAULT '0' COMMENT '用户id', CHANGE `m_username` `m_username` VARCHAR(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名', CHANGE `ins_lng` `ins_lng` DECIMAL(9,6) NOT NULL DEFAULT '0' COMMENT '当前经度', CHANGE `ins_lat` `ins_lat` DECIMAL(9,6) NOT NULL DEFAULT '0' COMMENT '当前纬度', CHANGE `csp_id` `csp_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '门店id', CHANGE `ins_note` `ins_note` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '巡店备注', CHANGE `ins_status` `ins_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '记录状态, 1=新建, 2=已更新, 3=已删除', CHANGE `ins_created` `ins_created` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间', CHANGE `ins_updated` `ins_updated` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间', CHANGE `ins_deleted` `ins_deleted` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("UPDATE oa_inspect SET ins_type=2 WHERE ins_status=2");
				$db->query("UPDATE oa_inspect SET ins_type=3 WHERE ins_status=3");
				$db->query("UPDATE oa_inspect SET ins_status=2 WHERE ins_status IN (2, 3)");
				$db->query("UPDATE oa_inspect SET ins_status=3 WHERE ins_status=4");
				$db->query("ALTER TABLE `oa_inspect` ADD `ins_score` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '巡店总分' AFTER `m_username`");
				$db->query("ALTER TABLE `oa_inspect_attachment` CHANGE `insat_status` `insat_status` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常; 2=已更新; 3=已删除;'");
				$db->query("UPDATE oa_inspect_attachment SET insat_status=3 WHERE insat_status=2");
				$db->query("ALTER TABLE `oa_inspect_mem` ADD `insm_src_uid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '发起人uid' AFTER `ins_id`, ADD INDEX (`insm_src_uid`)");
				$db->query("ALTER TABLE `oa_inspect_mem` ADD `insm_type` TINYINT NOT NULL DEFAULT '2' COMMENT '状态, 1: 接收人; 2: 抄送人;' AFTER `m_username`, ADD INDEX (`insm_type`)");
				$db->query("ALTER TABLE `oa_inspect_mem` CHANGE `insm_status` `insm_status` TINYINT(3) NOT NULL COMMENT '记录状态, 1=初始化，2=已更新, 3=已删除'");
				$db->query("ALTER TABLE `oa_inspect_mem` CHANGE `ins_id` `ins_id` INT(10) NOT NULL DEFAULT '0' COMMENT '巡店id', CHANGE `m_uid` `m_uid` INT(10) NOT NULL DEFAULT '0' COMMENT '被分享者uid', CHANGE `m_username` `m_username` VARCHAR(54) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名', CHANGE `insm_created` `insm_created` INT(10) NOT NULL DEFAULT '0' COMMENT '创建时间', CHANGE `insm_updated` `insm_updated` INT(10) NOT NULL DEFAULT '0' COMMENT '更新时间', CHANGE `insm_deleted` `insm_deleted` INT(10) NOT NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("UPDATE oa_inspect_mem SET insm_type=1 WHERE insm_status IN (1, 2)");
				$db->query("UPDATE oa_inspect_mem SET insm_type=2 WHERE insm_status=3");
				$db->query("UPDATE oa_inspect_mem SET insm_status=2 WHERE insm_status=3");
				$db->query("UPDATE oa_inspect_mem SET insm_status=3 WHERE insm_status=4");
				$db->query("ALTER TABLE `oa_inspect_score` CHANGE `isr_deleted` `isr_deleted` INT(10) NOT NULL DEFAULT '0' COMMENT '删除时间'");
				$db->query("ALTER TABLE `oa_inspect_score` ADD `isr_state` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT '巡店状态, 1=待评，2=已评' AFTER `isr_type`");
				$db->query("ALTER TABLE `oa_inspect_score` CHANGE `isr_status` `isr_status` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=新建，2=已更新, 3=已删除'");
				$db->query("ALTER TABLE `oa_inspect_score` CHANGE `m_uid` `m_uid` INT(10) NOT NULL DEFAULT '0' COMMENT '巡视者uid', CHANGE `cr_id` `cr_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属地区id', CHANGE `csp_id` `csp_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '店铺id', CHANGE `ins_id` `ins_id` INT(10) NOT NULL DEFAULT '0' COMMENT '巡店id', CHANGE `insi_id` `insi_id` INT(3) NOT NULL DEFAULT '0' COMMENT '打分项id, 为0时, 是总分', CHANGE `isr_message` `isr_message` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '对该项的评论/问题', CHANGE `isr_score` `isr_score` INT(3) NOT NULL DEFAULT '0' COMMENT '分数', CHANGE `isr_date` `isr_date` INT(10) NOT NULL DEFAULT '0' COMMENT '日期', CHANGE `isr_type` `isr_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1：日，2：周，3：月', CHANGE `isr_created` `isr_created` INT(10) NOT NULL DEFAULT '0' COMMENT '创建时间', CHANGE `isr_updated` `isr_updated` INT(10) NOT NULL DEFAULT '0' COMMENT '更新时间'");
				$db->query("ALTER TABLE `oa_inspect_score` ADD `isr_option` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '选项值' AFTER `isr_score`");
				$db->query("ALTER TABLE `oa_inspect_score` CHANGE `insi_id` `insi_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '打分项id, 为0时, 是总分'");
				$db->query("UPDATE oa_inspect_score SET isr_state=1 WHERE isr_state=1");
				$db->query("UPDATE oa_inspect_score SET isr_state=2 WHERE isr_state=2");
				$db->query("INSERT INTO `oa_inspect_setting` (`is_key`, `is_value`, `is_type`, `is_comment`, `is_status`, `is_created`, `is_updated`, `is_deleted`) VALUES ('select_title', '选项', '0', '评分选项', '1', '0', '0', '0')");
				$db->query("UPDATE `oa_inspect_setting` SET `is_key`=0 WHERE `is_value`='score_rule_diy'");
				$db->query("CREATE TABLE IF NOT EXISTS `oa_inspect_option` (
  `inso_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `insi_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '打分项id',
  `inso_optvalue` varchar(255) NOT NULL DEFAULT '' COMMENT '选项',
  `inso_state` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '选项状态, 1: 使用中, 2: 被弃用',
  `inso_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '记录状态, 1: 入库; 2: 更新; 3: 删除',
  `inso_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '入库时间',
  `inso_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `inso_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`inso_id`),
  KEY `inso_status` (`inso_status`),
  KEY `insi_id` (`insi_id`),
  KEY `inso_state` (`inso_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

				$q = $db->query("select * from oa_common_setting where cs_key='domain'");
				if ($setting = $db->fetch_array($q)) {
					echo voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'plugin.inspect.item.php'."\n";
					@unlink(voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'plugin.inspect.item.php');
					@unlink(voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'plugin.inspect.setting.php');
				}


			} catch (Exception $e) {
				continue;
			}

		}

	}

}
