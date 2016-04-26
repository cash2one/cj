<?php

/**
 * 活动审核
 *voa_c_frontend_activity_return
 * $Author$
 * $Id$
 */
class voa_c_frontend_activity_return extends voa_c_frontend_activity_base {

	public function execute() {
		//获取参数
		$apid = $this->request->get('apid');
		$acid = $this->request->get('acid');
		if (empty($apid)) {
			$this->_error_message("地址错误");
		}
		//获取数据
		$uda_sign = &uda::factory('voa_uda_frontend_activity_sign');
		$data = array();
		$request['ac'] = 'view';
		$request['apid'] = $apid;
		$request['acid'] = $acid;
		$url = '/frontend/activity/view/?acid=' . $acid . '&pluginid=' . startup_env::get('pluginid');
		$uda_sign->doit($request, $data);
		if (empty($data['m_uid'])) {
			return $this->_error_message("已同意过！");
		}
		$this->view->set('data', $data);
		$this->view->set('url', $url);


		// 引入应用模板
		$this->view->set('navtitle', '审批取消报名');
		$this->_output('mobile/' . $this->_plugin_identifier . '/return');

		return true;
	}

}
