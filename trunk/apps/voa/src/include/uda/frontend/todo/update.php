<?php
/**
 * 待办事项相关的更新操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_todo_update extends voa_uda_frontend_todo_base {
	/**
	 * 更新操作
	 * @param array $todo_id 待办事项的主键
	 * @return boolean
	 */
	public function todo_update($todo_id) {
		$subject = (string) $this->_request->get('subject');
		if (!$this->val_subject($subject)) {
			return false;
		}

		$calltime = (string) $this->_request->get('calltime');
		if (!$this->val_calltime($calltime)) {
			return false;
		}

		$exptime = (string) $this->_request->get('exptime');
		if (!$this->val_exptime($exptime)) {
			return false;
		}

		$stared = (int) $this->_request->get('stared');
		if (!$this->val_stared($stared)) {
			return false;
		}

		$serv =& service::factory('voa_s_oa_todo', array(
			'pluginid' => startup_env::get('pluginid')
		));

		try {
			$serv->begin();

			$serv->update(array(
				'td_subject' => $subject,
				'td_calltime' => $calltime,
				'td_exptime' => $exptime,
				'td_stared' => $stared
			), array('td_id' => $todo_id));

			$serv->commit();
		}
		catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}
}
