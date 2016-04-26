<?php
/**
 * voa_c_admincp_office_reimburse_list
 * 企业后台/微办公管理/报销/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_reimburse_list extends voa_c_admincp_office_reimburse_base {

	public function execute() {

		$perpage = 15;

		// 请求的是导出操作
		if ($this->request->get('is_dump')) {
			$perpage = 10000;
		}

		list($total, $multi, $search_by, $list) = $this->_reimburse_search($perpage);

		// 请求的是导出操作
		if ($this->request->get('is_dump')) {
			$this->__dump_list($list);
			return true;
		}

		$this->view->set('search_by', $search_by);
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('thread_delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('rb_id' => '')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('rb_id' => '')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('rb_id' => '')));

		$this->view->set('reimburse_type_list', $this->_sets['types']);
		$this->view->set('issearch', $this->request->get('issearch'));

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);

		$this->output('office/reimburse/reimburse_list');
	}

	/**
	 * 导出CSV文件
	 * @param array $list
	 */
	private function __dump_list($list) {

		// 待输出的数据，数组格式
		$data = array();
		// 标题栏 - 字段名称
		$data[] = array(
			'username' => '申请人',
			'expend' => '金额',
			//'type' => '类型',
			'subject' => '事由',
			'time' => '申请时间',
			'status' => '状态',
			'users' => '审批人',
		);
		// 提取审批人
		$rb_ids = array();
		$rb_uids = array();
		foreach ($list as $_row) {
			$rb_ids[] = $_row['rb_id'];
			$rb_uids[$_row['rb_id']] = $_row['m_uid'];
		}
		unset($_row);

		// 提取审批人
		$user_list = array();
		$serv_proc = &service::factory('voa_s_oa_reimburse_proc');
		foreach ($serv_proc->fetch_by_conditions(array('rb_id' => array($rb_ids, 'in'))) as $_row) {
			if ($rb_uids[$_row['rb_id']] == $_row['m_uid']) {
				continue;
			}
			$user_list[$_row['rb_id']][] = $_row['m_username'];
		}

		// 遍历数据每行一条
		foreach ($list as $_row) {
			$data[] = array(
				'username' => $_row['_realname'],
				'expend' => $_row['_expend'],
				//'type' => $_row['_type'],
				'subject' => $_row['rb_subject'],
				'time' => $_row['_time'],
				'status' => $_row['_status'],
				'users' => isset($user_list[$_row['rb_id']]) ? implode(', ', $user_list[$_row['rb_id']]) : ''
			);
		}

		// 转换为csv字符串
		$csv_data = array2csv($data);

		$filename = 'reimburse_'.rgmdate(startup_env::get('timestamp'), 'YmdHis').'.csv';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: text/csv");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Coentent_Length: '.strlen($csv_data));
		echo $csv_data;

		exit;
	}

}
