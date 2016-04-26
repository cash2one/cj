<?php

/**
 * 活动详情页
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_share extends voa_c_api_campaign_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {
		// 需要的参数
		$fields = array(
			// 活动id
			'id' => array('type' => 'int', 'required' => true), 
			// 销售id
			'saleid' => array('type' => 'int', 'required' => true), 
			// 分享时间戳
			'sharetime' => array('type' => 'int', 'required' => true));
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		$id = $this->_params['id'];
		$saleid = $this->_params['saleid'];
		$sharetime = $this->_params['sharetime'];
		// 获取cookie
		$cookieid = isset($_COOKIE[$id]) ? $_COOKIE[$id] : '';
		// 处理分享
		$uda_insert = &uda::factory('voa_uda_frontend_campaign_insert'); // 统计类
		$firsttime = null;
		if (!$this->_member['m_uid'] && $cookieid != $saleid) {
			// 保存分享记录
			$uda_insert->add_share($id, $saleid, $sharetime, $firsttime);
			$sharetimenow = isset($firsttime['date']) ? $firsttime['date'] : $sharetime;
			// 更新分享数
			$uda_insert->count_share($id, $saleid, rgmdate($sharetimenow, 'Y-m-d'));
		}
		return true;
	}

}
