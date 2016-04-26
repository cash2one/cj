<?php
/**
 * 对会议纪要内容的评论
 * $Author$
 * $Id$
 */

class voa_c_frontend_minutes_reply extends voa_c_frontend_minutes_base {

	public function execute() {
		/** 如果不是 post 提交 */
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		/** 审批 id */
		$mi_id = intval($this->request->get('mi_id'));
		$message = trim($this->request->get('message'));
		if (0 >= strlen($message)) {
			$this->_error_message('message_too_short');
		}

		/** 获取审批信息 */
		$serv = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$minutes = $serv->fetch_by_id($mi_id);
		if (empty($mi_id) || empty($minutes)) {
			$this->_error_message('minutes_is_not_exists', get_referer());
		}

		/** 读取权限用户 */
		$serv_m = &service::factory('voa_s_oa_minutes_mem', array('pluginid' => startup_env::get('pluginid')));
		$mem = $serv_m->fetch_by_mi_id_uid($mi_id, $this->_user['m_uid']);

		if (empty($mem)) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 评论信息入库 */
		$serv_p = &service::factory('voa_s_oa_minutes_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_p->insert(array(
				'mi_id' => $mi_id,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'mip_message' => $message
		));

		/** 提示操作成功 */
		$this->_success_message('评论操作成功', "/minutes/view/{$mi_id}");
	}
}
