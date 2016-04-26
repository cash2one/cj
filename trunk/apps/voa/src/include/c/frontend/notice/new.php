<?php
/**
 * 新增公告
 * $Author$
 * $Id$
 */

class voa_c_frontend_notice_new extends voa_c_frontend_notice_base {
	protected $_subject;
	protected $_message;

	public function execute() {
		if (1) {
			$this->_error_message('no_privilege');
			return true;
		}

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

			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('notice', array());
		$this->view->set('navtitle', '新公告');

		$this->_output('notice/post');
	}

	public function _add() {
		/** 数据入库 */
		$serv = &service::factory('voa_s_oa_notice', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();
			/** 公告信息入库 */
			$notice = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'nt_subject' => $this->_subject,
				'nt_message' => $this->_message,
				'nt_status' => voa_d_oa_notice::STATUS_NORMAL
			);
			$nt_id = $serv->insert($notice, true);
			$notice['nt_id'] = $nt_id;

			if (empty($nt_id)) {
				throw new Exception('notice_new_failed');
			}

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->_error_message('notice_new_failed', get_referer());
		}

		/** 给目标人发送微信模板消息 */

		$this->_success_message('发布公告成功', "/notice/view/{$nt_id}");
	}
}
