<?php
/**
 * voa_c_admincp_manage_member_import
 * 通讯录导入
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_import extends voa_c_admincp_manage_member_base{

	/** 导入的Excel解析后的数据储存位置 */
	protected $_waited_data_save_path = '';

	/** 处理完的数据储存位置 */
	protected $_completed_data_save_path = '';

	protected $_waited_list = array();
	protected $_completed_list = array();

    //deprecated
	public function execute() {
return;
		// 获取当前站点的缓存目录
		$save_base = voa_h_func::get_sitedir();

		// 待处理的数据完整的储存路径
		$this->_waited_data_save_path = $save_base.DIRECTORY_SEPARATOR.'_member_waited_'.$this->_user['m_uid'].'.json';

		// 已处理的数据完整的储存路径
		$this->_completed_data_save_path = $save_base.DIRECTORY_SEPARATOR.'_member_completed_'.$this->_user['m_uid'].'.json';

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
			if (!$this->get_uploadfile('upload', $filepath)) {
				return $this->_ajax_message($this->_member_uda_get->errno, $this->_member_uda_get->error, array());
			}

			// 分析出上传的文件内容
			$result = array();
			if (!$this->parse_excel_data($filepath, $result)) {
				return $this->_ajax_message($this->_member_uda_get->errno, $this->_member_uda_get->error, array());
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

				foreach ($this->_excel_fields as $key => $c) {
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
			$is_success = false;
			// 处理多级部门信息, 如果不成功, 则
			//$submit['cd_id'] = $data['cd_id'];
			if (!$this->_parse_department($submit, $data['cd_id'])) {
				$source['_result_msg'] = '部门信息更新失败';
			} else {
				if (isset($current[0]) && strpos($current[0], '#') !== false) {
					// 标记为忽略，则跳过不处理

					$source['_result_msg'] = '#已标记为忽略';

				} else {
					// 尝试提交
					if ($this->_uda_member_insert->add($submit, $m)) {
						// 添加成功
						$source['_result_msg'] = '添加成功';
						$is_success = true;
					} else {
						$source['_result_msg'] = $this->_uda_member_insert->error;
					}

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
			$this->view->set('report_col_field_setting', $this->_excel_fields);

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

		$this->output('manage/member/import');
	}

	/**
	 * 解析部门信息
	 * @param array $submit 提交的数据
	 * @param int $default_cd_id 默认部门id
	 * @return boolean true
	 */
	protected function _parse_department(&$submit, $default_cd_id) {

		// 取部门名称
		$cd_name = (string)$submit['cd_name'];
		$cd_name = trim($cd_name);
		unset($submit['cd_name']);
		// 如果部门名称为空, 则取默认
		if (empty($cd_name)) {
			$submit['cd_id'] = $default_cd_id;
			return true;
		}

		// 部门
		$serv_dp = &service::factory('voa_s_oa_common_department');
		// uda
		$uda_update = &uda::factory('voa_uda_frontend_department_update');

		// 按分隔符切分部门信息
		$names = explode('/', $cd_name);
		// 部门/上级部门id
		$cd_upid = 0;
		$cd_id = 0;
		$is_new = false;
		// 取部门id
		foreach ($names as $_name) {
			$_name = trim($_name);
			// 如果部门名称为空
			if (empty($_name)) {
				continue;
			}

			// 取部门信息
			$dp = $serv_dp->fetch_by_cd_name_upid($_name, $cd_upid);
			if (!empty($dp)) {
				$cd_id = $dp['cd_id'];
				$cd_upid = $dp['cd_id'];
				continue;
			}

			// 新部门信息
			$new_dp = array(
				'cd_name' => $_name,
				'cd_upid' => $cd_upid,
				'cd_displayorder' => 99
			);
			// 入库
			$cur_dp = array();
			if (!$uda_update->update(array(), $new_dp, $cur_dp)) {
				logger::error($uda_update->errcode.':'.$uda_update->errmsg);
				return false;
			}

			// 更新部门上级id
			$cd_upid = $cur_dp['cd_upid'];
			$cd_id = $cur_dp['cd_id'];
			$is_new = true;
		}

		// 如果是新部门, 则更新缓存
		if ($is_new) {
			voa_h_cache::get_instance()->get('department', 'oa', true);
		}

		$submit['cd_id'] = $cd_id;
		return true;
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
				if ($this->_insert_member($check)) {
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
	 * 提供给 Excel 模板使用的例子数据，注意字段需要与 voa_uda_frontend_member_base->excel_fields 进行对应
	 * @see voa_uda_frontend_member_base;
	 * @return array
	 */
	protected function _template_data(){
		return array (
			0 => array (
				'#'=>'#',
				'm_number' => '1234',
				'm_active' => '在职',
				'm_username' => '张三',
				'cd_name' => '企业部/事业部',
				'cj_name' => '总经理',
				'm_mobilephone' => '13812345678',
				'm_email' => 'zhangsan@company.com',
				'mf_idcard' => '200101197905210413',
				'm_gender' => '男',
				'mf_telephone' => '021-86868686',
				'mf_qq' => '10012',
				'mf_weixinid' => 'weixin',
				'mf_birthday' => '1979-05-21',
				'mf_address' => '上海徐汇区',
				'mf_remark' => '填写备注说明，长度不超过255个字符',
			),
			1 => array (
				'#'=>'#',
				'm_number' => '1235',
				'm_active' => '离职',
				'm_username' => '李四',
				'cd_name' => '人事部/什么部',
				'cj_name' => '总经理助理',
				'm_mobilephone' => '18612345678',
				'm_email' => 'lisi@company.com',
				'mf_idcard' => '300101198007210413',
				'm_gender' => '女',
				'mf_telephone' => '021-62626262',
				'mf_qq' => '20003',
				'mf_weixinid' => 'weixin2',
				'mf_birthday' => '1980-07-21',
				'mf_address' => '上海闵行区',
				'mf_remark' => '',
			),
			2 => array (
				'#'=>'#',
				'm_number' => '1236',
				'm_active' => '在职',
				'm_username' => '王五',
				'cd_name' => '财务部/出纳部',
				'cj_name' => '财务',
				'm_mobilephone' => '15812345678',
				'm_email' => 'wangwu@company.com',
				'mf_idcard' => '100101198207210413',
				'm_gender' => '男',
				'mf_telephone' => '021-12312312',
				'mf_qq' => '30000',
				'mf_weixinid' => 'weixin3',
				'mf_birthday' => '1982-07-21',
				'mf_address' => '上海长宁区',
				'mf_remark' => '',
			),
		);
	}

	/**
	 * 获取上传的通讯录excel文件绝对路径
	 * @param string $file_var 上传控件名
	 * @param string $filepath <strong style="color:red">引用结果</strong>绝对路径
	 * @return boolean
	 */
	public function get_uploadfile($file_var, &$filepath){
		$upload = isset($_FILES[$file_var]) ? $_FILES[$file_var] : array();
		if (empty($upload) || !isset($upload['error'])) {
			$this->errmsg(1001, '对不起，请正确上传通讯录  Excel 文件。');
			return false;
		}
		$errMsg = '';
		switch ($upload['error']) {
			case 0:
				$errMsg = false;
				break;
			case 1:
			case 2:
				$upload_max_filesize = @ini_get('upload_max_filesize');
				if (!$upload_max_filesize) {
					$upload_max_filesize = 1048576*2;
				}
				$upload_max_filesize = size_count($upload_max_filesize);
				$errMsg = '您只能上传大小不超过 '.$upload_max_filesize.' 的通讯录 Excel 文件';
				break;
			case 3:
				$errMsg = '通讯录文件上传失败，请返回重试。';
				break;
			case 4:
				$errMsg = '请上传通讯录 Excel 文件。';
				break;
			case 6:
				$errMsg = '服务器临时目录错误，上传失败。';
				break;
			case 7:
				$errMsg = '服务器文件写入错误，上传失败。';
				break;
			default:
				$errMsg = '上传通讯录文件发生未知错误。';
				break;
		}
		if ($errMsg) {
			$this->errmsg(1002, $errMsg);
			return false;
		}
		if (!is_readable($upload['tmp_name'])) {
			$this->errmsg(1003, '上传的通讯录文件读取失败，请返回重试。');
			return false;
		}
		$filepath = $upload['tmp_name'];

		return true;
	}

	/**
	 * 解析Excel文件并以数组输出
	 * @param string $filepath
	 * @param array $result <strong style="color:red">引用结果</strong>
	 * @return boolean
	 */
	public function parse_excel_data($filepath, &$result){
		if (!is_readable($filepath)) {
			$this->errmsg(1001, '上传的通讯录文件读取失败，请返回重试。');
			return false;
		}

		$data = file_get_contents($filepath, FALSE, NULL, 0, 8);
		if ($data != pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1)) {
			$this->errmsg(1002, '上传的文件不是标准的通讯录模板格式，请使用下载的模板');
			return false;
		}

		$error_reporting = error_reporting();
		error_reporting(0);
		$excel = new excel();
		$dataList = $excel->read_from_xsl($filepath, 0, array());
		error_reporting($error_reporting);

		if (empty($dataList)) {
			$this->errmsg(1003, '没有读取到可用的通讯录数据，请确认上传的 Excel 文件正确');
			return false;
		}

		/** 字段中文名与表字段名对应关系 */
		$name2field = array();
		foreach ($this->_excel_fields as $k => $arr) {
			$name2field[rstrtolower($arr['name'])] = $k;
		}

		/** 自数据第一行（标题栏）获取标记与字段名之间对应关系 */
		$col2field = array();
		foreach ($dataList[0] as $colNum => $colName) {
			$colName = rstrtolower($colName);
			if (isset($name2field[$colName]) && strpos($name2field[$colName],'#') === false) {
				$col2field[$colNum] = $name2field[$colName];
			}
		}
		if (isset($dataList[0])) {
			unset($dataList[0]);
		}
		$result = array($col2field, $dataList);

		return true;
	}

}
