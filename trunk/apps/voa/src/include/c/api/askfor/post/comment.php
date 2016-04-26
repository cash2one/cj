<?php
/**
 * 对审批内容的评论
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_post_comment extends voa_c_api_askfor_base {
	protected $_askfor = array();

	public function execute() {
		
		//调试信息
		/*date_default_timezone_set('PRC');
		ini_set('display_errors', 1);
		error_reporting(E_ALL & ~E_NOTICE);
		header('Content-Type:text/html;charset=utf-8');
		$this->_params = array(
			'af_id' =>	'55',
			'message' =>	'修改一个备忘录',
		);*/
		
		/*需要的参数*/
		$fields = array(
			'af_id' => array('type' => 'string_trim', 'required' => true),		//审批id
			'message' => array('type' => 'string_trim', 'required' => true),	//审批内容
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		/** 审批 id */
		$af_id = intval($this->_params['af_id']);
		$message = trim($this->_params['message']);
		
		//回复ID检查
		if (!$af_id) {
			return $this->_set_errcode('af_id is null');
		}
		//回复内容检查
		if (!$message) {
			return $this->_set_errcode('message is null');
		}

		

		/** 获取审批信息 */
		$serv_af = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$this->_askfor = $serv_af->fetch_by_id($af_id);
		
		if (empty($af_id) || empty($this->_askfor)) {
			return $this->_set_errcode('askfor_not_exist');
		}

		/** 判断是否有权限 */
		if (!$this->_is_permit()) {
			return $this->_set_errcode('no_privilege');
		}

		/** 评论信息入库 */
		$serv_afc = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
		$afc_id = $serv_afc->insert(array(
			'af_id' => $af_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'afc_message' => $message
		), true);
		$this->_result = array(
			'afc_id' => $afc_id
		);
	}

	/** 判断是否有权限 */
	protected function _is_permit() {
		/** 如果是发起者, 则 */
		if ($this->_askfor['m_uid'] == startup_env::get('wbs_uid')) {
			return true;
		}

		/** 读取审批想过用户信息 */
		$serv = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv->fetch_by_af_id($this->_askfor['af_id']);
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

