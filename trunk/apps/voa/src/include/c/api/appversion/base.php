<?php
/**
 * api 接口
 * APP版本信息相关
 * $Author$
 * $Id$
 */

class voa_c_api_appversion_base extends voa_c_api_base {

	/** uda app 版本信息操作 */
	protected $_uda_appversion_get = null;

	/** app 客户端类型文字小写格式与真实格式映射关系 */
	protected $_app_client_type_map = array();

	public function __construct() {
		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		if ($this->_uda_appversion_get === null) {
			$this->_uda_appversion_get = &uda::factory('voa_uda_uc_appversion_get');
			$this->_app_client_type_map = $this->_uda_appversion_get->app_client_type_map;
		}

		return true;
	}

	protected function _after_action($action) {
		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

}
