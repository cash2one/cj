<?php
/**
 * 查看待办事项
 * $Author$
 * $Id$
 */

class voa_c_frontend_todo_view extends voa_c_frontend_todo_base {

	public function execute() {
		/** 待办事项ID */
		$td_id = rintval($this->request->get('td_id'));

		/** 读取待办事项信息 */
		$todo = $this->main->fetch_by_id($td_id);
		if (empty($td_id) || empty($todo)) {
			$this->_error_message('todo_is_not_exists');
		}

		if (!$this->format->main($todo)) {
			$this->_error_message($this->format->error);
			return false;
		}

		/** 判断当前用户是否有权限查看 */
		if (startup_env::get('wbs_uid') != $todo['m_uid']) {
			$this->_error_message('no_privilege');
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('todo', $todo);
		$this->view->set('posts', $posts);
		$this->view->set('form_action', '/todo/edit');
		$this->view->set('td_id', $td_id);
		$this->view->set('navtitle', '查看待办事项');

		$this->_output('todo/post');
	}

}
