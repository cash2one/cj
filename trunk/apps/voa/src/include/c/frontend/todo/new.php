<?php
/**
 * 新的待办事项
 * $Author: wangxiangmin $
 * $Id$
 */

class voa_c_frontend_todo_new extends voa_c_frontend_todo_base {

	public function execute() {
		if ($this->_is_post()) {
			$subject = trim($this->request->get('subject'));
			$exptime = trim($this->request->get('exptime'));
			$calltime = trim($this->request->get('calltime'));
			$completed = trim($this->request->get('completed'));
			$stared = trim($this->request->get('stared'));

			if (empty($subject)) {
				$this->_error_message('对不起，缺少必填项');
			}

			/** 数据入库 */
			try {
				$this->main->begin();

				/** 申请信息入库 */
				$todo = array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'td_subject' => $subject,
					"td_exptime" => $exptime,
					"td_calltime" => $calltime,
					"td_completed" => ($completed) ? true : false,
					"td_stared" => ($stared) ? true : false,
					'td_status' => voa_d_oa_todo::STATUS_NORMAL
				);
				$td_id = $this->main->insert($todo, true);
				if (empty($td_id)) {
					throw new Exception('待办事项新增失败');
				}

				$this->main->commit();
			} catch (Exception $e) {
				$this->main->rollback();
				/** 如果 $id 值为空, 则说明入库操作失败 */
				$this->_error_message('待办事项新增失败');
			}

			$this->_success_message('待办事项发布成功', "/todo");
		}

		$this->view->set('form_action', "/todo/new?handlekey=post");
		$this->view->set('ac', $this->action_name);
		$this->view->set('refer', get_referer());
		$this->view->set('todo', array());
		$this->view->set('navtitle', '待办事项');

		$this->_output('todo/post');
	}
}
