<?php
/**
 * upchatgroup.php
 * 升级聊天表
 * @uses php tool.php -n upchatgroup
 * $Author$
 * $Id$
 */

class voa_backend_tool_upchatgroup extends voa_backend_base {

	// 参数
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		// 连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		// 判断数据库是否存在
		for($i = 10002; $i < 29375; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query('use ep_' . $i);
				$q = $db->query("SHOW TABLES LIKE 'oa_chatgroup'");
				if ($row = $db->fetch_row($q)) {
					echo "ep_{$i}\n";
					$db->query("ALTER TABLE oa_chatgroup_record DROP INDEX unique_cgr_id");
					$db->query("ALTER TABLE `oa_chatgroup_record` ADD INDEX(`cg_id`)");
					$db->query("ALTER TABLE `oa_chatgroup_record` ADD INDEX(`cgr_status`)");
					$db->query("ALTER TABLE `oa_chatgroup` ADD INDEX(`m_uid`)");
					$db->query("ALTER TABLE `oa_chatgroup` ADD INDEX(`cg_type`)");
					$db->query("ALTER TABLE `oa_chatgroup` ADD INDEX(`cg_name`)");
					$db->query("ALTER TABLE `oa_chatgroup` CHANGE `cg_name` `cg_name` CHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '聊天组名称'");
					$db->query("ALTER TABLE oa_chatgroup_record DROP INDEX unique_cgm_id");
					$db->query("ALTER TABLE `oa_chatgroup_member` ADD INDEX(`cgm_status`)");
					$db->query("ALTER TABLE `oa_chatgroup_member` ADD INDEX(`m_uid`)");
					$db->query("ALTER TABLE `oa_chatgroup_member` ADD INDEX(`cgm_count`)");
					$db->query("ALTER TABLE `oa_chatgroup_member` ADD INDEX(`cgm_lasted`)");
				}
			} catch (Exception $e) {
				continue;
			}
		}

		return true;
	}

}
