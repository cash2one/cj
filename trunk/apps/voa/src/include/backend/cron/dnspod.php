<?php
/**
 * dnspod.php
 * 比对数据表
 * @uses php tool.php -n dnspod -day 1
 * $Author$
 * $Id$
 */
class voa_backend_cron_dnspod extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->_db = &db::init($cfg);

		// 获取时间范围值
		$day = $this->__opts['day'];
		$ts = startup_env::get('timestamp') - $day * 86400;

		// 切换到 ucenter
		$this->_db->query('USE vcycenter');

		$failed = 0;
		$uda = &uda::factory('voa_uda_uc_dnspod_insert');
		// 读取待绑定的 dns
		$q = $this->_db->query("SELECT dp_cname, dp_data FROM uc_dnspod WHERE dp_created>$ts AND dp_status=1");
		while ($row = $this->_db->fetch_array($q)) {
			if (!$uda->add_cname($row['dp_cname'], $row['dp_data'])) {
				$failed ++;
				// 记录日志
				$this->_log($row['dp_cname'].', '.$row['dp_data']);
			}
		}

		// 发送短信
		$ip = empty($ip) ? controller_request::get_instance()->get_client_ip() : $ip;
		$uda_sms = &uda::factory('voa_uda_uc_sms_insert');
		$msg = 'DNS 绑定错误, 绑定错误域名数: '.$failed;
		if (0 < $failed && !$uda_sms->send('13588119714', $msg, $ip)) {
			return false;
		}

		return true;
	}

}
