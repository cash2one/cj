<?php
/**
 * execute.php
 * 升级脚本
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class execute {

	protected $_settings = array();
	protected $_params = array();
	protected $_db = null;
	protected $_plugin = array();
	protected $_options = array();
	protected $_tablepre = 'oa_';

	public function __construct() {

	}

	public function init($db, $settings, $params) {
		$this->_db = $db;
		$this->_settings = $settings;
		$this->_params = $params;
		$this->_plugin = $params['plugin'];
		$this->_options = $params['options'];
		$this->_tablepre = $params['tablepre'];
	}

	/**
	 * 升级数据表
	 * @return boolean
	 */
	public function dbtable() {

		$prefix = $this->_tablepre;
		$suffix = '';

		// 默认配置信息
		$default_settings = array(
			'available_distance' => '1000',
			'late_range' => '600',
			'leave_early_range' => '600',
			'locationx' => '23.134521',
			'locationy' => '113.358803',
			'pluginid' => '14',
			'sign_begin_hi' => '05:00',
			'sign_end_hi' => '23:59',
			'sign_expires' => '30',
			'work_begin_hi' => '09:00',
			'work_days' => unserialize('a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}'),
			'work_end_hi' => '17:00',
			'up_position_rate' => 60,
		);

		$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `{$prefix}sign_location{$suffix}` (
  `sl_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到人UID',
  `m_username` varchar(54) NOT NULL DEFAULT '' COMMENT '签到人名称',
  `sl_signtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
  `sl_ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '签到IP',
  `sl_longitude` decimal(9,6) NOT NULL COMMENT '经度',
  `sl_latitude` decimal(9,6) NOT NULL COMMENT '纬度',
  `sl_address` varchar(255) NOT NULL COMMENT '地址',
  `sl_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `sl_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `sl_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sl_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到上报位置表';

EOF;

		$sql_data = str_replace(array("\r\n", "\r"), "\n", $sql);
		$query_line_list = array();
		// 拆解完全的SQL语句，每个完整的SQL为一组$_query_block
		foreach(explode(";\n", trim($sql_data)."\n") as $_query_block) {
			$_query_block = trim($_query_block);
			if (empty($_query_block)) {
				//跳过空的
				continue;
			}

			// 将SQL语句分解为行，进行解析
			$query_line = '';
			foreach(explode("\n", trim($_query_block)) as $_block) {
				$_block = trim($_block);
				if ($_block === '' || $_block[0] == '#') {
					//空行 或 行首为#注释 则跳过
					continue;
				}
				if (isset($_block[1]) && $_block[0].$_block[1] == '--') {
					//行注释则跳过
					continue;
				}
				$query_line .= $_block;
			}

			if (empty($query_line)) {
				//SQL语句为空，则跳过
				continue;
			}

			$this->_db->query($query_line);
		}

		// 读取已有的配置信息
		$sets = array();
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}sign_setting` WHERE `ss_key` IN ('".implode("', '", array_keys($default_settings))."') AND `ss_status`<3");
		while ($row = $this->_db->fetch_array($query)) {
			$sets[$row['ss_key']] = $row['ss_value'];
		}

		foreach ($default_settings as $key => $value) {
			if (isset($sets[$key])) {
				// 存在此配置则忽略
				continue;
			}

			$type = 0;
			if (is_array($value)) {
				$type = 1;
			}

			// 新增新配置
			$data = array(
				'ss_key' => $key,
				'ss_value' => $type ? serialize($value) : $value,
				'ss_type' => $type,
				'ss_status' => 1,
				'ss_created' => startup_env::get('timestamp'),
				'ss_updated' => startup_env::get('timestamp')
			);
			$this->_db->query("REPLACE INTO `{$this->_tablepre}sign_setting` (`".implode("`, `", array_keys($data))."`) VALUES ('".implode("', '", array_values($data))."')");
		}

		return true;
	}

	/**
	 * 升级后台菜单
	 * @return boolean
	 */
	public function cpmenu() {

		// 找到所有该应用的后台菜单
		$query = $this->_db->query("SELECT * FROM `{$this->_tablepre}common_cpmenu` WHERE `ccm_operation`='{$this->_plugin['cp_identifier']}' AND `ccm_status`<3");
		while ($row = $this->_db->fetch_array($query)) {
			$cpmenus[$row['ccm_module']][$row['ccm_operation']][$row['ccm_subop']] = $row;
		}

		// 后台管理菜单
		$admincp_menu = config::get(startup_env::get('app_name').'.application.'.$this->_plugin['cp_identifier'].'.menu.admincp');
		// 后台应用设置菜单
		$setting_menu = config::get(startup_env::get('app_name').'.application.'.$this->_plugin['cp_identifier'].'.menu.setting');

		$menus = array();
		if ($admincp_menu) {
			$menus[$this->_params['cpmenu_module']] = $admincp_menu;
			$menus[$this->_params['cpmenu_module']][''] = array('icon' => '', 'display' => 1, 'name' => $this->_plugin['cp_name'], 'default' => -1);
		}
		if ($setting_menu) {
			$menus['setting'] = $setting_menu;
			$menus['setting'][''] = array('icon' => '', 'display' => 1, 'name' => $this->_plugin['cp_name'], 'default' => -1);
		}

		if (empty($menus)) {
			return null;
		}

		// 新建或更新菜单
		foreach ($menus as $ccm_module => $_menus) {
			foreach ($_menus as $subop => $set) {

				$data = array(
					'cp_pluginid' => $this->_plugin['cp_pluginid'],
					'ccm_module' => $ccm_module,
					'ccm_operation' => $this->_plugin['cp_identifier'],
					'ccm_subop' => $subop ? $subop : '',
					'ccm_system' => 0,
					'ccm_type' => $subop ? 'subop' : 'operation',
					'ccm_default' => isset($set['default']) ? ($set['default'] > 0 ? $set['default'] : 0) : 0,
					'ccm_name' => $set['name'],
					'ccm_icon' => $set['icon'],
					'ccm_display' => isset($set['display']) ? $set['display'] : 1,
					//'ccm_subnavdisplay' => $set['subnavdisplay'],
					'ccm_status' => 1,
					'ccm_created' => startup_env::get('timestamp'),
					'ccm_updated' => startup_env::get('timestamp')
				);

				if (isset($cpmenus[$ccm_module][$this->_plugin['cp_identifier']][$subop])) {
					// 菜单存在，则检查是否一致
					$update = array();
					$check_fields = array('ccm_system', 'ccm_default', 'ccm_name', 'ccm_icon', 'ccm_display');
					foreach ($check_fields as $_f) {
						if ($_f == 'ccm_default' && $set['default'] < 0) {
							continue;
						}
						if ($data[$_f] != $cpmenus[$ccm_module][$this->_plugin['cp_identifier']][$subop][$_f]) {
							$update[] = "`{$_f}`='{$data[$_f]}'";
						}
					}
					if (!empty($update)) {
						$this->_db->query("UPDATE `{$this->_tablepre}common_cpmenu` SET ".implode(', ', $update)." WHERE `ccm_id`='".$cpmenus[$ccm_module][$this->_plugin['cp_identifier']][$subop]['ccm_id']."'");
					}
				} else {
					// 不存在，则创建
					$this->_db->query("INSERT INTO `{$this->_tablepre}common_cpmenu` (`".implode("`, `", array_keys($data))."`) VALUES ('".implode("', '", array_values($data))."')");
				}

			}
		}

		// 移除已无用的菜单
		foreach ($cpmenus as $_ccm_module => $array) {
			foreach ($array as $_ccm_operation => $array2) {
				foreach ($array2 as $_ccm_subop => $_menu) {
					if (!isset($menus[$_ccm_module][$_ccm_subop])) {
						// 已经删除的菜单
						$this->_db->query("UPDATE `{$this->_tablepre}common_cpmenu` SET `ccm_status`=3, `ccm_deleted`=".startup_env::get('timestamp')." WHERE `ccm_id`={$_menu['ccm_id']}");
					}
				}
			}
		}

		return true;
	}

	/**
	 * 微信企业号自定义菜单升级
	 * @return boolean
	 */
	public function wxmenu() {

		list($domain) = explode('.', $this->_settings['domain']);

		// 读取微信企业号自定义菜单配置
		$qywx_menu = config::get(startup_env::get('app_name').'.application.'.$this->_plugin['cp_identifier'].'.menu.qywx');
		if (empty($qywx_menu)) {
			return true;
		}

		exec('php -q '.APP_PATH.'/backend/tool.php -n agentmenu -domain '.$domain.' -plugin '.$this->_plugin['cp_identifier'].' -pluginid '.$this->_plugin['cp_pluginid'].' -agentid '.$this->_plugin['cp_agentid'].' -action create > /dev/null &');

		return true;
	}

}
