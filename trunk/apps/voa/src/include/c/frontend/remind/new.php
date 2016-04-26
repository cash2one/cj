<?php
/**
 * 新增定时提醒
 * $Author$
 * $Id$
 */

class voa_c_frontend_remind_new extends voa_c_frontend_remind_base {
	/** 主题 */
	protected $_subject;
	/** 内容 */
	protected $_message;
	/** 提醒时间 */
	protected $_remindts;

	public function execute() {
		if ($this->_is_post()) {
			/** 标题 */
			$this->_subject = trim($this->request->get('subject'));
			if (empty($this->_subject)) {
				$this->_error_message('subject_short', get_referer());
			}

			/** 审批内容 */
			$this->_message = trim($this->request->get('message'));
			if (empty($this->_message)) {
				$this->_error_message('message_short', get_referer());
			}

			/** 提醒时间 */
			$this->_remindts = intval($this->request->get('remindts'));
			if ($this->_remindts < startup_env::get('timestamp')) {
				$this->_error_message('remindts_error');
			}

			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('remind', array());
		$this->view->set('navtitle', '新定时提醒');

		$this->_output('remind/post');
	}

	public function _add() {
		/** 数据入库 */
		$serv = &service::factory('voa_s_oa_remind', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 定时提醒信息入库 */
			$remind = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'rm_subject' => $this->_subject,
				'rm_message' => $this->_message,
				'rm_remindts' => $this->_remindts,
				'rm_status' => voa_d_oa_remind::STATUS_NORMAL
			);
			$rm_id = $serv->insert($remind, true);
			$remind['rm_id'] = $rm_id;

			if (empty($rm_id)) {
				throw new Exception('remind_new_failed');
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->_error_message('remind_new_failed', get_referer());
		}

		/** 给目标人发送微信模板消息 */

		$this->_success_message('发布定时提醒成功', "/remind/view/{$rm_id}");
	}
}
