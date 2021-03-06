<?php
/**
 * add.php
 * 云工作后台/系统设置/门店管理/添加门店
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_shop_add extends voa_c_admincp_system_shop_base {

	/** 当前执行动作名 */
	private $__action = '';

	/** 可允许的动作集 */
	private $__action_names = array(
		'addsubmit' => '新增单个门店',
		'gettpl' => '下载模板文件',
		'batch' => '批量导入',
		'uploadexcel' => '上传 Excel 文件',
		'import' => '导入门店数据',
		'delete' => '删除门店',
		'ajax_add' => '添加门店',
		'ajax_edit' => '编辑门店',
		'batchsubmit' => '批量提交'
	);

	/** 模板字段定义 */
	private $__fields = array(
		'region_level_1' => array('name' => '所在一级区域', 'width' => 20),
		'region_level_2' => array('name' => '所在二级区域', 'width' => 20),
		'region_level_3' => array('name' => '所在三级区域', 'width' => 20),
		'name' => array('name' => '门店名称', 'width' => 20),
		'address' => array('name' => '门店地址', 'width' => 40),
		'master' => array('name' => '门店负责人', 'width' => 20),
		'normal' => array('name' => '门店相关人(多个相关人之间以逗号分隔)', 'width' => 90),
	);

	/** 临时存储目录路径 */
	private $__tmp_path = '';
	/** 临时导入的数据储存路径 */
	private $__tmp_data_path = '';

	public function execute() {

		// 当前执行的动作
		$this->__action = (string)$this->request->get('subaction');
		// 获取默认的动作
		if (!isset($this->__action_names[$this->__action])) {
			$this->__action = false;
			unset($_actions);
		}
		// 上传以及处理文件时的临时目录路径
		$this->__tmp_path = voa_h_func::get_sitedir(startup_env::get('domain'));
		if (!in_array(substr($this->__tmp_path, -1), array('\\', '/'))) {
			$this->__tmp_path .= DIRECTORY_SEPARATOR;
		}
		$this->__tmp_path .= 'temp'.DIRECTORY_SEPARATOR;

		// 临时数据储存路径文件位置
		$this->__tmp_data_path = $this->__tmp_path.'data_place.php';



		if ($this->__action !== false) {
			// 执行具体的动作
			$method = '_execute_'.$this->__action;
			$this->$method();
		} else {

			// 批量导入的url
			$this->view->set('region_batch_url', '?subaction=batch');
			// 下载模板文件的 url
			$this->view->set('download_tpl_url', '?subaction=gettpl');
			// 上传批量 excel url
			$this->view->set('form_add_batch_url', '?subaction=uploadexcel');
			// 批量分步导入
			$this->view->set('batch_import_url', '?subaction=import');
			// 提交单个门店添加请求url
			$this->view->set('form_submit_single_url', '?subaction=addsubmit');

			// 默认的负责人和相关人员，因为是新增所以都为空
			$default_master = array();
			$default_normal = array();
			// 全部区域列表
			$placeregion_list = array();

			$this->view->set('default_master', rjson_encode($default_master));
			$this->view->set('default_normal', rjson_encode($default_normal));
			$this->view->set('placeregion_list', rjson_encode($placeregion_list));

			// 批量数据提交
			$this->view->set('batch_submit_url', $this->cpurl($this->_module
					, $this->_operation, $this->_subop, $this->_module_plugin_id
						, array('subaction' => 'batchsubmit')));

			$this->output('system/shop/add');
		}

		return true;
	}

	/**
	 * 提交新增单个门店请求
	 * @return boolean
	 */
	private function _execute_addsubmit() {

		if (!$this->_is_post()) {
			return $this->_admincp_error_message(1001, '非法的提交请求');
		}

		// 载入uda
		$uda = new voa_uda_frontend_common_place_add();
		$service = new voa_s_oa_common_place();
		$uda_place_member = new voa_uda_frontend_common_place_member_update();

		// 请求添加门店参数
		$request = array(
			'placetypeid' => $this->_placetypeid,
			'placeregionid' => (int)$this->request->post('placeregionid'),
			'name' => (string)$this->request->post('name'),
			'address' => (string)$this->request->post('address')
		);

		// 请求添加负责人的参数
		$master_uid = (array)$this->request->post('master_uid');
		$request_master = array(
			'id' => 0,
			'type' => voa_d_oa_common_place_member::TYPE_PLACE,
			'level' => voa_d_oa_common_place_member::LEVEL_CHARGE,
			'uid' => $master_uid
		);
		// 请求添加相关人的参数
		$normal_uid = (array)$this->request->post('normal_uid');
		$request_normal = array(
			'id' => 0,
			'type' => voa_d_oa_common_place_member::TYPE_PLACE,
			'level' => voa_d_oa_common_place_member::LEVEL_NORMAL,
			'uid' => $normal_uid
		);

		// 添加结果
		$result = array();

		try {
			$service->begin();
			// 新增区域
			$uda->doit($request, $result);
			// 新增负责人
			if ($master_uid) {
				$request_master['id'] = $result['placeid'];
				$uda_place_member->doit($request_master);
			}
			// 新增相关人
			if ($normal_uid) {
				$request_normal['id'] = $result['placeid'];
				$uda_place_member->doit($request_normal);
			}
			$service->commit();
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
			return false;
		} catch (Exception $e) {
			$service->rollback();
			logger::error($e);
			$this->_admincp_system_message($e);
			return false;
		}

		$this->message('success', '新增门店操作完毕', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id), false);
		return true;
	}

	/**
	 * 下载区域批量导入模板
	 * @return void
	 */
	protected function _execute_gettpl() {

		// 标题栏样式定义
		$options = array(
			'title_text_color' => 'FFFFFF00',
			'title_background_color' => 'FF808000',
		);
		// 下载的文件名
		$filename = '畅移云工作_门店批量导入';
		// 标题文字 和 标题栏宽度
		$title_string = $title_width = array();
		foreach ($this->__fields as $_key => $_ini) {
			$title_string[] = $_ini['name'];
			$title_width[] = $_ini['width'];
		}
		// 默认数据
		$row_data = array();
		// 载入 Excel 类
		excel::make_excel_download($filename, $title_string, $title_width, $row_data, $options);
		return;
	}

	/**
	 * 上传 excel 文件
	 * @return void
	 */
	protected function _execute_uploadexcel() {

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
		$current_config['max_size'] = config::get(startup_env::get('app_name').'.attachment.max_size');
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
		rfwrite($this->__tmp_data_path, "<?php\r\n\$excel_data = ".var_export($excel_parse_data, true).";");

		list($field, $list) = $excel_parse_data;

		if (($c1 = count($field)) != ($c2 = count($this->__fields))) {
			$this->_json_message(1010, '导入的门店列表文件格式不正确('.$c1.'/'.$c2.')，请使用模板导入');
		}

		$output = $this->__create_data_list($field, $list);

		$this->_json_message(0, 'OK', $output);
	}

	protected function _execute_resubmit() {

	}

	/**
	 * 批量分步导入
	 * @return void
	 */
	protected function _execute_import() {

		// 读取各个分区、负责人信息
		$name = trim((string)$this->request->post('name'));
		$region_level_1 = trim((string)$this->request->post('region_level_1'));
		$region_level_2 = trim((string)$this->request->post('region_level_2'));
		$region_level_3 = trim((string)$this->request->post('region_level_3'));
		$address = trim((string)$this->request->post('address'));
		$master = trim((string)$this->request->post('master'));
		$normal = trim((string)$this->request->post('normal'));

		// 由于只允许创建无分区或者有且只有三级分区，因此每个分区均需要判断
		if (!$name) {
			$this->_json_message(1011, '门店名称');
		}
		if (!$region_level_1 || !$region_level_2 || !$region_level_3) {
			$this->_json_message(1012, '门店所在区域必须填写');
		}
		if (!$address) {
			$this->_json_message(1013, '门店地址必须填写');
		}
		if (!$master) {
			$this->_json_message(1013, '门店负责人必须填写');
		}

		$region = array(
			1 => $region_level_1,
			2 => $region_level_2,
			3 => $region_level_3
		);

		if (!$this->__import_single($name, $address, $master, $normal, $region)) {
			// 如果写入失败，则直接退出，此处未必能执行（由于该方法抛出异常），仅做意外的补充处理完善逻辑
			return false;
		}

		$this->_json_message();
		return true;
	}

	/**
	 * 导入一个门店
	 * @param string $name 名称
	 * @param string $address 地址
	 * @param string $master 负责人
	 * @param string $normal 相关人员
	 * @param array $region 区域
	 * @return bool
	 */
	private function __import_single($name, $address, $master, $normal, $region) {

		$s_place = new voa_s_oa_common_place();

		//获取区域ID
		$region_id = 0;
		if (!$this->__find_regionid($region, $region_id)) {
			return false;
		}
		if (empty($region_id)) {
			return $this->_admincp_error_message(1006, '门店所在区域必须填写');
		}

		// 查找该门店是否已存在
		$uda_place_list = new voa_uda_frontend_common_place_list();
		$request_list = array(
			'name' => $name,
			'placeregionid' => $region_id,
			'placetypeid' => $this->_placetypeid,
			'remove' => 0
		);
		$result_list = array();
		$options_list = array('form_db' => 1);
		try {
			$uda_place_list->doit($request_list, $result_list, $options_list);
		} catch (help_exception $h) {
		} catch (Exception $e) {
		}

		// 存在此门店
		if (!empty($result_list['result'])) {
			return $this->_admincp_error_message(1007, '存在同名门店');
		}

		// 尝试找到负责人id
		$master_uid = array();
		$this->__find_uid($master, $master_uid);
		// 找到相关人id
		$normal_uid = array();
		if (!empty($normal)) {
			$this->__find_uid($normal, $normal_uid);
		}
		if (empty($master_uid)) {
			return $this->_admincp_error_message(1005, '门店负责人必须填写');
		}

		// 准备添加门店信息
		$uda_place = new voa_uda_frontend_common_place_add();
		$request = array(
			'placetypeid' => $this->_placetypeid,
			'placeregionid' => $region_id,
			'name' => $name,
			'address' => $address
		);
		$result = array();
		$options = array();

		// 载入场所用户表uda
		$uda_place_member = new voa_uda_frontend_common_place_member_update();

		try {
			$s_place->begin();

			// 新增门店
			$uda_place->doit($request, $result, $options);

			// 新增门店负责人
			if ($master_uid) {
				$request_master = array(
					'id' => $result['placeid'],
					'type' => voa_d_oa_common_place_member::TYPE_PLACE,
					'level' => voa_d_oa_common_place_member::LEVEL_CHARGE,
					'uid' => $master_uid
				);
				$result_master = array();
				$options_master = array();
				$uda_place_member->doit($request_master, $result_master, $options_master);
			}

			// 新增门店相关人
			if ($normal_uid) {
				$request_normal = array(
					'id' => $result['placeid'],
					'type' => voa_d_oa_common_place_member::TYPE_PLACE,
					'level' => voa_d_oa_common_place_member::LEVEL_NORMAL,
					'uid' => $normal_uid
				);
				$result_normal = array();
				$options_normal = array();
				$uda_place_member->doit($request_normal, $result_normal, $options_normal);
			}

			$s_place->commit();
		} catch (help_exception $h) {
			$s_place->rollback();
			return $this->_admincp_error_message($h);
		} catch (Exception $e) {
			$s_place->rollback();
			logger::error($e);
			return $this->_admincp_system_message($e);
		}

		return true;
	}

	/**
	 * 找到给定的用户帐号信息的uid列表
	 * @param string $accounts 帐号信息，多个之间半角逗号分隔
	 * @param array $uids (引用结果)uid列表
	 * @return boolean
	 */
	private function __find_uid($accounts, &$uids) {

		if (strpos($accounts, ',') !== false) {
			$accounts = explode(',', $accounts);
		} else {
			$accounts = array($accounts);
		}
		// uda载入
		$uda_member_find = &uda::factory('voa_uda_frontend_member_find');
		// 由于用户输入的是三类帐号任意之一，因此，需三个条件同时给出，以便于查询到具体的人员
		$find_uid_request = array(
			'mobile' => $accounts,
			'email' => $accounts,
			'weixinid' => $accounts,
			'username' => $accounts
		);

		// 获取具体的人员uid
		try {
			$uda_member_find->doit($find_uid_request, $uids);
		} catch (help_exception $h) {
			//return false;
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			//return false;
			$this->_admincp_system_message($e);
		}

		return true;
	}

	/**
	 * 找到已存在的分区或试图添加分区
	 * @param array $region 分区信息
	 * + 1 一级分区
	 * + 2 二级分区
	 * + 3 三级分区
	 * @param number $placeregionid (引用结果)分区ID
	 * @return boolean
	 */
	private function __find_regionid(array $region, &$placeregionid = 0) {

		/**
		 * 思路：
		 * 由于三级区域同时写入，因此三级按顺序分别导入，第一级的上级ID为0
		 * 第二级的上级ID为第一级的ID 。。。
		 */
		// 上级区域ID
		$parentid = 0;

		foreach ($region as $_region_name) {

			// 当前的分区信息
			$current = array();
			// 尝试写入当前分区
			if (!$this->__find_region_single($parentid, $_region_name, '', $current)) {

				// 如果写入失败，则直接退出，此处未必能执行（由于该方法抛出异常），仅做意外的补充处理完善逻辑
				break;
			}
			// 当前分区的ID，也是下级分区的父级ID
			$parentid = $current['placeregionid'];
		}

		// 最后获取到的分区信息$current
		$placeregionid = $current['placeregionid'];

		return true;
	}

	/**
	 * 试图找到或者添加一个分区名
	 * @param number $parentid 上级分区ID
	 * @param string $name 分区名称
	 * @param string $master 负责人
	 * @param array $region (引用结果)分区信息
	 * @return boolean
	 */
	private function __find_region_single($parentid, $name, $master, &$region) {

		$service_place = new voa_s_oa_common_place_region();
		$service_place_member = new voa_s_oa_common_place_member();

		// 尝试找到负责人id
		$master_uid = array();
		if ($master) {
			$this->__find_uid($master, $master_uid);
		}

		// 查询指定的区域是否存在
		$request = array(
			'placeregionid' => 0,
			'placetypeid' => $this->_placetypeid,
			'parentid' => $parentid,
			'name' => $name
		);
		$region = array();
		try {
			$uda_region_get = &uda::factory('voa_uda_frontend_common_place_region_get');
			$uda_region_get->doit($request, $region, array('from_db' => true));
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 存在此分区信息，直接返回
		if ($region) {
			return true;
		}

		// 该分区不存在，则尝试添加
		try {

			$service_place->begin();
			if (!$region) {
				// 未找到则尝试添加新分区
				$uda_region_add = &uda::factory('voa_uda_frontend_common_place_region_add');
				$uda_region_add->doit(array(
					'placetypeid' => $this->_placetypeid,
					'parentid' => $parentid,
					'name' => $name
				), $region, array('no_update_cache' => true));
			}

			// 尝试导入负责人
			if ($master_uid) {
				$uda_member_update = &uda::factory('voa_uda_frontend_common_place_member_update');
				$s_place_member = new voa_s_oa_common_place_member();
				$request = array(
					'id' => $region['placeregionid'],
					'type' => voa_d_oa_common_place_member::TYPE_REGION,
					'level' => voa_d_oa_common_place_member::LEVEL_CHARGE,
					'uid' => $master_uid
				);
				$result = array();
				$uda_member_update->doit($request, $result);
			}

			$service_place->commit();
		} catch (help_exception $h) {
			$service_place->rollback();
			$this->_admincp_error_message($h);
			return false;
		} catch (Exception $e) {
			$service_place->rollback();
			logger::error($e);
			$this->_admincp_system_message($e);
			return false;
		}

		return true;
	}

	/**
	 * 批量提交数据，用于处理编辑后的错误数据，类似execel导入的后半部的处理过程
	 * @return void
	 */
	protected function _execute_batchsubmit() {

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
			$this->_admincp_error_message(1002, '门店名称数据异常');
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
	}

	/**
	 * 将数据整理为批量导入需要的格式
	 * @param array $field 字段定义
	 * @param array $list 数据列表
	 * @return array
	 */
	private function __create_data_list($field, $list) {

		// “忽略”列，键名定义
		$key_ignore = '_ignore';
		// “导入结果”列，键名定义
		$key_result = '_result';

		// 标题栏总宽度
		$width_total = 0;
		$_fields = array();
		$_fields[] = array('key' => $key_ignore, 'name' => '忽略', 'width' => 12);
		$_fields = array_merge($_fields, $this->__fields);
		$_fields[] = array('key' => $key_result, 'name' => '导入结果', 'width' => 120);
		foreach ($_fields as $_key => $_ini) {
			$width_total = $width_total + $_ini['width'];
		}
		unset($_key, $_ini);

		// 取得标题栏列的名称和宽度比例
		$field_name = array();
		foreach ($_fields as $_key => $_ini) {
			$field_name[] = array(
				'key' => $_key,
				'name' => $_ini['name'],
				'width' => round($_ini['width']/$width_total, 2) * 100
			);
		}
		unset($_key, $_ini, $width_total);

		// 重新整理导入的数据列表
		$data_list = array();
		foreach ($list as $_key => $_val) {
			foreach ($_val as $_k => $_v) {
				$data_list[$_key][$field[$_k]] = $_v !== null ? $_v : '';
			}
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

}
