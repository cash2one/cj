<?php
/**
 * rsyncadminer.php
 * 比对数据表
 * @uses php tool.php -n rsyncadminer
 * $Author$
 * $Id$
 */
class voa_backend_tool_rsyncadminer extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function _db2($cfg) {

		$cfg['host'] = '10.66.141.207';
		$cfg['pw'] = '88d8K88rMhQse4MD';
		$tablepre = $cfg['tablepre'];
		return db::init($cfg);
	}

	public function main() {

		return true;
		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);
		$db->query("USE vcycenter");

		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 38501; ++ $i) {
			try {
				echo 'use ep_'.$i."\n";
				if ($i > 36708) {
					$cur_db = $this->_db2($cfg);
				} else {
					$cur_db = $db;
				}
				$cur_db->query('USE ep_'.$i);

				$db->query("UPDATE vcycenter.uc_enterprise_adminer SET `status`=3 WHERE ep_id={$i}");
				/**$query = $db->query("SELECT * FROM uc_enterprise_adminer WHERE ep_id=$i");
				$adminers = array();
				while ($row = $db->fetch_array($query)) {
					$adminers[$row['ca_id']] = $row;
				}*/

				$query = $cur_db->query("SELECT * FROM oa_common_adminer WHERE ca_status<3");
				$adminers = array();
				while ($row = $cur_db->fetch_array($query)) {
					$lock = 2 == $row['ca_locked'] || 0 == $row['ca_locked'] ? 1 : 2;
					/**if ($adminers[$row['ca_id']]) {
						$db->query("UPDATE uc_enterprise_adminer SET WHERE ep_id=$i AND ca_id={$row['ca_id']}");
					} else {*/
						$db->query("INSERT INTO vcycenter.uc_enterprise_adminer(ep_id, ca_id, realname, mobilephone, userstatus, password, salt, status) VALUES($i, {$row['ca_id']}, '{$row['ca_username']}', '{$row['ca_mobilephone']}', {$lock}, '{$row['ca_password']}', '{$row['ca_salt']}', 1)");
					//}
				}
			} catch (Exception $e) {
				//print_r($e);
				continue;
			}
		}

	}

}
