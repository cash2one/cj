<?php
/**
 * epmonth.php
 * 比对数据表
 * @uses php tool.php -n epmonth
 * $Author$
 * $Id$
 */
class voa_backend_tool_epmonth extends voa_backend_base {
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

		$enterprise = array();
		$ep_corpid = array();
		$mems = array();
		$memwx = array();
		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 31212; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query('USE ep_' . $i);
				$query = $db->query("SELECT * FROM oa_common_setting WHERE cs_key IN ('corp_id', 'sitename')");
				$epname = '';
				while ($row = $db->fetch_array($query)) {
					if ('corp_id' == $row['cs_key']) {
						$ep_corpid[date('Y-m', $row['cs_updated'])] ++;
					} elseif ('sitename' == $row['cs_key']) {
						$epname = $row['cs_value'];
					}
				}

				$mcount = 0;
				$query = $db->query("SELECT * FROM oa_member WHERE m_status<4");
				while ($row = $db->fetch_array($query)) {
					$mems[date('Y-m', $row['m_created'])] ++;
					if (1 == $row['m_qywxstatus']) {
						$memwx[date('Y-m', $row['m_created'])] ++;
					}

					$mcount ++;
				}

				$enterprise[] = "{$epname},{$mcount}";
			} catch (Exception $e) {
				continue;
			}

		}

		$memout = array();
		foreach ($mems as $_ymd => $_m) {
			$memout[] = "{$_ymd},{$ep_corpid[$_ymd]},{$_m},{$memwx[$_ymd]}";
		}

		logger::error(implode("\n", $enterprise));
		logger::error(implode("\n", $memout));
	}

}
