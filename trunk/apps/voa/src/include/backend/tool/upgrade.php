<?php
/**
 * upgrade.php
 * 升级系统工具
 * 主要用于系统整体的数据升级之用。具体应用数据升级请使用upgradeplugin工具
 * php -q APP_PATH/backend/tool.php -n upgrade
 * -version 系统的目标版本号（必须提供）具体根据应用升级目录而定，一般推荐使用ymd+数字来命名
 * -epid 执行的企业数据库范围（必须提供）如：1001,1234。则执行ep_1001到ep_1234的数据库（会自动忽略不存在的库），
 *       或者，直接写要升级的数据库名，多个之间使用半角逗号分隔
 *       如果设置为：-1，则自动执行整个数据库服务器内所有 ep_*** 的数据库
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_backend_tool_upgrade extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	private $__execute_file = '';
	private $__databases = 0;

	public function __construct($opts) {

		parent::__construct();

		// 默认的全部参数定义
		// 参数名 => 是否必须提供
		$default_opts = array(
			'version' => true,// 版本号
			'epid' => true,// 需要执行的企业ep_id范围，格式为：xxxx,yyyy
			'common_table_no_run' => false,// 定义是否执行公共表结构升级（具体按应用升级需要而定）
		);
		$options = array();
		foreach ($default_opts as $option => $required) {
			$value = isset($opts[$option]) ? trim($opts[$option]) : null;
			if ($required && $value === null) {
				return $this->_output("option '-".$option."' is must");
			}
			$options[$option] = $value;
		}

		// 检查版本号是否有效
		if (empty($options['version']) || preg_match('/['.preg_quote('\/:*?"<>|.', '/').']/', $options['version'])) {
			return $this->_output("option '-version' value is invalid character");
		}
		// 升级脚本所在目录
		$this->__execute_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'upgrade'.DIRECTORY_SEPARATOR.$options['version'].'.php';
		if (!is_file($this->__execute_file)) {
			return $this->_output("option '-version' value invalid or not exists");
		}

		// 检查企业ID执行范围是否正确
		if (empty($options['epid'])) {
			return $this->_output("option '-epid' value invalid");
		}

		$databases = array();
		if ($options['epid'] < 0) {
			// 执行服务器内所有ep_**的数据库

			$databases = null;

		} elseif (preg_match('/^\s*([\d]+)\s*,\s*([\d]+)\s*$/', $options['epid'], $match)) {
			// 以数据库ep_id为范围的执行
			if ($match[1] > $match[2]) {
				return $this->_output("option '-epid' number range error");
			}

			for ($i = $match[1]; $i <= $match[2]; ++ $i) {
				$databases[] = 'ep_'.$i;
			}
		} elseif (rstrtolower($options['epid']) == 'all') {
			// 执行全部数据库
			$databases = null;
		} else {
			// 指定数据库执行
			foreach (explode(",", $options['epid']) as $_dbname) {
				$_dbname = trim($_dbname);
				if (in_array($_dbname, $databases)) {
					continue;
				}
				$databases[] = $_dbname;
			}
		}
		$this->__databases = $databases;

		$this->__opts = $options;


	}

	/**
	 * 主方法
	 * @return boolean
	 */
	public function main() {

		if (empty($this->__opts)) {
			return false;
		}

		// 使用最高权限帐号连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		@$db = &db::init($cfg);

		// 如果待执行的数据库为空，则遍历整个企业OA数据库
		if (!is_array($this->__databases) || empty($this->__databases)) {
			$this->__databases = array();
			$query = $db->query("SHOW DATABASES LIKE 'ep_%';");
			while ($row = $db->fetch_array($query)) {
				if (substr($row['Database'], 0, 3) != 'ep_') {
					// 只提取企业站的数据库
					continue;
				}
				$this->__databases[] = $row['Database'];
			}
		}

		// 引入升级类
		require $this->__execute_file;
		$execute = new execute();

		// 待执行总的数据库数量
		$r_total = count($this->__databases);
		// 当前执行的编号
		$r_num = 0;
		// 启动时间
		$r_starttime = time();
		$r_starttime_s = microtime(true);
		$ignores = array(
			'ep_18520', 'ep_23970', 'ep_21523', 'ep_31734', 'ep_30956', 'ep_34087',
			'ep_27728', 'ep_29040', 'ep_21334', 'ep_30750', 'ep_21879', 'ep_34855',
			'ep_21418', 'ep_18821', 'ep_22414', 'ep_36607', 'ep_35705', 'ep_35872',
			'ep_35874', 'ep_35492', 'ep_10591', 'ep_29680', 'ep_27776', 'ep_29763',
			'ep_10561'
		);
		// 遍历待执行操作的数据库
		foreach ($this->__databases as $dbname) {
			if (in_array($dbname, $ignores)) {
				continue;
			}

			// 新数据库
			$ep_id = substr($dbname, 3);
			if ($ep_id > 36700) {
				$cfg['host'] = '10.66.141.207';
				$cfg['pw'] = '88d8K88rMhQse4MD';
				$tablepre = $cfg['tablepre'];
				$db = db::init($cfg);
			}

			$r_num++;
			try {
				$db->query('use '.$dbname);
				// 开始事务
				$db->query("begin");
				// 读取站点配置信息
				$settings = array();
				$q = $db->query("SELECT * FROM `{$tablepre}common_setting`");
				while ($set = $db->fetch_array($q)) {
					$settings[$set['cs_key']] = $set['cs_type'] ? @unserialize($set['cs_value']) : $set['cs_value'];
				}
				// setting为空
				if (empty($settings)) {
					throw new Exception("database name '{$dbname}' setting is empty");
				}
				// 当前站点域名为空
				if (empty($settings['domain'])) {
					throw new Exception("database name '{$dbname}' 'domain' is empty".print_r($settings, true));
					continue;
				}

				// 初始化执行脚本环境变量
				$execute->init($db, $tablepre, $settings, $this->__opts, array(
					'cachedir' => $this->_site_cache_dir($settings['domain']),
					'dbname' => $dbname,
					'upgrade' => $this
				));

				// 开始脚本执行
				$execute->run();

				// 提交事务
				$db->query("commit");

			} catch (Exception $e) {
				// 错误回滚
				$db->query("rollback");
				$_domain = isset($settings['domain']) ? $settings['domain'] : '-';
				$error = "db: {$dbname};domain: {$_domain};errcode: ".$e->getCode()."; errmsg: ".$e->getMessage();
				logger::error($error);
				$this->_output($error);
				continue;
			}

			$_r_current_time = microtime(true);

			// 平均每个执行的时间
			$_r_pre_time = round(($_r_current_time - $r_starttime_s)/$r_num, 2);
			// 预计执行完成时间
			$_r_over_time = rgmdate(time() + $_r_pre_time * ($r_total - $r_num), 'Y-m-d H:i:s');

			$msg = "{$r_num}/{$r_total}(av. {$_r_pre_time}s, {$_r_over_time} over)[{$dbname}]";

			$this->__output($msg.' site '.$settings['domain'].' upgrade over.', true, false);
		}

	}

	/**
	 * 输出打印消息
	 * @see voa_backend_base::_output()
	 */
	protected function _output($msg, $success = false, $log = 'auto') {
		return $this->__output('site upgrade: '.$msg, $success, $log);
	}

	/**
	 * 获取指定站点的缓存目录
	 * @param string $domain
	 * @return string
	 */
	protected function _site_cache_dir($domain) {
		$dir = voa_h_func::get_sitedir(voa_h_func::get_domain($domain));
		startup_env::set('sitedir', null);

		return $dir;
	}

	public function rfwrite($filename, $data, $method = 'rb+', $iflock = 1, $check = 1, $chmod = 1) {
		$dir = dirname($filename);
		if (!is_dir($dir)) {
			rmkdir($dir, 0777, false);
		}
		$check && check_filepath($filename);
		if(false == ris_writable($filename)) {
			logger::error('Can not write to cache files, please check directory '.$filename.' .');
		}

		touch($filename);
		$handle = fopen($filename, $method);
		$iflock && flock($handle, LOCK_EX);
		fwrite($handle, $data);
		$method == 'rb+' && ftruncate($handle, strlen($data));
		fclose($handle);
		$chmod && @chmod($filename, 0777);
	}

}
