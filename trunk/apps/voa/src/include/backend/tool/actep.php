<?php
/**
 * actep.php
 * 比对数据表
 * @uses php tool.php -n activeuser
 * $Author$
 * $Id$
 */
class voa_backend_tool_actep extends voa_backend_base {
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

		$month = array();
		$days = array();
		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 31966; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query("USE ep_{$i}");

				$query = $db->query("SELECT * FROM vchangyi_admincp.cy_enterprise_profile WHERE ep_id={$i}");
				if (!$enterprise = $db->fetch_array($query)) {
					continue;
				}

				$ym = rgmdate($enterprise['ep_created'], 'Y-m');
				$ymd = rgmdate($enterprise['ep_created'], 'Y-m-d');

				if (!isset($month[$ym])) {
					$month[$ym] = array(
						'count' => 0,
						'ymd' => $ymd,
						'ten_d' => 0,
						'ten_i' => 0
					);
				}
				if (!isset($days[$ymd])) {
					$days[$ymd] = array(
						'count' => 0,
						'ymd' => $ymd,
						'ten_d' => 0,
						'ten_i' => 0
					);
				}

				$query = $db->query("SELECT COUNT(*) FROM oa_member WHERE m_status<4");
				$count = $db->result($query);

				$month[$ym]['count'] ++;
				$days[$ymd]['count'] ++;
				if (10 > $count) {
					$month[$ym]['ten_d'] ++;
					$days[$ymd]['ten_d'] ++;
				} else {
					$month[$ym]['ten_i'] ++;
					$days[$ymd]['ten_i'] ++;
				}


			} catch (Exception $e) {
				continue;
			}

		}

		$month_ar = array();
		$days_ar = array();
		foreach ($month as $k => $v) {
			$month_ar[] = implode("\n", $v);
		}

		foreach ($days as $k => $v) {
			$days_ar[] = implode("\n", $v);
		}

		logger::error(implode("\n", $month_ar));
		logger::error(implode("\n", $days_ar));

	}

}
