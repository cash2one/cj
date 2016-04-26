<?php
/**
 * voa_c_cyadmin_manage_base
 * 主站后台/后台管理/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_base extends voa_c_cyadmin_base {

	/**
	 * 管理组列表
	 * @var array
	 */
	protected $_adminergroup_list = array();

	/**
	 * 管理员锁定状态描述映射
	 * @var array
	 */
	protected $_adminer_locked_map	=	array(
			voa_d_cyadmin_common_adminer::LOCKED_NO => '允许登录',
			voa_d_cyadmin_common_adminer::LOCKED_YES => '禁止登录',
			voa_d_cyadmin_common_adminer::LOCKED_SYS => '系统帐号',
	);

	/**
	 * 管理组启用状态描述映射
	 * @var array
	*/
	protected $_adminergroup_enable_map = array(
			voa_d_oa_common_adminergroup::ENABLE_YES => '允许登录',
			voa_d_oa_common_adminergroup::ENABLE_NO => '禁止登录',
			voa_d_oa_common_adminergroup::ENABLE_SYS => '系统权限组',
	);

	/** 管理组表 service */
	protected $_serv_adminergroup = null;
	/** 管理员表 service */
	protected $_serv_adminer = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_serv_adminergroup = &service::factory('voa_s_cyadmin_common_adminergroup', array('pluginid' => 0));
		$this->_serv_adminer = &service::factory('voa_s_cyadmin_common_adminer', array('pluginid' => 0));
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 格式化管理组信息
	 * @param array $adminergroup
	 * @return array
	 */
	protected function _adminergroup_format($adminergroup) {
		$adminergroup['_enable'] = isset($this->_adminergroup_enable_map[$adminergroup['cag_enable']]) ? $this->_adminergroup_enable_map[$adminergroup['cag_enable']] : '';
		$adminergroup['_updated'] = rgmdate($adminergroup['cag_updated']);
		$adminergroup['_role'] = explode(',', $adminergroup['cag_role']);

		return $adminergroup;
	}

	/**
	 * 管理组列表
	 * @return array
	 */
	protected function _adminergroup_list(){
		if (!empty($this->_adminergroup_list)) {
			return $this->_adminergroup_list;
		}
		$list = $this->_serv_adminergroup->fetch_all();
		foreach ($list as &$_cag) {
			$_cag = $this->_adminergroup_format($_cag);
		}
		unset($_cag);
		$this->_adminergroup_list = $list;

		return $list;
	}

}
