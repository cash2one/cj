<?php
/**
 * 新建日程
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_plan_new extends voa_c_frontend_plan_base {
	// 插入对象
	protected $insert;

	public function execute() {
		if ($this->_is_post()) {
			$this->_add();
		}

		$range_begin = $range_finish = rgmdate(startup_env::get('timestamp') - (30*24*3600), 'Y-m-d');
		$selected_begin = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		$selected_finish = rgmdate(startup_env::get('timestamp') + 1, 'Y-m-d');

		$this->view->set('types', $this->settings['types']);
		$this->view->set('ac', $this->action_name);
		$this->view->set('range_begin', $range_begin);
		$this->view->set('selected_begin', $selected_begin);
		$this->view->set('selected_finish', $selected_finish);
		$this->view->set('form_action', "/plan/".$this->action_name."?handlekey=post");
		$this->view->set('navtitle', '新日程');

		$this->_output('plan/post');
	}

	protected function _add() {
		$plan = array();

		$insert =& uda::factory('voa_uda_frontend_plan_insert');

		! $insert->plan_new($plan)
		? $this->_error_message($insert->error)
		: $this->_success_message('发布日程成功', "/plan/edit/{$plan['pl_id']}");

		return false;
	}
}
