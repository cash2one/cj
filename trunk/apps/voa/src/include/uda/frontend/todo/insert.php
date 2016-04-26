<?php
/**
 * 待办事项相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_todo_insert extends voa_uda_frontend_todo_base {

	/**
	 * 对待办事项的回复
	 * @param array $post
	 * @return boolean
	 */
	public function todo_reply(&$post) {
		/** 内容 */
		$message = (string) $this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		/** 待办 id */
		$td_id = intval($this->_request->get('td_id'));

		/** 获取待办事项信息 */
		$serv =& service::factory('voa_s_oa_todo', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$todo = $serv->fetch_by_id($td_id);
		if (empty($td_id) || empty($todo)) {
			$this->errmsg(100, 'todo_is_not_exists');
			return false;
		}

		/** 获取待办事项用户 */
		$serv_m =& service::factory('voa_s_oa_todo_mem', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$mem = $serv_m->fetch_by_conditions(array(
			'td_id' => $td_id,
			'm_uid' => startup_env::get('wbs_uid')
		));
		if (empty($mem)) {
			$this->errmsg(101, 'no_privilege');
			return false;
		}

		/** 评论信息入库 */
		$serv_pt =& service::factory('voa_s_oa_todo_post', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$post = array(
			'td_id' => $td_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'tdp_message' => $message
		);
		$serv_pt->insert($post);

		return true;
	}

	/**
	 * 新待办事项入库
	 * @throws Exception
	 * @return boolean
	 */
	public function todo_new(&$todo) {
		$subject = (string) $this->_request->get('subject');
		if (!$this->val_subject($subject)) {
			return false;
		}

		$calltime = (string) $this->_request->get('calltime');
		if ( ! $this->val_calltime($calltime)) {
			return false;
		}

		$exptime = (string) $this->_request->get('exptime');
		if ( ! $this->val_exptime($exptime)) {
			return false;
		}

		$completed = (int) $this->_request->get('completed');
		if ( ! $this->val_completed($completed)) {
			return false;
		}

		$stared = (int) $this->_request->get('stared');
		if ( ! $this->val_stared($stared)) {
			return false;
		}

		$transcation =& service::factory('voa_s_oa_todo', array());

		/** 数据入库 */
		try {
			$transcation->begin();

			/** 待办事项标题信息入库 */
			$todo = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'td_subject' => $subject,
				'td_calltime' => $calltime,
				'td_exptime' => $exptime,
				'td_stared' => $stared,
				'td_completed' => $completed,
				'td_status' => voa_d_oa_todo::STATUS_NORMAL
			);
			$td_id = $transcation->insert($todo, true);
			$todo['td_id'] = $td_id;

			if (empty($td_id)) {
				throw new Exception('todo_new_failed');
			}

			$transcation->commit();
		} catch (Exception $e) {
			$transcation->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'todo_new_failed');
			return false;
		}

		return true;
	}
}
