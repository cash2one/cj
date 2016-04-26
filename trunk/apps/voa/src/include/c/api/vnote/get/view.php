<?php
/**
 * 查看备忘
 * $Author$
 * $Id$
 */

class voa_c_api_vnote_get_view extends voa_c_api_vnote_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CC = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {
		// 请求参数
		$fields = array(
			'vn_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		/** 备忘ID */
		$vn_id = rintval($this->request->get('vn_id'));

		/** 读取备忘信息 */
		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$vnote = $serv->fetch_by_id($vn_id);
		if (empty($vn_id) || empty($vnote)) {
			$this->_error_message('vnote_is_not_exists');
		}
	
		/** 判断当前用户是否有权限查看 */
		if (startup_env::get('wbs_uid') != $vnote['m_uid']) {
			$this->_error_message('no_privilege');
		}

		unset($v);
		
		$this->_result = array(
			'uid' 		=> $vnote['m_uid'],
			'username' 	=> $vnote['m_username'],
			'avatar' 	=> voa_h_user::avatar($vnote['m_uid']),
			'message' 	=> $vnote['vn_subject'],
			'created' 	=> $vnote['vn_created'],
			'updated' 	=> $vnote['vn_updated'],
		);
	}

}
