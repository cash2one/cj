<?php
/**
 * getsignsite.php
 * 比对数据表
 * @uses php tool.php -n getsignsite
 * $Author$
 * $Id$
 */

class voa_backend_tool_getsignsite extends voa_backend_base {
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

		$epid2memct = array();
		$ts = startup_env::get('timestamp') - 86400 * 30;

		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 31827; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query('use ep_'.$i);

				//$db->query("UPDATE `oa_common_plugin` SET cp_name = '考勤' WHERE cp_name = '签到'");
				//$db->query("UPDATE `oa_common_plugin` SET cp_description = '支持IP与经纬度双重定位，支持多部门分班次、多地点考勤设置；外勤人员必须现场拍照确认地理位置，可设置考勤提醒。' WHERE cp_name = '考勤'");
				/**$q = $db->query("SHOW TABLES LIKE 'oa_sign_batch'");
				if ($row = $db->fetch_row($q)) {
					$db->query("ALTER TABLE oa_sign_batch ADD `range_on` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启考勤范围' AFTER `enable`");
					$db->query("ALTER TABLE oa_sign_batch ADD `sign_on` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启签到提醒' AFTER `range_on`");
					$db->query("ALTER TABLE oa_sign_batch ADD `sign_off` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启签退提醒' AFTER `sign_on`");
				}*/

				//$db->query("UPDATE oa_common_cpmenu SET ccm_status = 3 WHERE ccm_operation = 'sign' AND ccm_subop = 'setting'");

				/**$query = $db->query("SELECT * FROM oa_sign_batch");
				if ($row = $db->fetch_array($query)) {
					$db->query("UPDATE oa_sign_record SET sr_sign=1 WHERE sr_sign=0");
				}

				$q = $db->query("select * from oa_common_setting where cs_key='domain'");
				if ($setting = $db->fetch_array($q)) {
					echo voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])) . 'plugin.php' . "\n";
					@unlink(voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])) . 'plugin.php');
				}*/

				$query = $db->query("SELECT COUNT(*) FROM oa_sign_record WHERE sr_created>{$ts}");
				$rcdct = $db->result($query);
				if (0 >= $rcdct) {
					continue;
				}

				$query = $db->query("SELECT COUNT(*) FROM oa_member WHERE m_status<4");
				$ct = $db->result($query);

				$epid2memct[$i] = $ct.','.$rcdct;
			} catch (Exception $e) {
				continue;
			}
		}

		$epdetails = array();
		$epids = array_keys($epid2memct);
		$query = $db->query("SELECT * FROM vchangyi_admincp.cy_enterprise_profile WHERE ep_id IN (".implode(',', $epids).")");
		while ($row = $db->fetch_array($query)) {
			$epdetails[] = implode(',', array(
				$row['ep_id'],
				$epid2memct[$row['ep_id']],
				$row['ep_domain'],
				$row['ep_mobilephone'],
				$row['ep_email'],
				rgmdate($row['ep_created'], 'Y-m-d H:i'),
				$row['ep_name']
			));
		}

		logger::error(implode("\n", $epdetails));
	}

}
