<?php
/**
 * 新增资料库
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_new extends voa_c_frontend_datum_base {
	/** 主题 */
	protected $_subject;
	/** 内容 */
	protected $_message;

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

			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('datum', array());
		$this->view->set('navtitle', '新资料库');

		$this->_output('datum/post');
	}

	public function _add() {
		/** 数据入库 */
		$serv = &service::factory('voa_s_oa_datum', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 资料库信息入库 */
			$datum = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'dt_subject' => $this->_subject,
				'dt_message' => $this->_message,
				'dt_datumts' => $this->_datumts,
				'dt_status' => voa_d_oa_datum::STATUS_NORMAL
			);
			$dt_id = $serv->insert($datum, true);
			$datum['dt_id'] = $dt_id;

			if (empty($dt_id)) {
				throw new Exception('datum_new_failed');
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->_error_message('datum_new_failed', get_referer());
		}

		/** 给目标人发送微信模板消息 */

		$this->_success_message('发布资料库成功', "/datum/view/{$dt_id}");
	}
}
