<?php
/**
 * 对评论内容的回复
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_post_reply extends voa_c_api_askfor_base {

	public function execute() {
		
		/*date_default_timezone_set('PRC');
		ini_set('display_errors', 1);
		error_reporting(E_ALL & ~E_NOTICE);
		header('Content-Type:text/html;charset=utf-8');
		$this->_params = array(
			'afc_id' =>	'128',
			'message' =>	'回复一个评论',
		);*/
		
		/*需要的参数*/
		$fields = array(
			'afc_id' => array('type' => 'string_trim', 'required' => true),		//审批id
			'message' => array('type' => 'string_trim', 'required' => true),	//审批内容
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		/** 审批 id */
		$afc_id = intval($this->_params['afc_id']);
		$message = trim($this->_params['message']);
		
		//回复ID检查
		if (!$afc_id) {
			return $this->_set_errcode('afc_id is null');
		}
		//回复内容检查
		if (!$message) {
			return $this->_set_errcode('message is null');
		}

		/** 获取评论信息 */
		$servcmt = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
		$cmt = $servcmt->fetch_by_id($afc_id);
		if (empty($cmt)) {
			$this->_set_errcode('askfor_comment_not_exist', get_referer());
		}
		/** 判断是否有权限 */
		if (!$this->_is_permit($cmt)) {
			return $this->_set_errcode('no_privilege');
		}

		/** 回复信息入库 */
		$servrpy = &service::factory('voa_s_oa_askfor_reply', array('pluginid' => startup_env::get('pluginid')));
		$afr_id = $servrpy->insert(array(
			'afc_id' => $afc_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'afr_message' => $message
		), true);
		$this->_result = array(
			'afr_id' => $afr_id
		);
	}

	/** 判断是否有权限 */
	protected function _is_permit($cmt) {
		/** 如果是发起者, 则 */
		if ($cmt['m_uid'] == startup_env::get('wbs_uid')) {
			return true;
		}
		//如果是自己发起的审批
		$askfor = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$askfor = $askfor->fetch_by_id($cmt['af_id']);
		if($askfor['m_uid'] == startup_env::get('wbs_uid')) {
			return true;
		}

		/** 读取审批想过用户信息 */
		$servp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $servp->fetch_by_af_id($cmt['af_id']);
		/** 判断是否有权限 */
		$permit = false;
		foreach ($procs as $v) {
			if ($v['m_uid'] == startup_env::get('wbs_uid')) {
				$permit = true;
				break;
			}
		}

		return $permit;
	}
}

