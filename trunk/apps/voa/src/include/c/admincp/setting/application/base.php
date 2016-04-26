<?php
/**
 * voa_c_admincp_setting_application_base
 * 企业后台/系统设置/应用维护/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_setting_application_base extends voa_c_admincp_setting_base {

	protected $_uda_get = null;
	/** 是否启用企业微信应用型代理接口 */
	protected $_use_qywx_api = null;
	/** 是否启用了企业微信 */
	protected $_open_wxqy = null;

	/** 需要关闭不能开放给用户使用的应用 */
	protected $_unused_application = array('footprint', 'inspect');

	/** 应用状态 */
	protected $_availables = array(
			'new' => voa_d_oa_common_plugin::AVAILABLE_NEW,
			'wait_open' => voa_d_oa_common_plugin::AVAILABLE_WAIT_OPEN,
			'wait_close' => voa_d_oa_common_plugin::AVAILABLE_WAIT_CLOSE,
			'wait_delete' => voa_d_oa_common_plugin::AVAILABLE_WAIT_DELETE,
			'open' => voa_d_oa_common_plugin::AVAILABLE_OPEN,
			'close' => voa_d_oa_common_plugin::AVAILABLE_CLOSE,
			'delete' => voa_d_oa_common_plugin::AVAILABLE_DELETE,
	);

	/** 取消请求时的文字 */
	protected $_cancel_languages = array(
			voa_d_oa_common_plugin::AVAILABLE_WAIT_OPEN => '正在审核开启',
			voa_d_oa_common_plugin::AVAILABLE_WAIT_CLOSE => '正在审核关闭',
			voa_d_oa_common_plugin::AVAILABLE_WAIT_DELETE => '正在审核删除',
	);

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		if ($this->_uda_get === null) {
			$this->_uda_get = &uda::factory('voa_uda_frontend_application_get');
			$this->_use_qywx_api = $this->_uda_get->use_qywx_api;
			$this->_open_wxqy = $this->_uda_get->open_wxqy;
		}

		// 注入微信名词
		$this->_wechat_lang_set();

		$this->view->set('use_qywx_api', $this->_use_qywx_api);
		$this->view->set('cancel_languages', $this->_cancel_languages);
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 绑定应用操作
	 * @param string $agentid
	 * @param number $cp_pluginid
	 * @param string $suiteid
	 * @return boolean
	 */
	protected function _bind_agent($agentid, $cp_pluginid, $suiteid, $appid = 0) {

		$uda_suite = new voa_uda_frontend_application_suite();

		startup_env::set('pluginid', $cp_pluginid);

		// 应用图标
		$logo_file = '';
		if ($this->_plugin['cp_icon']) {
			$logo_file = $this->_plugin['cp_icon'];
		} else {
			$logo_file = '_default.jpg';
		}
		// 只上传jpg格式图标
		$logo_file = APP_PATH.'/admincp/static/images/application/'.str_ireplace('.png', '.jpg', $logo_file);
		if (!$uda_suite->open($cp_pluginid, $agentid, $suiteid, $appid, $logo_file)) {
			$errcode = $uda_suite->errcode;
			$errmsg = $uda_suite->errmsg;
		} else {
			$errcode = 0;
			$errmsg = '';
		}

		return array($errcode, $errmsg);
	}

}
