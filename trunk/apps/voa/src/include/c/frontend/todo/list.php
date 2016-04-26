<?php
/**
 * 待办列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_todo_list extends voa_c_frontend_todo_base {

	public function execute() {
		/**
		 * 待办列表
		 * @param
		 *   $_GET['ac']
		 *     -> more: 获取更多
		 *     -> list: 列表页
		 *     -> star: 设置顶
		 *     -> complete: 设完成
		 *     -> delete: 删除
		 */
		$acs = array(
			'more',
			'list',
			'complete',
			'delete',
			'star'
		);
		$ac = $this->request->get('ac');
		$ac = in_array($ac, $acs) ? $ac : 'list';

		/** 执行对于操作 */
		$func = '_' . $ac;
		if (method_exists($this, $func)) {
			call_user_func(array(
				$this,
				$func
			));
			return true;
		}

		/** 按完成的状态获取我的待办 */
		$data = new stdClass;

		$data->incomplete = $this->main->fetch_by_conditions_and_order(array(
			'td_completed' => false,
			'm_uid' => startup_env::get('wbs_uid')
		), 0, $this->settings['perpage'], array(
			'td_stared' => 'desc',
			'td_created' => 'desc'
		));

		// 此处默认设置已完成列表中的数量为3条
		$data->complete = $this->main->fetch_by_conditions_and_order(array(
			'td_completed' => true,
			'm_uid' => startup_env::get('wbs_uid')
		), 0, 3, array(
			'td_stared' => 'desc',
			'td_created' => 'desc'
		));

		/** 整理输出 */
		foreach ($data->incomplete as &$value) {
			$this->format->in_list($value);
		}

		unset($value);

		foreach ($data->complete as &$value) {
			$this->format->in_list($value);
		}

		unset($value);

		$this->view->set('list', $data);
		$this->view->set('form_action', '/todo/new?handlekey=post');
		$this->view->set('navtitle', '待办事项');

		$this->_output('todo/' . $ac);
	}

	/**
	 * 获取更多
	 * @return json 某时间点之后的几条数据
	 */
	protected function _more() {
		/** 取几条 */
		$limit = $this->request->get('limit') ? (int) $this->request->get('limit') : $this->settings['perpage'];

		/** 当前时间 */
		$time_spot = intval($this->request->get('datetime'));
		if (empty($time_spot)) {
			$this->_error_message("datetime_is_empty");
		}

		/** 取数据 */
		$todos = $this->main->fetch_by_conditions_and_order(array(
			'td_created' => array($time_spot, '<'),
			'td_completed' => true,
			'm_uid' => startup_env::get('wbs_uid')
		), 0, $limit, array('td_created' => 'desc'));

		$ret = array(
			'items' => array()
		);

		// 组成Json交给JS处理
		foreach ($todos as $key => $todo) {
			$this->format->in_list($todo);

			$temp = array();
			$temp['data-id'] = $todo['td_id'];
			$temp['subject'] = $todo['_subject'];
			$temp['created'] = $todo['td_created'];
			(int) $todo['td_stared'] > 0 && $temp['stared'] = 'top';
			(int) $todo['td_exptime'] > 0 && $temp['expTime'] = $todo['_exptime'];
			(int) $todo['td_calltime'] > 0 && $temp['clock'] = 1;

			array_push($ret['items'], $temp);
		}

		$this->_json_message($ret);
	}

	/** 设为完成 */
	protected function _complete() {
		$id = $this->request->get('id');
		$checked = ($this->request->get('checked') === 'true') ? true : false;

		/** 读取待办事项信息 */
		$todo = $this->main->fetch_by_id($id);
		if (empty($id) || empty($todo)) {
			$this->_error_message('todo_is_not_exists');
		}

		/** 判断权限 */
		if (startup_env::get('wbs_uid') != $todo['m_uid']) {
			$this->_error_message('该待办事项不存在或已被删除');
			return false;
		}

		$rows = $this->main->update(array(
			'td_completed' => $checked
		), array(
			'td_id' => $id
		));

		$ret = ($rows > 0) ? array('response' => 'success') : array('response' => 'fail');

		$this->_json_message($ret);
	}

	/** 设为星标 */
	protected function _star() {
		$id = $this->request->get('id');
		$set = $this->request->get('set');

		/** 读取待办事项信息 */
		$todo = $this->main->fetch_by_id($id);
		if (empty($id) || empty($todo)) {
			$this->_error_message('todo_is_not_exists');
		}

		/** 判断权限 */
		if (startup_env::get('wbs_uid') != $todo['m_uid']) {
			$this->_error_message('该待办事项不存在或已被删除');
			return false;
		}

		$rows = $this->main->update(array(
			'td_stared' => $set
		), array(
			'td_id' => $id
		));

		$ret = ($rows > 0) ? array('response' => 'success') : array('response' => 'fail');

		$this->_json_message($ret);
	}

	/**
	 * 删除待办
	 * @return json 成功/失败
	 */
	protected function _delete() {
		$id = $this->request->get('id');

		/** 读取待办事项信息 */
		$todo = $this->main->fetch_by_id($id);
		if (empty($id) || empty($todo)) {
			$this->_error_message('todo_is_not_exists');
		}

		/** 判断权限 */
		if (startup_env::get('wbs_uid') != $todo['m_uid']) {
			$this->_error_message('该待办事项不存在或已被删除');
			return false;
		}

		$rows = $this->main->delete_by_ids(array(
			'td_id' => $id
		));

		$ret = ($rows > 0) ? array('response' => 'success') : array('response' => 'fail');

		$this->_json_message($ret);
	}
}
