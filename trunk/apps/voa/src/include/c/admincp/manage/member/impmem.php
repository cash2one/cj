<?php

/**
 * 导入用户
 * voa_c_admincp_manage_member_impmem
 * User: luckwang
 * Date: 15/4/7
 * Time: 下午6:20
 */
class voa_c_admincp_manage_member_impmem extends voa_c_admincp_manage_member_base
{

	/** 可允许的动作集 */
	private $__action_names = array(
		'downloadtpl' => '下载模板文件',
		'batch' => '批量导入',
		'uploadexcel' => '上传 Excel 文件',
		'import' => '导入用户数据',
		'batchsubmit' => '批量提交',
		'resubmit' => '重新提交'
	);

	/** 模板字段定义 */
	private $__fields = array(
		'name' => array('name' => '姓名'),
		//'openid' => array('name' => '账号'),
		'gender' => array('name' => '性别'),
		'department' => array('name' => '所属部门'),
		'job' => array('name' => '职位'),
		'weixin' => array('name' => '微信号'),
		'mobilephone' => array('name' => '手机号'),
		'email' => array('name' => '邮箱')
	);

	/**
	 * 部门uda更新
	 * @var null
	 */
	private $__uda_department_update = null;

	/**
	 * 部门uda获取
	 * @var null
	 */
	private $__uda_department_get = null;

	/** 临时存储目录路径 */
	private $__tmp_path = '';
	/** 临时导入的数据储存路径 */
	private $__tmp_data_path = '';

	public function execute()
	{
		$act = $this->request->get('act');
		if (isset($this->__action_names[$act])) {

			// 上传以及处理文件时的临时目录路径
			$this->__tmp_path = voa_h_func::get_sitedir(startup_env::get('domain'));
			if (!in_array(substr($this->__tmp_path, -1), array('\\', '/'))) {
				$this->__tmp_path .= DIRECTORY_SEPARATOR;
			}
			$this->__tmp_path .= 'temp' . DIRECTORY_SEPARATOR;

			// 临时数据储存路径文件位置
			$this->__tmp_data_path = $this->__tmp_path . 'data_members.php';

			foreach ($this->_settings['fields'] as $k => $field) {
				if ($field['status'] != 0) {
					$this->__fields[$k] = array('name' => $field['desc']);
				}
			}

			$act = '__' . $act;
			return $this->$act();
		}
		$this->view->set('batch_url', '/PubApi/Apicp/Field/Batch');
		$this->view->set('uploadexcel_url', '/PubApi/Apicp/Field/Uploadexcel');
		//$this->view->set('import_url', '/PubApi/Apicp/Field/Import');
		$this->view->set('resubmit_url', '/PubApi/Apicp/Field/Resubmit');
		$this->view->set('download_tpl_url', $this->cpurl($this->_module, $this->_operation, 'impmem', $this->_module_plugin_id, array('act' => 'downloadtpl')));
		//$this->view->set('batch_url', $this->cpurl($this->_module, $this->_operation, 'impmem', $this->_module_plugin_id, array('act' => 'batch')));
		//$this->view->set('uploadexcel_url', $this->cpurl($this->_module, $this->_operation, 'impmem', $this->_module_plugin_id, array('act' => 'uploadexcel')));
		//$this->view->set('import_url', $this->cpurl($this->_module, $this->_operation, 'impmem', $this->_module_plugin_id, array('act' => 'import')));
		//$this->view->set('resubmit_url', $this->cpurl($this->_module, $this->_operation, 'impmem', $this->_module_plugin_id, array('act' => 'resubmit')));

		$this->output('manage/member/impmem');
	}

	/**
	 * 下载批量模板
	 */
	private function __downloadtpl()
	{

		// 标题栏样式定义
		$options = array(
			'title_text_color' => 'FFFFFF00',
			'title_background_color' => 'FF808000',
		);
		// 下载的文件名
		$filename = '畅移云工作_用户批量导入';
		// 标题文字 和 标题栏宽度
		$title_width = array();
		$title_string = array();

		foreach ($this->__fields as $field) {
			$title_string[] = $field['name'];
		}

		// 默认数据
		$row_data = array();
		$row_data[0][0] = '张三';
		$row_data[0][1] = 'zhangsan';
		$row_data[0][2] = '男';
		$row_data[0][3] = '市场部/市场一部';
		$row_data[0][4] = '市场专员';
		$row_data[0][5] = 'wxid_demo';
		$row_data[0][6] = '13888888888';
		$row_data[0][7] = 'test@test.com';
		$row_data[1][0] = '李四';
		$row_data[1][1] = 'lishi';
		$row_data[1][2] = '女';
		$row_data[1][3] = '技术部;市场部/市场一部';
		$row_data[1][4] = '技术经理';
		$row_data[1][5] = 'wxid_demo1';
		$row_data[1][6] = '13333333333';
		$row_data[1][7] = 'demo@demo.com';
		// 载入 Excel 类
		excel::make_excel_download($filename, $title_string, $title_width, $row_data, $options);
		return;
	}

	/**
	 * 批量添加用户
	 */
	private function __batch()
	{
		$post = $this->request->postx();

		$submit['m_openid'] = isset($post['openid']) ? trim($post['openid']) : '';
		$submit['m_weixin'] = isset($post['weixin']) ? trim($post['weixin']) : '';
		$submit['m_mobilephone'] = isset($post['mobilephone']) ? trim($post['mobilephone']) : '';
		$submit['m_email'] = isset($post['email']) ? trim($post['email']) : '';
		$submit['m_username'] = isset($post['name']) ? trim($post['name']) : '';
		//电话/邮箱/微信不能同时为空
		if (empty($submit['m_mobilephone']) &&
			empty($submit['m_email']) &&
			empty($submit['m_weixin'])
		) {
			$this->_json_message('1', '微信号手机号邮箱不能同时为空');
		}

		if (empty($submit['m_username'])) {
			$this->_json_message('3', '用户姓名不能为空');
		}

		// 验证 openid 是否正确
		if (!empty($submit['m_openid']) && !preg_match('/^[a-z0-9_]+$/i', $submit['m_openid'])) {
			$this->_json_message('3', '账号不能为空');
			return true;
		}

		$submit['cj_name'] = isset($post['job']) ? trim($post['job']) : '';
		//性别
		$submit['m_gender'] = voa_d_oa_member::GENDER_UNKNOWN;
		if (isset($post['gender']) && $post['gender'] == '男') {

			$submit['m_gender'] = voa_d_oa_member::GENDER_MALE;
		} elseif (isset($post['gender']) && $post['gender'] == '女') {

			$submit['m_gender'] = voa_d_oa_member::GENDER_FEMALE;
		}

		//部门id
		if (isset($post['department'])) {

			$this->__uda_department_update = &uda::factory('voa_uda_frontend_department_update');
			$this->__uda_department_get = &uda::factory('voa_uda_frontend_department_get');

			$cds = explode(';', trim($post['department']));
			$cd_ids = array();
			foreach ($cds as $cd) {
				$cd = trim($cd);
				$cd_id = $this->get_sub_cd_id_by_cd_name($cd);
				if (is_numeric($cd_id) && $cd_id > 0) {
					$cd_ids[] = $cd_id;
				} else {
					$this->_json_message('2', $cd_id);
				}
				/*
				$uda_department = &uda::factory('voa_uda_frontend_department_get');
				if ($uda_department->get_cd_id_by_name($cd, $cd_id)) {
					if ($cd_id > 0) {
						$cd_ids[] = $cd_id;
					}
				}*/
			}
			$submit['cd_id'] = $cd_ids;
		}

		if (empty($submit['cd_id'])) {
			$this->_json_message('2', '没有找到对应的部门');
		}

		//设置扩展字段
		foreach ($this->_settings['fields'] as $k => $field) {
			if (isset($post[$k])) {
				if ($field['status'] == 2) {
					$submit['mf_' . $k] = trim($post[$k]);
				} elseif ($field['status'] == 1) {
					$submit['mf_ext' . $k] = trim($post[$k]);
				}
			}
		}
		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
		$member = array();
		try {
			$uda_member_update->update($submit, $member);
			// 更新部门人数
			$uda_member_update->update_department_usernum();
		} catch (Exception $e) {
			logger::error($e);
		}

		$this->_json_message($uda_member_update->errcode, $uda_member_update->errmsg);
	}

	/**
	 * 上传 excel 文件
	 * @return void
	 */
	private function __uploadexcel()
	{

		$current_config = array();
		// 储存根目录
		$current_config['save_dir_path'] = $this->__tmp_path;
		if (!is_dir($current_config['save_dir_path'])) {
			rmkdir($current_config['save_dir_path'], 0777, true);
		}
		// 允许上传的附件类型
		$current_config['allow_files'] = array('xls');
		// 储存附件的文件名格式
		$current_config['file_name_format'] = 'auto';
		// 允许上传的文件最大尺寸
		$current_config['max_size'] = config::get(startup_env::get('app_name') . '.attachment.max_size');
		// 源文件名
		$current_config['source_name'] = isset($_POST['fileName']) ? $_POST['fileName'] : 'x.xsl';
		// 储存格式
		$current_config['file_name_format'] = '{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:8}';
		// 上传文件
		$upload = new upload('upload', $current_config, 'upload');
		// 上传后的文件信息
		$result = $upload->get_file_info();
		if (!empty($result['error_code'])) {
			$this->_json_message($result['error_code'], $result['error']);
			return true;
		}

		// 上传的文件位置
		$file = $result['file_path'];

		// 解析 Excel 文件
		$excel = new excel();
		$excel_parse_data = $excel->parse_xsl($file, 0, $this->__fields, 0, 1);
		if (!$excel_parse_data) {
			$this->_json_message($excel->errcode, $excel->errmsg);
		}
		@unlink($file);
		// 写入临时储存
		rfwrite($this->__tmp_data_path, "<?php\r\n\$excel_data = " . var_export($excel_parse_data, true) . ";");

		list($field, $list) = $excel_parse_data;

		if (($c1 = count($field)) != ($c2 = count($this->__fields))) {
			$this->_json_message(1010, '导入的用户列表文件格式不正确(' . $c1 . '/' . $c2 . ')，请使用模板导入');
		}
		$output = $this->__create_data_list($field, $list);

		$this->_json_message(0, 'OK', $output);
	}

	/**
	 * 将数据整理为批量导入需要的格式
	 * @param array $field 字段定义
	 * @param array $list 数据列表
	 * @return array
	 */
	private function __create_data_list($field, $list)
	{

		// “忽略”列，键名定义
		$key_ignore = '_ignore';
		// “导入结果”列，键名定义
		$key_result = '_result';

		// 标题栏总宽度
		//$width_total = 0;
		$_fields = array();
		$_fields[] = array('key' => $key_ignore, 'name' => '忽略', 'width' => 12);
		$_fields = array_merge($_fields, $this->__fields);
		$_fields[] = array('key' => $key_result, 'name' => '导入结果', 'width' => 120);
		//foreach ($_fields as $_key => $_ini) {
		//    $width_total = $width_total + $_ini['width'];
		//}
		unset($_key, $_ini);

		// 取得标题栏列的名称和宽度比例
		$field_name = array();
		foreach ($_fields as $_key => $_ini) {
			$field_name[] = array(
				'key' => $_key,
				'name' => $_ini['name'],
				'width' => ''//round($_ini['width']/$width_total, 2) * 100
			);
		}
		unset($_key, $_ini, $width_total);

		// 重新整理导入的数据列表
		$data_list = array();
		foreach ($list as $_key => $_val) {
			$is_empty = true;
			$temp = array();
			foreach ($_val as $_k => $_v) {
				$temp[$field[$_k]] = $_v !== null ? $_v : '';
				if (!empty($temp[$field[$_k]])) {
					$is_empty = false;
				}
			}
			if ($is_empty === true) {
				unset($list[$_key]);
				continue;
			}
			$data_list[$_key] = $temp;
		}
		unset($_key, $_k, $_val, $_v);
		// 重新整理列表
		foreach ($list as $_key => &$_val) {
			foreach ($_val as $_k => &$_v) {
				if ($_v === null) {
					$_v = '';
				}
			}
			unset($_v, $_k);
		}
		unset($_key, $_val);

		$this->__import_dp($data_list);

		$output = array(
			'total' => count($list),
			'key_ignore' => $key_ignore,
			'key_result' => $key_result,
			'field' => $field,
			'field_name' => $field_name,
			'list' => $list,
			'data_list' => $data_list
		);

		return $output;
	}


	/**
	 * 批量提交数据，用于处理编辑后的错误数据，类似execel导入的后半部的处理过程
	 * @return void
	 */
	private function __resubmit()
	{

		// 字段定义
		$field = array_keys($this->__fields);
		// 读取上传的数据
		$data = array();
		foreach ($field as $_k) {
			$data[$_k] = $this->request->post($_k);
		}

		unset($_k, $_v);
		if (empty($data)) {
			$this->_admincp_error_message(1001, '没有待导入的数据');
			return false;
		}

		// 请求忽略的数据
		$ignore = (array)$this->request->post('ignore');

		// 整理格式，以名称为标准
		$name_key = array_search('name', $field);
		if (!isset($data[$field[$name_key]])) {
			$this->_admincp_error_message(1002, '名称数据异常');
			return false;
		}

		// 整理数据
		$list = array();
		foreach ($data[$field[$name_key]] as $_id => $_val) {
			if (isset($ignore[$_id])) {
				continue;
			}
			foreach ($field as $_field_id => $_field) {
				if (isset($data[$_field][$_id])) {
					$list[$_id][$_field_id] = $data[$_field][$_id];
				}
			}
		}

		if (empty($list)) {
			$this->_admincp_error_message('1003', '没有待导入的新数据');
			return false;
		}

		// 输出批量导入需要的格式
		$output = $this->__create_data_list($field, $list);

		$this->_json_message(0, 'OK', $output);
		return true;
	}

	/**
	 * 导入部门
	 * @param $data_list
	 */
	private function __import_dp($data_list) {

		if (!empty($data_list) &&
				is_array($data_list)) {

			$this->__uda_department_update = &uda::factory('voa_uda_frontend_department_update');
			$this->__uda_department_get = &uda::factory('voa_uda_frontend_department_get');

			foreach ($data_list as $data) {
				if (!empty($data['department'])) {
					$cds = explode(';', trim($data['department']));
					foreach ($cds as $cd) {
						$this->get_sub_cd_id_by_cd_name(trim($cd));
					}
				}
			}
		}
	}


	/**
	 * 获取最下级的部门id
	 * @param $cd_name_str
	 * @return int
	 */
	public function get_sub_cd_id_by_cd_name($cd_name_str)
	{
		//分隔部门
		$cd_names = explode('/', $cd_name_str);
		//获取公司id
		$upid = $this->get_top_cd_id();
		$children_cd_id = 0;
		//遍历公司
		foreach ($cd_names as $cd_name) {
			$children_cd_id = $this->get_cd_id_by_cd_name($cd_name, $upid);
			$upid = $children_cd_id;
			if (empty($upid) || !is_numeric($upid)) {
				return $upid;
			}
		}

		return $children_cd_id;
	}

	/**
	 * 获取部门id根据名称
	 * @param $cd_name
	 * @return int
	 */
	function get_cd_id_by_cd_name($cd_name, $upid)
	{
		$cd_name = trim($cd_name);
		$cd_id = $this->__uda_department_get->get_cd_id_by_name_upid($cd_name, $upid);
		if (!empty($cd_id)) {
			return $cd_id;
		}

		//不存在则添加部门
		$data['cd_upid'] = $upid;
		$data['cd_name'] = $cd_name;
		$data['cd_displayorder'] = 0;
		$data['cd_id'] = 0;
		$update = array();
		if ($this->__uda_department_update->update(array(), $data, $update)) {
			return $update['cd_id'];
		} else {
			return $this->__uda_department_update->errmsg;
		}
	}

	/**
	 * 获取公司id
	 * @return int
	 */
	function get_top_cd_id()
	{
		foreach ($this->_departments as $department) {
			if ($department['cd_upid'] == 0) {
				return $department['cd_id'];
			}
		}
		return 0;
	}
}
