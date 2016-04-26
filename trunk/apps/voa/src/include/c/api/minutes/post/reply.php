<?php
/**
 * voa_c_api_minutes_reply
 * 对会议纪要内容的评论
 * $Author$
 * $Id$
 */

class voa_c_api_minutes_post_reply extends voa_c_api_minutes_base {

	public function execute() {

		
		//调试信息
		/*date_default_timezone_set('PRC');
		ini_set('display_errors', 1);
		error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
		header('Content-Type:text/html;charset=utf-8');
		$request = controller_request::get_instance();
		$this->_params = array(
			'mi_id' =>	'1',
			'message' =>	'回复会议记录1',
		);
		$request->set_params($this->_params);*/
		
		
		/*请求参数*/
		$fields = array(
			/*会议记录ID*/
			'mi_id' => array('type' => 'int', 'required' => true),
			/*回复信息*/
			'message' => array('type' => 'string_trim', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		/*回复id检查*/
		if (empty($this->_params['mi_id'])) {
			return $this->_set_errcode('mi_id is null');
		}
		/*回复内容检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode('message is null');
		}
		/** 审批 id */
		$mi_id = $this->_params['mi_id'];
		$message = $this->_params['message'];

		/** 获取审批信息 */
		$serv = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$minutes = $serv->fetch_by_id($mi_id);
		if (empty($mi_id) || empty($minutes)) {
			return $this->_set_errcode('minutes_is_not_exists');
		}

		/** 读取权限用户 */
		$serv_m = &service::factory('voa_s_oa_minutes_mem', array('pluginid' => startup_env::get('pluginid')));
		$mem = $serv_m->fetch_by_mi_id_uid($mi_id, $this->_member['m_uid']);

		if (empty($mem)) {
			return $this->_set_errcode('no_privilege');
		}

		/** 评论信息入库 */
		$serv_p = &service::factory('voa_s_oa_minutes_post', array('pluginid' => startup_env::get('pluginid')));
		$this->_return = $serv_p->insert(array(
				'mi_id' => $mi_id,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'mip_message' => $message
		));

		/*返回数组*/
		$this->_result = array(
			
		);
		return true;
	}
}
