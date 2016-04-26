<?php

/**
 * 置顶(排序)操作
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_displayorder extends voa_c_frontend_thread_base
{

	protected $_t_id;

	public function execute()
	{
		/**
		 * 排序相关操作
		 * up: 置顶
		 * cancel: 取消置顶
		 */
		$acs = array(
			'up',
			'cancel'
		);

		$ac = trim($this->request->get('ac'));
		$func = '_' . $ac;
		if (empty($ac) || ! in_array($ac, $acs) || ! method_exists($this, $func)) {
			$this->_error_message('undefined_action');
		}

		// 读取主题信息
		$this->_t_id = intval($this->request->get('t_id'));
		$serv = &service::factory('voa_s_oa_thread', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$thread = $serv->fetch_by_id($this->_t_id);
		if (empty($thread)) {
			$this->_error_message('thread_is_not_exist');
		}

		// 判断是否有权限
		if ($thread['m_uid'] != startup_env::get('wbs_uid')) {
			$this->_error_message('no_privilege');
		}

		call_user_func(array(
			$this,
			$func
		));
	}

	// 置顶操作
	protected function _up()
	{
		// 读取最大的排序号
		$serv = &service::factory('voa_s_oa_thread', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$first_t = $serv->fetch_mine_by_uid(startup_env::get('wbs_uid'), 0, 1);
		// 如果最前面的主题不是当前主题, 则更新排序号
		if ($first_t['t_id'] != $this->_t_id) {
			// 即使排序 id
			$orderid = 0;
			if (! empty($first_t)) {
				$orderid = $first_t['t_displayorder'] + 1;
			}

			// 更新排序值
			$serv->update(array(
				't_displayorder' => $orderid
			), array(
				't_id' => $this->_t_id
			));
		}

		// 返回
		$this->_json_message(array(
			'orderid' => $orderid
		));
	}

	// 取消置顶
	protected function _cancel()
	{
		$serv = &service::factory('voa_s_oa_thread', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$serv->update(array(
			't_displayorder' => 0
		), array(
			't_id' => $this->_t_id
		));
		$this->_json_message('');
	}
}
