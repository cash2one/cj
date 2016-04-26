<?php

/**
 * 请假申请列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_activity_list extends voa_c_frontend_activity_base {

	public function execute() {
		//获取参数
		$ac = $this->request->get('action');
		$ac = (!empty($ac)) ? $ac : 'all';
		$uda_get = &uda::factory('voa_uda_frontend_activity_get');
		$this->view->set('ac', $ac);
		$this->view->set('op', $uda_get->getoption());

		// 引入应用模板
		if ($ac == 'mine' || $ac == 'join') {
			$this->view->set('navtitle', '我的活动');
			$this->_output('mobile/' . $this->_plugin_identifier . '/mine');
		} else {
			$this->view->set('navtitle', '活动列表');
			$this->_output('mobile/' . $this->_plugin_identifier . '/list');
		}
		return true;
	}
}
