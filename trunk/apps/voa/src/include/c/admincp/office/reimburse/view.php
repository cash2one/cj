<?php
/**
 * voa_c_admincp_office_reimburse_view
 * 企业后台/微办公管理/报销/查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_reimburse_view extends voa_c_admincp_office_reimburse_base {

	public function execute() {

		$rb_id = $this->request->get('rb_id');
		$rb_id = rintval($rb_id, false);

		// 设置页URL
		$this->view->set('plugin_setting_url', $this->cpurl($this->_module, $this->_operation, 'setting', $this->_module_plugin_id));

		// @ 当前查看的报销基本信息
		$serv = &service::factory('voa_s_oa_reimburse', array('pluginid' => $this->_module_plugin_id));
		$reimburse = $serv->fetch_by_id($rb_id);
		if (empty($reimburse)) {
			$this->message('error', '指定 '.$this->_module_plugin['cp_name'].' 数据不存在');
		}

		// 格式化报销信息
		$uda_reimburse_format = &uda::factory('voa_uda_frontend_reimburse_format');
		$uda_reimburse_format->reimburse($reimburse);

		// @ 读取清单信息
		$bill_list = $this->_service_single('reimburse_bill_submit', $this->_module_plugin_id, 'fetch_by_rb_id', $rb_id);
		$uda_reimburse_format->reimburse_bill_list($bill_list);

		// 总金额
		$money_total = 0;
		foreach ($bill_list as $_bill) {
			$money_total = $money_total + $_bill['rbb_expend'];
		}
		$money_total = round($money_total / 100, 2);

		// @ 获取回复信息
		$post_list = $this->_service_single('reimburse_post', $this->_module_plugin_id, 'fetch_by_rb_id', $rb_id);
		// 格式化回复信息数据
		$uda_reimburse_format->reimburse_post_list($post_list);

		// @ 获取报销进度信息
		$proc_list = $this->_service_single('reimburse_proc', $this->_module_plugin_id, 'fetch_by_rb_id', $rb_id);
		// 格式化报销进度信息
		$uda_reimburse_format->reimburse_proc_list($proc_list);
		// 审批人列表
		$proc_user_list = array();
		foreach ($proc_list as $_proc) {
			if ($_proc['m_uid'] == $reimburse['m_uid']) {
				continue;
			}
			$proc_user_list[] = $_proc;
		}
		unset($_proc);

		$this->view->set('rb_id', $rb_id);
		$this->view->set('reimburse', $reimburse);
		$this->view->set('bill_list', $bill_list);
		$this->view->set('bill_count', count($bill_list));
		$this->view->set('post_list', $post_list);
		$this->view->set('post_count', count($post_list));
		$this->view->set('proc_list', $proc_list);
		$this->view->set('proc_user_list', $proc_user_list);
		$this->view->set('proc_count', count($proc_list) - 1);
		$this->view->set('money_total', $money_total);

		$this->output('office/reimburse/reimburse_view');
	}

}
