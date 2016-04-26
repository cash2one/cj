<?php
/**
 * 新的销售轨迹
 * $Author$
 * $Id$
 */

class voa_c_frontend_footprint_new extends voa_c_frontend_footprint_base {

	public function execute() {
		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', '/footprint/new?handlekey=post');
		$this->view->set('footprint', array());
		$this->view->set('types', $this->_p_sets['types']['type']);
		$this->view->set('default_index', $default_index);
		$this->view->set('navtitle', '新销售轨迹');

		$this->_output('footprint/post');
	}

	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_footprint_insert');
		/** 轨迹信息 */
		$footprint = array();
		/** 抄送人信息 */
		$cculist = array();
		if (!$uda->footprint_new($footprint, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 给目标人发送微信消息 */

		$this->_success_message('发布销售轨迹成功', "/footprint/mine?btime=".rgmdate($footprint['fp_visittime'], 'Y-m-d'));
	}
}
