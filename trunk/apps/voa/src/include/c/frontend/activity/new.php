<?php

/**
 * 活动报名-发起活动和修改活动
 * $Author$
 * $Id$
 */
class voa_c_frontend_activity_new extends voa_c_frontend_activity_base {

	public function execute() {
		//获取参数
		$ac = $this->request->get('ac');
		$acid = rintval($this->request->get('acid'));
		$this->view->set('ac', $ac);
		$this->view->set('acid', $acid);

		if (!empty($acid)) {
			//编辑页面
			$uda_view = &uda::factory('voa_uda_frontend_activity_view');
			$data = array();
			$uda_view->doit($acid, $data);
			//数据处理
			$view = array();
			$uda_view->format($data, $view);
			$view['content'] = htmlspecialchars_decode($view['content']);
		} else {
			//新增页面,时间操作
			$view['start_time'] = startup_env::get('timestamp') + 86400;
			$view['end_time'] = startup_env::get('timestamp') + 86400 * 2;
			$view['cut_off_time'] = startup_env::get('timestamp') + 86400;
		}
		$this->view->set('data', $view);
		$this->view->set('navtitle', '活动报名');

		// 引入应用模板
		$this->_output('mobile/' . $this->_plugin_identifier . '/new');

		return true;

	}

}
