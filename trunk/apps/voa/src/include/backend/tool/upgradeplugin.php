<?php
/**
 * upgradeplugin.php
 * 升级应用专用工具
 *
 * php -q APP_PATH/backend/tool.php -n upgradeplugin
 * -pluginname 应用的唯一标识名（必须提供）
 * -version 应用的目标版本号（必须提供）具体根据应用升级目录而定，一般推荐使用ymd+数字来命名
 * -epid 执行的企业数据库范围（必须提供）如：1001,1234。则执行ep_1001到ep_1234的数据库（会自动忽略不存在的库），或者，直接写要升级的数据库名，多个之间使用半角逗号分隔
 * -action 执行的动作（可选），如设置则执行具体的动作。不指定则按需顺序执行全部动作
 * 可选值：dbtable=升级数据表,cpmenu=升级后台菜单,wxmenu=升级微信企业号自定义菜单,cachecpemnu=更新菜单缓存
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_backend_tool_upgradeplugin extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	private $__upgradeplugin_dir = '';
	private $__databases = 0;

	public function __construct($opts) {

		parent::__construct();

		$actions = array('dbtable', 'cpmenu', 'wxmenu');
		// 默认的全部参数定义
		// 参数名 => 是否必须提供
		$default_opts = array(
			'pluginname' => true,// 插件的唯一标识名
			'version' => true,// 应用目标版本号
			'epid' => true,// 需要执行的企业ep_id范围，格式为：xxxx,yyyy
			'action' => false,// 需要执行的具体指定的动作
		);
		$options = array();
		foreach ($default_opts as $option => $required) {
			$value = isset($opts[$option]) ? trim($opts[$option]) : null;
			if ($required && $value === null) {
				return $this->_output("option '-".$option."' is must");
			}
			$options[$option] = $value;
		}

		// 检查应用唯一标识名是否正确有效
		if (empty($options['pluginname']) || preg_match('/['.preg_quote('\/:*?"<>|.', '/').']/', $options['pluginname'])) {
			return $this->_output("option '-pluginname' value is invalid character");
		}
		$this->__upgradeplugin_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'upgradeplugin'.DIRECTORY_SEPARATOR.$options['pluginname'].DIRECTORY_SEPARATOR;
		if (!is_dir($this->__upgradeplugin_dir)) {
			return $this->_output("option '-pluginname' value invalid");
		}

		// 检查应用版本号是否有效
		if (empty($options['version']) || preg_match('/['.preg_quote('\/:*?"<>|.', '/').']/', $options['version'])) {
			return $this->_output("option '-version' value is invalid character");
		}
		$this->__upgradeplugin_dir .= $options['version'].DIRECTORY_SEPARATOR;
		if (!is_dir($this->__upgradeplugin_dir)) {
			return $this->_output("option '-version' value invalid or not exists");
		}

		// 检查企业ID执行范围是否正确
		if (empty($options['epid'])) {
			return $this->_output("option '-epid' value invalid");
		}

		$databases = array();
		if (preg_match('/^\s*([\d]+)\s*,\s*([\d]+)\s*$/', $options['epid'], $match)) {
			// 以数据库ep_id为范围的执行
			if ($match[1] > $match[2]) {
				return $this->_output("option '-epid' number range error");
			}

			for ($i = $match[1]; $i <= $match[2]; ++ $i) {
				$databases[] = 'ep_'.$i;
			}
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


		// 检查执行动作定义是否正确
		if (!empty($options['action']) && !in_array(rstrtolower($options['action']), $actions)) {
			return $this->_output("option '-action' value invalid. dbtable|cpmenu|wxmenu");
		}
		if ($options['action'] !== null) {
			$options['action'] = array(rstrtolower($options['action']));
		} else {
			$options['action'] = $actions;
		}

		$this->__opts = $options;


	}

	public function main() {

		if (empty($this->__opts)) {
			return false;
		}

		// 使用最高权限帐号连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		// 引入升级类
		require $this->__upgradeplugin_dir.'execute.php';
		$classname = 'execute.php';
		$execute = new execute();

		foreach ($this->__databases as $dbname) {
			try {
				$db->query('use '.$dbname);

				$q = $db->query("SELECT * FROM `{$tablepre}common_plugin` WHERE `cp_identifier`='{$this->__opts['pluginname']}' LIMIT 1");
				if (!$plugin = $db->fetch_array($q)) {
					// 应用不存在则跳过
					$this->_output("cp_identifier=".$this->__opts['pluginname']." not exists");
					continue;
				}

				// 应用所在模块组
				$cpmenu_module = $db->result_first("SELECT `cmg_dir` FROM `{$tablepre}common_module_group` WHERE `cmg_id`='{$plugin['cmg_id']}' LIMIT 1");

				// 读取站点配置信息
				$settings = array();
				$q = $db->query("SELECT * FROM `{$tablepre}common_setting`");
				while ($set = $db->fetch_array($q)) {
					$settings[$set['cs_key']] = $set['cs_type'] ? @unserialize($set['cs_value']) : $set['cs_value'];
				}
				if (empty($settings)) {
					$this->_output("cp_identifier=".$this->__opts['pluginname']." setting empty");
					continue;
				}

				if (empty($settings['domain'])) {
					$this->_output("cp_identifier=".$this->__opts['pluginname']." domain empty".print_r($settings, true));
					continue;
				}

				$execute->init($db, $settings, array(
					'options' => $this->__opts,
					'plugin' => $plugin,
					'tablepre' => $tablepre,
					'cpmenu_module' => $cpmenu_module,
				));
				foreach ($this->__opts['action'] as $act) {

					$result = null;

					if ($act == 'dbtable') {
						// 升级数据表
						$result = $execute->dbtable();
					} elseif ($act == 'cpmenu') {
						// 后台菜单升级
						if ($plugin['cp_available'] != 4) {
							// 应用未启用
							$this->_output("cp_identifier=".$this->__opts['pluginname']." no open");
							continue;
						}
						$result = $execute->cpmenu();
					} elseif ($act == 'wxmenu') {
						// 微信企业号自定义菜单升级
						if ($plugin['cp_available'] != 4 || !$plugin['cp_agentid']) {
							// 应用未开启 或 微信企业号应用ID不存在 则忽略
							$this->_output("cp_identifier=".$this->__opts['pluginname']." wechat appid empty");
							continue;
						}
						$result = $execute->wxmenu();
					} else {
						$this->_output("cp_identifier=".$this->__opts['pluginname']." action '{$act}' error");
						continue;
					}

					if ($result === null) {
						continue;
					}

					if ($result === false) {
						break;
					}
				}

				// 更新菜单缓存
				unlink(voa_h_func::get_sitedir(voa_h_func::get_domain($settings['domain'])).'cpmenu.php');
				startup_env::set('sitedir', null);

			} catch (Exception $e) {
				$this->_output("run error ".print_r($e, true));
				continue;
			}
		}


	}

	/**
	 * 输出打印消息
	 * @see voa_backend_base::_output()
	 */
	protected function _output($msg, $success = false, $log = 'auto') {
		return $this->__output('plugin upgrade: '.$msg, $success, $log);
	}

}
