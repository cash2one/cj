<?php
/**
 * hsdaily.php
 * 日活数据表
 * @uses php tool.php -n hsdaily
 * $Author$
 * $Id$
 */
class voa_backend_tool_hsdaily extends voa_backend_base {
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

		$week = array();
		$days = array();

		/**
		 * 判断数据库是否存在
		 */
		for($i = 10002; $i < 32348; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query('use ep_' . $i);

				// 周活跃
				$query = $db->query("SELECT COUNT(DISTINCT uid) AS ct, week FROM oa_common_userlog GROUP BY week");
				while ($row = $db->fetch_array($query)) {
					if ($week[$row['week']]) {
						$week[$row['week']] += $row['ct'];
					} else {
						$week[$row['week']] = $row['ct'];
					}
				}

				// 日活跃
				$query = $db->query("SELECT COUNT(DISTINCT uid) AS ct, year, month, day FROM oa_common_userlog GROUP BY year, month, day");
				while ($row = $db->fetch_array($query)) {
					$ymd = $row['year'].'-'.$row['month'].'-'.$row['day'];
					if ($days[$ymd]) {
						$days[$ymd] += $row['ct'];
					} else {
						$days[$ymd] = $row['ct'];
					}
				}
			} catch (Exception $e) {
				continue;
			}
		}

		$week_res = array();
		$days_res = array();
		foreach ($week as $_k => $_v) {
			$week_res[] = $_k . ':' . $_v;
		}

		foreach ($days as $_k => $_v) {
			$days_res[] = $_k . ':' . $_v;
		}

		logger::error(var_export($week_res, true));
		logger::error(var_export($days_res, true));
	}

}
