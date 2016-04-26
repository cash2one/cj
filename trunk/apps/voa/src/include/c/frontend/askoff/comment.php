<?php
/**
 * 对审批内容的评论
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_comment extends voa_c_frontend_askoff_base {
	protected $_askoff = array();

	public function execute() {

		return true;
		/** 如果不是 post 提交 */
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		/** 审批 id */
		$af_id = intval($this->request->get('af_id'));
		$message = trim($this->request->get('message'));
		if (0 >= strlen($message)) {
			$this->_error_message('message_too_short');
		}

		/** 获取审批信息 */
		$serv_af = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$this->_askoff = $serv_af->fetch_by_id($af_id);
		if (empty($af_id) || empty($this->_askoff)) {
			$this->_error_message('askoff_not_exist', get_referer());
		}

		/** 判断是否有权限 */
		if (!$this->_is_permit()) {
			$this->_error_message('no_privilege');
		}

		/** 评论信息入库 */
		$serv_afc = &service::factory('voa_s_oa_askoff_comment', array('pluginid' => startup_env::get('pluginid')));
		$serv_afc->insert(array(
			'af_id' => $af_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'afc_message' => $message
		));

		/** 提示操作成功 */
		$this->_success_message('评论操作成功', "/askoff/view/{$af_id}");
	}

	/** 判断是否有权限 */
	protected function _is_permit() {
		/** 如果是发起者, 则 */
		if ($this->_askoff['m_uid'] == startup_env::get('wbs_uid')) {
			return true;
		}

		/** 读取审批想过用户信息 */
		$serv = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv->fetch_by_af_id($this->_askoff['af_id']);
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

