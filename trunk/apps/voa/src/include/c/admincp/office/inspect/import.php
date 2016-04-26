<?php
/**
 * voa_c_admincp_manage_addressbook_import
 * 通讯录导入
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_inspect_import extends voa_c_admincp_office_base{

	/** 导入的Excel解析后的数据储存位置 */
	protected $_waited_data_save_path = '';

	/** 处理完的数据储存位置 */
	protected $_completed_data_save_path = '';

	protected $_waited_list = array();
	protected $_completed_list = array();

	public function execute(){

		// 获取当前站点的缓存目录
		$save_base = voa_h_func::get_sitedir();

		// 待处理的数据完整的储存路径
		$this->_waited_data_save_path = $save_base.DIRECTORY_SEPARATOR.'_addressbook_waited_'.$this->_user['ca_id'].'.json';

		// 已处理的数据完整的储存路径
		$this->_completed_data_save_path = $save_base.DIRECTORY_SEPARATOR.'_addressbook_completed_'.$this->_user['ca_id'].'.json';

		// 待处理的数据数量
		if (is_file($this->_waited_data_save_path)) {
			$this->_waited_list = @json_decode(trim(file_get_contents($this->_waited_data_save_path)), true);
		} else {
			$this->_waited_list = array();
		}

		// 已处理的数据
		if (is_file($this->_completed_data_save_path)) {
			$this->_completed_list = @json_decode(trim(file_get_contents($this->_completed_data_save_path)), true);
		}

		$action = $this->request->get('action');

		$report_list = array();
		$report_list_count = array();
		$report_col_fields = array();
		$report_col_field_names = $this->_excel_fields;

		if ($action == 'downloadtemplate') {
			// 模板文件下载

			$filename = '畅移云工作通讯录模板';
			$this->_action_downloadtemplate($filename);

		} elseif ($action == 'parse') {
			// 上传excel文件进行分析

			$cd_id = $this->request->get('cd_id');
			$cd_id = (int)$cd_id;

			$uda_department_get = &uda::factory('voa_uda_frontend_department_get');
			$department = array();
			$uda_department_get->department($cd_id, $department);
			if (empty($department['cd_id']) || $department['cd_id'] != $cd_id) {
				return $this->_ajax_message(3001, '选定的部门不存在');
			}

			// 获取到上传的文件的物理路径
			$filepath = '';
			if (!$this->_addressbook_uda_get->get_uploadfile('upload', $filepath)) {
				return $this->_ajax_message($this->_addressbook_uda_get->errno, $this->_addressbook_uda_get->error, array());
			}

			// 分析出上传的文件内容
			$result = array();
			if (!$this->_addressbook_uda_get->parse_excel_data($filepath, $result)) {
				return $this->_ajax_message($this->_addressbook_uda_get->errno, $this->_addressbook_uda_get->error, array());
			}

			// 保存分析结果
			$source_data = array();
			$source_data['cd_id'] = $cd_id;
			$source_data['total'] = count($result[1]);
			$source_data['report_cold_fields'] = $result[0];
			$source_data['row_data'] = $result[1];

			file_put_contents($this->_waited_data_save_path, rjson_encode($source_data));

			return $this->_ajax_message(0, 'ok', array('total' => count($result[1]), 'num' => 1));

		} elseif ($action == 'submitparse') {
			// 对导入结果进行编辑后的提交分析

			if (!isset($this->_waited_list['cd_id'])) {
				return $this->_ajax_message(1001, '缓存数据读取错误');
			}

			$new = $this->request->post('new');
			if (!is_array($new) || empty($new)) {
				return $this->_ajax_message(1002, '无法获取到新数据');
			}

			$ignore = $this->request->post('ignore');
			$ignore = (array)$ignore;

			// 重构新数据
			$new_result = array();
			$_ordernum = 1;
			foreach ($new as $_key => $_arr) {
				if (isset($ignore[$_key])) {
					continue;
				}
				$_new_result = array();
				foreach ($this->_waited_list['report_cold_fields'] as $_k => $_n) {
					$_new_result[$_k] = isset($_arr[$_n]) ? $_arr[$_n] : '';
				}
				$new_result[$_ordernum] = $_new_result;
				$_ordernum++;
			}

			if (empty($new_result)) {
				return $this->_ajax_message(1003, '没有需要提交的数据');
			}

			// 保存新提交的数据
			$source_data = array();
			$source_data['cd_id'] = $this->_waited_list['cd_id'];
			$source_data['total'] = count($new_result);
			$source_data['report_cold_fields'] = $this->_waited_list['report_cold_fields'];
			$source_data['row_data'] = $new_result;

			file_put_contents($this->_waited_data_save_path, rjson_encode($source_data));

			return $this->_ajax_message(0, 'ok', array('total' => count($new_result), 'num' => 1));

		} elseif ($action == 'submit') {
			// 提交导入

			$num = $this->request->get('num');
			$num = (int)$num;
			$total = $this->request->get('total');
			$total = (int)$total;
			if ($num < 1) {
				return $this->_ajax_message(1001, '数据序列非法');
			}
			if ($num > $total) {
				return $this->_ajax_message(0, 'ok', array());
			}

			$data = @file_get_contents($this->_waited_data_save_path);
			if (empty($data) || !($data = json_decode($data, true))) {
				return $this->_ajax_message(1001, '没有待导入的通讯录数据');
			}

			if (!isset($data['row_data'][$num])) {
				return $this->_ajax_message(1002, '待转换的数据读取错误');
			}

			// 已完成的数据
			if ($num > 1) {
				// 不是第一次提交
				$completed_data = $this->_completed_list;
			} else {
				// 第一次提交
				$completed_data = array();
				$completed_data['ignore'] = array();
				$completed_data['success'] = array();
				$completed_data['cold_fields'] = array();

				foreach ($this->_addressbook_uda_get->excel_fields as $key => $c) {
					if (strpos($key, '#') === false) {
						$completed_data['cold_fields'][$key] = $c['name'];
					}
				}
			}

			// 当前正在处理的数据
			$current = $data['row_data'][$num];

			// 重构数据，按字段进行对应
			$source = array();
			foreach ($data['report_cold_fields'] as $key => $field) {
				if (isset($current[$key])) {
					$source[$field] = $current[$key];
				} else {
					$source[$field] = '';
				}
			}

			$submit = $source;
			$submit['cd_id'] = $data['cd_id'];

			$is_success = false;
			if (isset($current[0]) && strpos($current[0], '#') !== false) {
				// 标记为忽略，则跳过不处理

				$source['_result_msg'] = '#已标记为忽略';

			} else {
				// 尝试提交

				$addressbook = array();
				$this->_addressbook_uda_get->addressbook(0, $addressbook);

				$updated = array();

				if ($this->_addressbook_uda_update->update($addressbook, $submit, $updated)) {
					// 添加成功
					$source['_result_msg'] = '添加成功';
					$is_success = true;
				} else {
					$source['_result_msg'] = $this->_addressbook_uda_update->error;
				}

			}

			if ($is_success) {
				$completed_data['success'][] = $source;
			} else {
				$completed_data['ignore'][] = $source;
			}

			@file_put_contents($this->_completed_data_save_path, rjson_encode($completed_data));
			if (isset($data['row_data'][$num+1])) {
				// 如果还有未处理的数据

				return $this->_ajax_message(0, 'ok'.$num);
			} else {
				// 已经处理完毕，则返回结果列表
				return $this->_ajax_message(0, 'ok'.$num, $completed_data);
			}

		} elseif ($action == 'report') {
			// 显示报告页面

			if (empty($this->_completed_list)) {
				$this->message('error', '暂无可用的导入报告');
			}

			$report_list = array(
					'ignore' => $this->_completed_list['ignore'],
					'success' => $this->_completed_list['success']
			);

			$this->view->set('ignore_count', count($this->_completed_list['ignore']));
			$this->view->set('success_count', count($this->_completed_list['success']));
			$this->view->set('report_col_field_names', $this->_completed_list['cold_fields']);
			$this->view->set('report_col_span', count($this->_completed_list['cold_fields']) + 2);
			$this->view->set('report_col_field_setting', $this->_addressbook_uda_get->excel_fields);

			$this->view->set('report_list', $report_list);

		}

		$this->view->set('action', $action);


		// 提交分析的url
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('action'=>'parse', 'ajax'=>1)));

		// 提交修改的url
		$this->view->set('form_change_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('action' => 'submitparse', 'ajax' => 1)));

		// Excel模板下载Url
		$this->view->set('template_download_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('action'=>'downloadtemplate')));

		// 提交导入的url
		$this->view->set('post_address_book_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('action' => 'submit')));

		// 报告列表url
		$this->view->set('report_list_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('action' => 'report')));

		// 部门选择器
		$this->view->set('department_select', $this->_department_select('cd_id', array()));
	}

	/**
	 * 下载模板文件
	 * @param string $filename 下载的文件名
	 * @return void
	 */
	private function _action_downloadtemplate($filename){

		$title_string = array();
		$title_width = array();
		$row_data = array();
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data($this->_template_data());
		excel::make_excel_download($filename, $title_string, $title_width, $row_data, $options, $attrs);
	}

	/**
	 * 导入通讯录数据到数据库，并输出结果报告数据
	 * @param array $fields
	 * @param array $values
	 */
	private function _import_data($fields, $values){

		$report_list = array(
				'ignore'=>array(),
				'success'=>array(),
		);
		$report_list_count = array(
				'ignore'=>0,
				'success'=>0
		);

		foreach ($values as $row => $data) {

			$rowData = array();
			/** 按字段来读取每列 */
			foreach ($fields as $colnum => $colfield) {
				$rowData[$colfield] = isset($data[$colnum]) ? $data[$colnum] : '';
			}

			if (!empty($rowData)) {

				/** 第一列为“#”则忽略该行导入 */
				if (strpos($data[0],'#') !== false) {
					//$rowData['_result_msg'] = '#忽略导入';
					//$report_list['ignore'][] = $rowData;
					//continue;
				}

				/** 检查通讯录字段合法性 */
				$check = parent::_field_check($rowData);
				if (empty($check)) {
					$rowData['_result_msg'] = '未读取到通讯录信息';
					$report_list['ignore'][] = $rowData;
					continue;
				}
				/** 该行数据不合法 */
				if (!is_array($check)) {
					$rowData['_result_msg'] = $check;
					$report_list['ignore'][] = $rowData;
					continue;
				}
//FIXME !!重写
				if ($this->_insert_addressbook($check)) {
					$rowData['_result_msg'] = '添加成功';
					$report_list['success'][]=	$rowData;
				} else {
					$rowData['_result_msg'] = '添加失败';
					$report_list['ignore'][] = $rowData;
				}
			}
		}

		$report_list_count['ignore'] = count($report_list['ignore']);
		$report_list_count['success'] = count($report_list['success']);

		return array($report_list, $report_list_count);
	}

	/**
	 * 提供给 Excel 模板使用的例子数据，注意字段需要与 voa_uda_frontend_addressbook_base->excel_fields 进行对应
	 * @see voa_uda_frontend_addressbook_base;
	 * @return array
	 */
	protected function _template_data(){
		return array (
				0 => array (
						'#'=>'#',
						'cab_number' => '1234',
						'cab_active' => '在职',
						'cab_realname' => '张三',
						//'cd_name' => '总经理办公室',
						'cj_name' => '总经理',
						'cab_mobilephone' => '13812345678',
						'cab_idcard' => '200101197905210413',
						'cab_gender' => '男',
						'cab_telephone' => '86868686',
						'cab_email' => 'zhangsan@company.com',
						'cab_qq' => '100',
						'cab_weixinid' => 'weixin',
						'cab_birthday' => '1979-05-21',
						'cab_address' => '上海徐汇区',
						'cab_remark' => '填写备注说明，长度不超过255个字符',
				),
				1 => array (
						'#'=>'#',
						'cab_number' => '1235',
						'cab_active' => '离职',
						'cab_realname' => '李四',
						//'cd_name' => '总经理办公室',
						'cj_name' => '总经理助理',
						'cab_mobilephone' => '18612345678',
						'cab_idcard' => '300101198007210413',
						'cab_gender' => '女',
						'cab_telephone' => '626262662',
						'cab_email' => 'lisi@company.com',
						'cab_qq' => '2000',
						'cab_weixinid' => 'weixin2',
						'cab_birthday' => '1980-07-21',
						'cab_address' => '上海闵行区',
						'cab_remark' => '',
				),
				2 => array (
						'#'=>'#',
						'cab_number' => '1236',
						'cab_active' => '在职',
						'cab_realname' => '王五',
						//'cd_name' => '财务部',
						'cj_name' => '财务',
						'cab_mobilephone' => '15812345678',
						'cab_idcard' => '100101198207210413',
						'cab_gender' => '男',
						'cab_telephone' => '12312312',
						'cab_email' => 'wangwu@company.com',
						'cab_qq' => '30000',
						'cab_weixinid' => 'weixin2',
						'cab_birthday' => '1982-07-21',
						'cab_address' => '上海长宁区',
						'cab_remark' => '',
				),
		);
	}
}
