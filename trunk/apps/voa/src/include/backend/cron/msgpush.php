<?php
/**
 * 发送微信消息
 * $Author$
 * $Id$
 */

class voa_backend_cron_msgpush extends voa_backend_base {
	/** 外部传入的参数 */
	private $__opts = array();
	/** 微生活消息发送接口 */
	private $__serv_wxlife;
	/** 每次获取的数据条数 */
	private $__perpage = 100;
	/** 模板消息表 service */
	private $__serv_wxsq;
	/** php */
	private $__php;
	private $__cname_interval;
	private $__cname_sleep;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;

		$appname = basename(APP_PATH);
		$this->__php = config::get($appname.'.crontab.php');
		$this->__cname_interval = (int)config::get($appname.'.crontab.cname_interval');
		$this->__cname_sleep = (int)config::get($appname.'.crontab.cname_sleep');

		/** 判断初始值 */
		$this->__cname_interval = 0 > $this->__cname_interval ? 50 : $this->__cname_interval;
		$this->__cname_sleep = 0 > $this->__cname_sleep ? 1 : $this->__cname_sleep;
	}

	/**
	 * 入口函数
	 *
	 * @access public
	 * @return void
	 */
	public function main() {
		$this->_lock();
		$this->_log('--- begin 模板消息发送 --------------');

		try {
			/** 读取 cname 列表 */
			$cnames = array();
			if (!$this->__get_cnames($cnames)) {
				throw new Exception('get cnames error.', 100);
			}

			/** 发送请求 */
			$n = 0;
			foreach ($cnames as $_n) {
				$this->__exec_in_backend($this->__php.' -q '.APP_PATH.'/backend/cron.php -n pushmsgbycname -cname '.$_n);
				$this->__exec_in_backend($this->__php.' -q '.APP_PATH.'/backend/cron.php -n xinge -cname '.$_n);
				$n ++;
				/** 休息下 */
				if (0 == $n % $this->__cname_interval) {
					sleep($this->__cname_sleep);
				}
			}
		} catch (Exception $e) {
			$this->_log($e->getMessage());
		}

		$this->_log('--- end 模板消息发送 --------------');
		$this->_unlock();
	}

	/**
	 * 在后天执行
	 */
	private function __exec_in_backend($cmd) {

		if (substr(php_uname(), 0, 7) == "Windows"){
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
	}

	/**
	 * 获取 cnames 数据
	 * @param unknown $data
	 * @throws Exception
	 * @return boolean
	 */
	private function __get_cnames(&$data) {

		/** 先读取缓存 */
		$filepath = APP_PATH.'/tmp/crontab/';
		if (!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
		}

		$filename = $filepath.'cnames.php';
		if (file_exists($filename)) {
			@include $filename;
			if (startup_env::get('timestamp') < $cache['ts'] + 3600) {
				$data = $cache['data'];
				return true;
			}
		}

		$a = config::get(basename(APP_PATH).'.crontab.a');
		/** 从 uc 中取 */
		$uda = &service::factory('voa_uda_uc_dnspod_list');
		$data = array();
		if (!$uda->fetch_cnames_by_a($data, $a)) {
			throw new Exception('get dnspod list error.', 100);
			return false;
		}

		/** 写缓存 */
		$cache = array(
			'ts' => startup_env::get('timestamp'),
			'data' => $data
		);
		rfwrite($filename, "<?php\n//wbs! cache file, DO NOT modify me!\n//Created on ".rgmdate("M j, Y, G:i")."\n\n\$cache = ".rvar_export($cache).";\n\n");

		return true;
	}
}
