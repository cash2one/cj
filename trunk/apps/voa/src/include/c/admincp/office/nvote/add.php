<?php

/**
 * voa_c_admincp_office_nvote_add
 * 投票调研-添加投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:45
 */
class voa_c_admincp_office_nvote_add extends voa_c_admincp_office_nvote_base {

	public function execute() {

		//如果为post请求执行添加
		if ($this->request->get_method() === 'POST') {
			$this->__add();
		} else {
			$this->output('office/nvote/add');
		}
	}

	private function __add() {

		$data = $this->request->postx();

		$nvote = array(
			'subject' => '',
			'is_single' => '',
			'is_show_name' => '',
			'is_show_result' => '1',
			'is_repeat' => '2',
			'end_time' => '',
			'at_id' => '0',
		);
		$this->_init_params($data['nvote'], $nvote);

		//拼接结束日期和时间
		$nvote['end_time'] = $data['nvote']['end_date'] . ' ' . $data['nvote']['end_time'];
		date_default_timezone_set('Asia/Shanghai');
		if (strtotime($nvote['end_time']) < time()) {
			$this->message('error', '截至时间不能小于当前时间');
		}
		//用户id
		$m_uids = array();
		if (!empty($data['m_uids'])) {
			$m_uids = $data['m_uids'];
		}

		//投票选项
		$options = array();
		if (!empty($data['options'])) {
			$options = $data['options'];
		}

		//投票参与部门
		$cd_ids = array();
		if (!empty($data['cd_ids'])) {
			$cd_ids = $data['cd_ids'];
		}

		//取当前登陆用户，活动发起人
		$nvote['submit_ca_id'] = $this->_user['ca_id'];
		$nvote['submit_id'] = 0;
		$uda = &uda::factory('voa_uda_frontend_nvote_add');
		try {
			if (!$uda->add($nvote, $m_uids, $cd_ids, $options, $this->session)) {
				$this->message('error', $uda->error);
			}

		} catch (help_exception $h) {
			$this->_admincp_error_message($h);

		} catch (Exception $e) {
			$this->_error_message('系统发生内部错误，错误编码:-9999');
			logger::error(print_r($e, true));
			//$this->_admincp_system_message($e);
		}

		$this->_admincp_success_message('投票调研添加成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
	}
}
