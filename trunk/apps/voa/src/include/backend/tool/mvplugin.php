<?php
/**
 * mvplugin.php
 * 比对数据表
 * @uses php tool.php -n mvplugin
 * $Author$
 * $Id$
 */
class voa_backend_tool_mvplugin extends voa_backend_base {
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

				$db->query("UPDATE oa_common_plugin_group SET cpg_name='微信OA' WHERE cpg_id=1");
				$db->query("UPDATE oa_common_plugin_group SET cpg_name='销售管理' WHERE cpg_id=2");
				$db->query("UPDATE oa_common_plugin_group SET cpg_name='团队协作' WHERE cpg_id=4");
				$db->query("UPDATE oa_common_plugin SET cp_name='工作报告' WHERE cp_pluginid=11");
				$db->query("UPDATE oa_common_plugin SET cpg_id=1 WHERE cp_pluginid=5");
				$db->query("UPDATE oa_common_plugin SET cpg_id=1 WHERE cp_pluginid=31");
				$db->query("UPDATE oa_common_plugin SET cpg_id=3 WHERE cp_pluginid=25");
				$db->query("UPDATE oa_common_plugin SET cpg_id=3 WHERE cp_pluginid=23");
				$db->query("UPDATE oa_common_plugin SET cpg_id=4 WHERE cp_pluginid=1");
				$db->query("UPDATE oa_common_plugin SET cpg_id=2 WHERE cp_pluginid=24");

				$q = $db->query("select * from oa_common_setting where cs_key='domain'");
				if ($setting = $db->fetch_array($q)) {
					//echo voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'plugin.inspect.item.php'."\n";
					@unlink(voa_h_func::get_sitedir(voa_h_func::get_domain($setting['cs_value'])).'plugin.php');
				}
			} catch (Exception $e) {
				echo $i.',';
				continue;
			}
		}
	}

}
