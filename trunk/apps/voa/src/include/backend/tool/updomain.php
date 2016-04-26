<?php
/**
 * updomain.php
 * 比对数据表
 * @uses php tool.php -n updomain ep_id_b 1 ep_id_e 3
 * $Author$
 * $Id$
 */
class voa_backend_tool_updomain extends voa_backend_base {
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
		$db = db::init($cfg);

		$q = $db->query('USE vchangyi_admincp');
		$limit = 200;
		$ep_id_b = $this->__opts['ep_id_b'];
		$ep_id_e = $this->__opts['ep_id_e'] + 1;
		while (true) {
			try {
				$q = $db->query("SELECT * FROM cy_enterprise_profile WHERE ep_id>{$ep_id_b} AND ep_id<{$ep_id_e} ORDER BY ep_id ASC LIMIT {$limit}");
				$count = 0;
				while ($row = $db->fetch_array($q)) {
					$count ++;
					voa_h_func::get_json_by_post($data, "http://{$row['ep_domain']}/api/common/post/updatewxqydomain");
					//echo "http://{$row['ep_domain']}/api/common/post/updatewxqydomain\n";
					$ep_id_b = $row['ep_id'];
					echo $row['ep_id'] . "\n";
				}
			} catch (Exception $e) {
				continue;
			}

			if ($limit > $count) {
				break;
			}
		}

	}

}
