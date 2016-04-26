<?php
/**
 * voa_c_admincp_system_profile_base
 * 企业后台/系统设置/我的资料/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_profile_base extends voa_c_admincp_system_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		return false;
	}

	/**
	 * 获取管理员详情，不存在则返回数据字段默认值
	 * /system/profile/base
	 * @param number $m_uid
	 * @return array
	 */
	protected function _adminer_detail($m_uid = 0) {

		if ($m_uid) {
			$adminer = $this->_adminer($m_uid);
			$adminer = $adminer ? $adminer : self::_adminer_detail(0);
		} else {
			$adminer = $this->_service_single('common_adminer', 'fetch_all_field', array());
		}

		return $adminer;
	}

	/**
	 * 返回指定管理员信息
	 * /system/profile/base
	 * @param number $m_uid
	 */
	protected function _adminer($m_uid){
		return $this->_service_single('common_adminer', 'fetch', $m_uid);
	}

}
