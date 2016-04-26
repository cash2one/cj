<?php
/**
 * activeuser.php
 * 比对数据表
 * @uses php tool.php -n activeuser
 * $Author$
 * $Id$
 */
class voa_backend_tool_activeuser extends voa_backend_base {
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

		$month_1 = time() - 30 * 86400;
		$month_2 = time() - 60 * 86400;
		$month_3 = time() - 90 * 86400;

		$m_1 = array();
		$m_2 = array();
		$m_3 = array();
		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 31350; ++ $i) {
			startup_env::set('sitedir', null);
			try {
				$db->query("USE ep_{$i}");
				$query = $db->query("SELECT COUNT(*) FROM oa_member WHERE m_status<4");
				$count = $db->result($query);

				$query = $db->query("SELECT COUNT(*) FROM oa_member WHERE m_updated>{$month_3} AND m_status<4");
				$count_3 = $db->result($query);
				if (10 > $count || 0.15 > $count_3 / $count) {
					continue;
				}

				$query = $db->query("SELECT * FROM oa_common_setting WHERE cs_key IN ('ep_id', 'sitename')");
				$ep_id = 0;
				$sitename = '';
				while ($row = $db->fetch_array($query)) {
					if ('ep_id' == $row['cs_key']) {
						$ep_id = $row['cs_value'];
					} else {
						$sitename = $row['cs_value'];
					}
				}
				$m_3[] = $ep_id.",".$sitename.",".(int)($count_3 * 100 / $count);

				$query = $db->query("SELECT COUNT(*) FROM oa_member WHERE m_updated>{$month_2} AND m_status<4");
				$count_2 = $db->result($query);
				if (0.15 > $count_2 / $count) {
					continue;
				}
				$m_2[] = $ep_id.",".$sitename.",".(int)($count_2 * 100 / $count);

				$query = $db->query("SELECT COUNT(*) FROM oa_member WHERE m_updated>{$month_1} AND m_status<4");
				$count_1 = $db->result($query);
				if (0.15 > $count_1 / $count) {
					continue;
				}
				$m_1[] = $ep_id.",".$sitename.",".(int)($count_1 * 100 / $count);
			} catch (Exception $e) {
				continue;
			}

		}

		logger::error("1 month\n".implode("\n", $m_1));
		logger::error("2 month\n".implode("\n", $m_2));
		logger::error("3 month\n".implode("\n", $m_3));

	}

}
