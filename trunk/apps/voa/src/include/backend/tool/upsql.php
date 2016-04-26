<?php
/**
 * upsql.php
 * 比对数据表
 * @uses php tool.php -n upsql
 * $Author$
 * $Id$
 */
class voa_backend_tool_upsql extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		return;
		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 10540; ++ $i) {
			try {
				echo 'use ep_'.$i."\n";
				$db->query('use ep_'.$i);
				//$db->query("ALTER TABLE `oa_member` DROP INDEX `m_openid`, ADD INDEX `m_openid` (`m_openid`)");
				//$db->query("UPDATE `oa_common_cpmenu` SET `ccm_name`='同步通讯录' WHERE `ccm_module`='manage' AND `ccm_operation`='member' AND `ccm_subop`='impqywx'");
				$db->query("DELETE FROM oa_common_cpmenu WHERE `ccm_module`='manage' AND `ccm_operation`='member' AND `ccm_subop`='dump'");
			} catch (Exception $e) {
				continue;
			}

			/**$q = $db->query('select * from oa_common_adminer where m_uid=1');
			$adminer = $db->fetch_array($q);
			$q = $db->query('select * from oa_member where m_uid=1');
			$member = $db->fetch_array($q);*/

			$q = $db->query("select * from oa_common_setting where cs_key='domain'");
			$setting = $db->fetch_array($q);
			echo voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'cpmenu.php'."\n";
			@unlink(voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'cpmenu.php');
			startup_env::set('sitedir', null);
		}

	}

}
