<?php
/**
 * uppluginuid.php
* 比对数据表
* @uses php tool.php -n uppluginuid
* $Author$
* $Id$
*/
class voa_backend_tool_uppluginuid extends voa_backend_base {
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
		for ($i = 10002; $i < 10706; ++ $i) {
			try {
				$db->query('use ep_'.$i);
				$q = $db->query("select * from oa_common_plugin where cp_available>0 and cp_available<6");
				while ($_p = $db->fetch_array($q)) {
					$mem = array();
					$qa = $db->query("select * from oa_member where m_uid=1");
					$mem = $db->fetch_array($qa);

					$mem_c = array();
					$qb = $db->query("select * from oa_member where m_mobilephone='{$mem['m_mobilephone']}' and m_uid>1
					and m_status<4");
					$mem_c = $db->fetch_array($qb);
					if (empty($mem_c)) {
						continue;
					}

					if ('project' == $_p['cp_identifier']) {
						$db->query("update oa_project_proc set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_project_mem set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_project set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('dailyreport' == $_p['cp_identifier']) {
						$db->query("update oa_dailyreport set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_dailyreport_mem set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_dailyreport_post set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('inspect' == $_p['cp_identifier']) {
						$db->query("update oa_inspect set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_inspect_mem set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_inspect_score set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('sign' == $_p['cp_identifier']) {
						$db->query("update oa_sign_plead set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_sign_record set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('asdfor' == $_p['cp_identifier']) {

					} elseif ('askoff' == $_p['cp_identifier']) {
						$db->query("update oa_askoff set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_askoff_post set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_askoff_proc set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('reimburse' == $_p['cp_identifier']) {
						$db->query("update oa_reimburse set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_reimburse_bill set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_reimburse_bill_attachment set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_reimburse_bill_submit set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_reimburse_post set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_reimburse_proc set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('meeting' == $_p['cp_identifier']) {
						$db->query("update oa_meeting set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_meeting_mem set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('vnote' == $_p['cp_identifier']) {
						$db->query("update oa_vnote set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_vnote_mem set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_vnote_post set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					} elseif ('minutes' == $_p['cp_identifier']) {
						$db->query("update oa_minutes set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_minutes_mem set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
						$db->query("update oa_minutes_post set m_uid='{$mem_c['m_uid']}' where m_uid='1'");
					}
				}
			} catch (Exception $e) {
				continue;
			}

		}

	}

	public function echo_query($sql) {
		echo $sql."\n";
	}

}
