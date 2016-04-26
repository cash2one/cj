<?php

/**
 * voa_c_api_activity_post_sign
 * 活动报名-报名
 * $Author$
 * $Id$
 */
class voa_c_api_activity_post_sign extends voa_c_api_activity_base {

	public function execute() {
		//获取参数
		$fields = array(
			'acid' => array('type' => 'int', 'required' => false),
			'apid' => array('type' => 'int', 'required' => false),
			'ac' => array('type' => 'string', 'required' => true),
			'm_uid' => array('type' => 'string', 'required' => false),
			'message' => array('type' => 'string', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		$request['acid'] = $this->_params['acid'];
		$request['apid'] = $this->_params['apid'];
		$request['ac'] = $this->_params['ac'];
		$request['m_uid'] = $this->_params['m_uid'];
		$request['message'] = $this->_params['message'];
		$request['session'] = $this->session;
		$request['_setting'] = $this->_setting;
		$result = array();
		$uda_sign = &uda::factory('voa_uda_frontend_activity_sign');
		$uda_sign->doit($request, $result);


		// 输出结果
		$this->_result = $result;

		// $this->_result = array(
		// 'url' => 'asdfasdfh,',
		// 'message' => ''
		// );
		return true;
	}
}
