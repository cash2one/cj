<?php
/**
 * 对秘密内容的评论
 * $Author$
 * $Id$
 */

class voa_c_frontend_secret_reply extends voa_c_frontend_secret_base {

	public function execute() {
		/** 如果不是 post 提交 */
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		/** 审批 id */
		$st_id = intval($this->request->get('st_id'));
		$message = trim($this->request->get('message'));
		if (0 >= strlen($message)) {
			$this->_error_message('message_too_short');
		}

		/** 获取审批信息 */
		$serv = &service::factory('voa_s_oa_secret', array('pluginid' => startup_env::get('pluginid')));
		$secret = $serv->fetch_by_id($st_id);
		if (empty($st_id) || empty($secret)) {
			$this->_error_message('secret_not_exist', get_referer());
		}

		/** 评论信息入库 */
		$serv_p = &service::factory('voa_s_oa_secret_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_p->insert(array(
			'st_id' => $st_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'stp_message' => $message
		));

		/** 提示操作成功 */
		$this->_success_message('评论操作成功', "/secret/view/{$st_id}");
	}
}
