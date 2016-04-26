<?php
/**
 * 编辑待办事项信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_todo_edit extends voa_c_frontend_todo_base {

	public function execute() {
		/** 获取待办事项信息 */
		$td_id = intval($this->request->get('td_id'));
		$todo = $this->main->fetch_by_id($td_id);

		if (empty($todo)) {
			$this->_error_message('当前待办事项记录不存在' . $td_id);
			return false;
		}

		/** 判断权限 */
		if (startup_env::get('wbs_uid') != $todo['m_uid']) {
			$this->_error_message('该待办事项不存在或已被删除');
			return false;
		}

		/** 处理编辑 */
		if ($this->_is_post()) {
			if (!$this->update->todo_update($td_id)) {
				$this->_error_message($this->update->error);
				return false;
			}

			$this->_success_message('待办事项修改成功', '/todo/list');
		}

		/** 待办事项数据格式化 */
		if (!$this->format->in_post($todo)) {
			$this->_error_message($this->format->error);
			return false;
		}

		$this->view->set('todo', $todo);
		$this->view->set('ac', $this->action_name);
		$this->view->set('form_action', "/todo/edit/{$td_id}/?handlekey=post");
		$this->view->set('navtitle', '编辑待办事项');

		$this->_output('todo/post');
	}
}
