<?php
/**
 * voa_uda_frontend_application_base
 * 应用基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_base extends voa_uda_frontend_base {

	// 独立安装
	const INSTALL_MODE_ALONE = 2;
	// 标准安装
	const INSTALL_MODE_STANDARD = 1;
	/** 应用分组表 */
	public $serv_module_group = null;
	/** 站点应用表 */
	public $serv_plugin = null;
	/** 菜单表 */
	public $serv_cpmenu = null;
	/** 应用分组表 */
	public $serv_plugin_group = null;

	/** 应用分组列表 */
	protected $_module_group_list = null;
	/** 站点应用列表 */
	protected $_plugin_list = null;
	/** 应用分组表 */
	protected $_plugin_group_list = null;

	// TODO 设置是否使用企业微信接口，
	/** 是否使用企业微信接口 设置为：qywx=使用企业微信接口（目前未开通）、cyadmin=使用畅移后台开通、self=自主开通 */
	public $use_qywx_api = 'self';

	/** 是否使用企业微信接口设置应用的菜单。false不使用接口，true使用 */
	public $use_qywx_menu_api = true;

	/** 是否开启了企业微信帐号 */
	public $open_wxqy = false;

	/** 同一个应用两次变更状态的最小间隔时间。单位：秒 */
	public $status_change_expire = 86400;

	/** 开启应用时是否清空历史数据 */
	public $truncate_data = false;

	/** 应用开启状态映射关系 */
	public $available_types = array(
			'wait_open' => voa_d_oa_common_plugin::AVAILABLE_WAIT_OPEN,
			'wait_close' => voa_d_oa_common_plugin::AVAILABLE_WAIT_CLOSE,
			'wait_delete' => voa_d_oa_common_plugin::AVAILABLE_WAIT_DELETE,
			'open' => voa_d_oa_common_plugin::AVAILABLE_OPEN,
			'close' => voa_d_oa_common_plugin::AVAILABLE_CLOSE,
			'delete' => voa_d_oa_common_plugin::AVAILABLE_DELETE,
	);

	public function __construct() {
		parent::__construct();
        // 根据全局环境重置设置
        $this->use_qywx_api = config::get('voa.use_qywx_api');
        $this->use_qywx_menu_api = config::get('voa.use_qywx_menu_api');
        $this->status_change_expire = config::get('voa.status_change_expire');

        $settings = voa_h_cache::get_instance()->get('setting', 'oa');
        $this->open_wxqy = !empty($settings['ep_wxqy']) ? true : false;

		if ($this->serv_module_group === null) {
			$this->serv_module_group = &service::factory('voa_s_oa_common_module_group', array('pluginid' => 0));
		}
		if ($this->serv_plugin === null) {
			$this->serv_plugin = &service::factory('voa_s_oa_common_plugin', array('pluginid' => 0));
		}
		if ($this->serv_cpmenu === null) {
			$this->serv_cpmenu = &service::factory('voa_s_oa_common_cpmenu', array('pluginid' => 0));
		}
		if ($this->serv_plugin_group === null) {
			$this->serv_plugin_group = &service::factory('voa_s_oa_common_plugin_group', array('pluginid' => 0));
		}
	}

	/**
	 * 获取应用分组列表
	 * @param number $cpg_id 指定某个分组信息，不指定则返回全部
	 * @param string $force 是否强制读取已删除的
	 * @return multitype:
	 */
	protected function _plugin_group_list($cpg_id = 0, $force = false) {

		if ($this->_plugin_group_list === null) {
			$this->_plugin_group_list = $this->serv_plugin_group->fetch_all(0, 0, true);
		}
		$plugin_group_list = $this->_plugin_group_list;

		// 只读取未删除的
		if (!$force) {
			foreach ($plugin_group_list as $_id => $_data) {
				if ($_data['cpg_status'] == voa_d_oa_common_plugin_group::STATUS_REMOVE) {
					unset($plugin_group_list[$_id]);
				}
			}
			unset($_id, $_data);
		}

		// 读取指定分组
		if ($cpg_id) {
			return isset($plugin_group_list[$cpg_id]) ? $plugin_group_list[$cpg_id] : array();
		}

		return $plugin_group_list;
	}

	/**
	 * 获取系统模块组列表
	 * @param number $cmg_id 指定某个模块组信息，不指定返回全部
	 * @param boolean $force = false 是否强制读取已删除的
	 * @return array
	 */
	protected function _module_group_list($cmg_id = 0, $force = FALSE) {
		if ($this->_module_group_list === null) {
			$this->_module_group_list = $this->serv_module_group->fetch_all(0, 0, true);
		}
		$module_group_list = $this->_module_group_list;

		if (!$force) {
			// 只读取未删除的
			foreach ($module_group_list as $_id => $_data) {
				if ($_data['cmg_status'] == voa_d_oa_common_module_group::STATUS_REMOVE) {
					// 移除已删除的数据
					unset($module_group_list[$_id]);
				}
			}
			unset($_id, $_data);
		}

		if ($cmg_id) {
			// 读取指定的分组
			return isset($module_group_list[$cmg_id]) ? $module_group_list[$cmg_id] : array();
		}

		return $module_group_list;
	}

	/**
	 * 获取站点的插件列表
	 * @param number $cp_pluginid 指定某个插件信息，不指定返回全部
	 * @param array $cp_available 显示具体某类状态的 为空显示全部
	 * @param boolean $force = false 是否强制读取已删除的，true显示全部
	 * @return array
	 */
	protected function _plugin_list($cp_pluginid = 0, $cp_available = array(), $force = FALSE, $force_read = FALSE) {
		if (is_null($this->_plugin_list) || $force_read) {
			$this->_plugin_list = $this->serv_plugin->fetch_all(0, 0, true);
		}
		$plugin_list = $this->_plugin_list;

		if ($cp_available) {
			// 只读取某个状态范围的
			$cp_available = (array)$cp_available;
			foreach ($plugin_list as $_id => $_data) {
				if (!in_array($_data['cp_available'], $cp_available)) {
					// 移除非指定范围状态的
					unset($plugin_list[$_id]);
				}
			}
			unset($_id, $_data);
		}

		if (!$force) {
			// 只读取未删除的
			foreach ($plugin_list as $_id => $_data) {
				if ($_data['cp_status'] == voa_d_oa_common_plugin::STATUS_REMOVE) {
					// 移除已标记为删除的
					unset($plugin_list[$_id]);
				}
			}
			unset($_id, $_data);
		}
		if ($cp_pluginid) {
			return isset($plugin_list[$cp_pluginid]) ? $plugin_list[$cp_pluginid] : array();
		}
		return $plugin_list;
	}

	/**
	 * 格式化插件信息
	 * @param array $plugin
	 * @return array
	 */
	public function plugin_format($plugin) {
		$plugin['_icon'] = $this->application_icon_url($plugin['cp_icon']);
		return $plugin;
	}

	/**
	 * 返回一个应用图标的绝对Url
	 * @param string $icon
	 * @return string
	 */
	public function application_icon_url($icon = '') {
		if ($icon && preg_match('/^[0-9]+$/', $icon)) {
			// 以数字开头的路径被认为是本地上传的附件ID
			return voa_h_attach::attachment_url($icon);
		} else {
			// 否则，认为是系统内置的固定位置
			$setting = voa_h_cache::get_instance()->get('setting', 'oa');
			$scheme = config::get('voa.oa_http_scheme');
			return $scheme.$setting['domain'].APP_STATIC_URL.'images/application/'.$icon;
		}
	}

	/**
	 * 变更应用状态
	 * @param number $cp_pluginid
	 * @param string $type
	 * @return boolean
	 */
	public function update_available($cp_pluginid = 0, $type = '', $qywx_application_agent = array()) {

		if (!isset($this->available_types[$type])) {
			$this->errmsg(1000, '未知的应用状态');
			return false;
		}

		$update = array();
		$update['cp_available'] = $this->available_types[$type];
		$update['cp_lastavailable'] = startup_env::get('timestamp');
		if (!empty($qywx_application_agent['agentid'])) {
			$update['cp_agentid'] = $qywx_application_agent['agentid'];
		} else {
			$update['cp_agentid'] = 0;
		}

		if ($type == 'open') {
			// 如果是开启应用，则标记最后启用时间
			// 此字段为了告诉开启应用时是否要导入默认数据
			$update['cp_lastopen'] = startup_env::get('timestamp');
			// 标记应用所在的套件ID
			$update['cp_suiteid'] = isset($qywx_application_agent['suiteid']) ? $qywx_application_agent['suiteid'] : '';
		} else {
			// 移除应用的套件ID
			$update['cp_suiteid'] = '';
			$update['cp_agentid'] = '';
		}

		if (!empty($qywx_application_agent['cyea_id'])) {
			// 如果给出了畅移的应用请求id则写入
			$update['cyea_id'] = $qywx_application_agent['cyea_id'];
		}

		$this->serv_plugin->update($update, array('cp_pluginid' => $cp_pluginid));
		return true;
	}

	/**
	 * 获取指定 cp_pluginid 的应用信息
	 * @param number $cp_pluginid
	 * @return array
	 */
	public function get_plugin($cp_pluginid) {
		if (isset($this->_plugin_list[$cp_pluginid])) {
			return $this->_plugin_list[$cp_pluginid];
		}

		return $this->_plugin_list($cp_pluginid, array(), false);
	}

	/**
	 * 检查指定应用上次变更状态的时间
	 * @param number $cp_pluginid
	 * @return boolean
	 */
	public function check_status_change_expire($cp_pluginid) {
		$plugin = $this->get_plugin($cp_pluginid);
		if (empty($plugin)) {
			$this->errmsg(2000, '指定应用信息不存在 或 已下架');
			return false;
		}

		// TODO 对应用不再限制两次操作时间间隔限制
		return true;

		if (startup_env::get('timestamp') - $plugin['cp_lastavailable'] < $this->status_change_expire) {
			$this->errmsg(2001, round($this->status_change_expire/3600, 0).'小时内禁止频繁对同一应用请求开启和关闭以及删除操作');
			return false;
		}
		return true;
	}

	/**
	 * 尝试清理系统菜单表内指定应用的菜单数据
	 * @param number $pluginid
	 * @return boolean
	 */
	public function clear_cpmenu($pluginid) {
		$data = array(
			'ccm_display' => 0,
		);
		$this->serv_cpmenu->update_by_cp_pluginid_ccm_module($data, $pluginid);
		return true;
	}

	/**
	 * 畅移客服接口
	 * @param number $cp_pluginid
	 * @param string $type open|close|delete
	 * @param number $cyea_id <strong style="color:red">(引用结果)</strong>应用在畅移后台对应的ID
	 * @return boolean
	 */
	public function vchangyi_application_api($cp_pluginid, $type, &$cyea_id = 0) {

		// 判断是否独立部署
		if (self::INSTALL_MODE_ALONE == config::get('voa.install_mode')) {
			return true;
		}

		// 应用信息
		$plugin = $this->_plugin_list($cp_pluginid, array(), false, true);
		if (empty($plugin)) {
			$this->errmsg(1001, '通知客服接口的应用不存在');
			return false;
		}

		// 系统设置
		$setting = voa_h_cache::get_instance()->get('setting', 'oa');
		if (!isset($setting['ep_id']) || empty($setting['ep_id'])) {
			$this->errmsg(1002, '通知客服接口的企业 ID 发生错误['.(!isset($setting['ep_id']) ? 0 : 1).']');
			return false;
		}

		$plugin = $this->plugin_format($plugin);

		// 构造待传输的数据
		$application = array(
			'name' => $plugin['cp_name'],
			'icon' => $plugin['_icon'],
			'desc' => $plugin['cp_description'],
			'pluginid' => $cp_pluginid,
			'ep_id' => $setting['ep_id'],
			'agentid' => $plugin['cp_agentid'],
			'appstatus' => $type
		);

		// 修改应用编辑操作
		$data = array();
		$url = config::get('voa.cyadmin_url') . 'OaRpc/Rpc/EnterpriseApp';
		if (!voa_h_rpc::query($data, $url, 'updateApp', $application, true)) {
			$this->errmsg($data['errcode'], $data['errmsg']);
			return false;
		}

		$cyea_id = $data['ea_id'];

		return true;
	}

}
