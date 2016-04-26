<?php
/**
 * 查看审批申请
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_edit extends voa_c_frontend_askfor_base {

	public function execute() {
		/** 判断当前审批是否存在 */
		$servaf = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$af_id = rintval($this->request->get('af_id'));
		$askfor = $servaf->fetch_by_id($af_id);
		if (empty($askfor)) {
			$this->_error_message('askfor_not_exist', get_referer());
		}

		if ($askfor['m_uid'] != $this->_user['m_uid']) {
			$this->_error_message('no_privilege');
		}

		/** 如果不是提交操作 */
		if ($this->_is_post()) {
			/** 标题 */
			$subject = trim($this->request->get('subject'));
			if (empty($subject)) {
				$this->_error_message('subject_too_short', get_referer());
			}

			/** 内容 */
			$message = trim($this->request->get('message'));
			if (empty($message)) {
				$this->_error_message('message_too_short', get_referer());
			}

			/** 保存信息 */
			$servaf->update(array(
				'af_subject' => $subject,
				'af_message' => $message,
				'af_status' => $status
			), array('af_id' => $askfor['af_id']));

			/** 当前审批人信息入库 */
			$approveuid = trim($this->request->get('approveuid'));
			$mem = $this->_user;
			if (empty($approveuid) || empty($mem)) {
				$this->_error_message('approveuser_is_empty', get_referer());
			}

			/** 不能审批自己的申请信息 */
			if ($mem['m_uid'] == startup_env::get('wbs_uid')) {
				$this->_error_message('askfor_verify_self', get_referer());
			}

			/** 审批人信息入库 */
			$servp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
			$servp->insert(array(
				'af_id' => $askfor['af_id'],
				'm_uid' => $mem['m_uid'],
				'm_username' => $mem['m_username'],
				'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
			));

			$this->_success_message('更新成功', '/meeting/view/'.$askfor['af_id']);
			return false;
		}

		$this->_output('askfor/post');
	}
}
