<?php
/**
 * 对评论内容的回复
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_reply extends voa_c_frontend_askfor_base {

	public function execute() {
		/** 如果非 post */
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
		}

		/** 审批 id */
		$afc_id = intval($this->request->get('afc_id'));
		$message = trim($this->request->get('message'));
		if (0 >= strlen($message)) {
			$this->_error_message('message_too_short');
		}

		/** 获取评论信息 */
		$servcmt = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
		$cmt = $servcmt->fetch_by_id($afc_id);
		if (empty($afc_id) || empty($cmt)) {
			$this->_error_message('askfor_comment_not_exist', get_referer());
		}

		/** 判断是否有权限 */
		if (!$this->_is_permit($cmt)) {
			$this->_error_message('no_privilege');
		}

		/** 回复信息入库 */
		$servrpy = &service::factory('voa_s_oa_askfor_reply', array('pluginid' => startup_env::get('pluginid')));
		$servrpy->insert(array(
			'afc_id' => $afc_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'afr_message' => $message
		));

		/** 提示操作成功 */
		$this->_success_message('回复操作成功', "/askfor/view/{$cmt['af_id']}");
	}

	/** 判断是否有权限 */
	protected function _is_permit($cmt) {
		/** 如果是发起者, 则 */
		if ($cmt['m_uid'] == $this->_user['m_uid']) {
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

