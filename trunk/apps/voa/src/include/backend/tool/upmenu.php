<?php
/**
 * upmenu.php
* 比对数据表
* @uses php tool.php -n upmenu
* $Author$
* $Id$
*/
class voa_backend_tool_upmenu extends voa_backend_base {
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

		$pluginname = 'project';

		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 10409; ++ $i) {
			try {
				$db->query('use ep_'.$i);

				$q = $db->query("select * from oa_common_plugin where cp_identifier='{$pluginname}' and cp_available='4'");
				if (!$plugin = $db->fetch_array($q)) {
					continue;
				}

				$q = $db->query("select * from oa_common_setting where cs_key='domain'");
				if (!$set = $db->fetch_array($q)) {
					continue;
				}

				$domains = explode('.', $set['cs_value']);

				exec('php -q '.APP_PATH.'/backend/tool.php -n agentmenu -domain '.$domains[0].' -plugin '.$pluginname.' -pluginid '.$plugin['cp_pluginid']." -action create > /dev/null &");
			} catch (Exception $e) {
				continue;
			}
		}
	}
}
