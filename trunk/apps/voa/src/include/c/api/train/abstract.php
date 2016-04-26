<?php
/**
 * voa_c_api_train_abstract
 * 培训基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_train_abstract extends voa_c_api_base {
	/** 插件id */
	protected $_pluginid = 0;
	// 插件名称
	protected $_pluginname = 'train';
	// 表格名称
	protected $_tname = 'train';

	protected  $_perpage = 10;

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	/**
	 * 身份检查
	 * @return void
	 */
	protected function _access_check() {

		// 取后台登录信息
		$uda_member_get = &uda::factory('voa_uda_frontend_adminer_get');
		// cookie 信息
		$cookie_data = array();
		$uda_member_get->adminer_auth_by_cookie($cookie_data, $this->session);
		if (!empty($cookie_data['uid']) && 0 < $cookie_data['uid']) {
			// 如果后台登陆信息存在, 则清理前台登陆账号
			$this->session->remove('uid');
			$this->_require_login = false;
		}

		return parent::_access_check();
	}

	/**
	 * 返回真实页码
	 * @param int  $page 页码
	 * @return int
	 */
	protected  function _get_real_page($page) {

		$page = (int)$page;
		if ($page > 1) {//如果页码不为0或负数
			return $page;
		}
		return 1;
	}

	/**
	 * 将所有输出转义
	 * @param array  $lists 数据数组
	 * @return int
	 */
	protected function transform_output(&$lists) {

		if ($lists) {
			foreach ($lists as $k => $list) {
				if (is_array ($list)) {
					$this->transform_output($list);
				} else {
					$list = rhtmlspecialchars($list);
				}
				$lists[$k] = $list;
			}
		}

	}

}
