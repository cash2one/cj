<?php
/**
 * region.php
 * 云工作后台/系统设置/门店管理/区域管理
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_shop_region extends voa_c_admincp_system_shop_base {

	/** 当前执行动作名 */
	private $__action = '';

	/** 可允许的动作集 */
	private $__action_names = array(
		'list' => '区域列表',
		'gettpl' => '下载模板文件',
		'batch' => '批量导入',
		'uploadexcel' => '上传 Excel 文件',
		'import' => '导入区域数据',
		'delete' => '删除区域',
		'ajax_add' => '添加区域',
		'ajax_edit' => '编辑区域',
		'bindmaster' => '绑定负责人',
		'batchsubmit' => '批量数据提交'
	);

	/** 模板字段定义 */
	private $__fields = array(
		'level_1' => array('name' => '一级区域', 'width' => 30),
		'level_1_master' => array('name' => '一级负责人', 'width' => 30),
		'level_2' => array('name' => '二级区域', 'width' => 30),
		'level_2_master' => array('name' => '二级负责人', 'width' => 30),
		'level_3' => array('name' => '三级区域', 'width' => 30),
		'level_3_master' => array('name' => '三级负责人', 'width' => 30),
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
			$_actions = array_keys($this->__action_names);
			$this->__action = $_actions[0];
			unset($_actions);
		}
		// 上传以及处理文件时的临时目录路径
		$this->__tmp_path = voa_h_func::get_sitedir(startup_env::get('domain'));
		if (!in_array(substr($this->__tmp_path, -1), array('\\', '/'))) {
			$this->__tmp_path .= DIRECTORY_SEPARATOR;
		}
		$this->__tmp_path .= 'temp'.DIRECTORY_SEPARATOR;

		// 临时数据储存路径文件位置
		$this->__tmp_data_path = $this->__tmp_path.'data.php';

		// 批量导入的url
		$this->view->set('region_batch_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('subaction' => 'batch')));
		// 下载模板文件的 url
		$this->view->set('download_tpl_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('subaction' => 'gettpl')));
		// 上传批量 excel url
		$this->view->set('form_add_batch_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('subaction' => 'uploadexcel')));
		// 批量分步导入
		$this->view->set('batch_import_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('subaction' => 'import')));
		// 批量数据提交
		$this->view->set('batch_submit_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('subaction' => 'batchsubmit')));
		$this->view->set('add_region_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		// 执行具体的动作
		$method = '_execute_'.$this->__action;
		$this->$method();

		return true;
	}

	/**
	 * 区域列表管理
	 * @return void
	 */
	protected function _execute_list() {

		// 载入uda
		$uda = &uda::factory('voa_uda_frontend_common_place_region_list');
		// 构造uda请求
		$request = array(
			'placetypeid' => $this->_placetypeid,// 所在类型
			'parentid' => 0,// 上级分区id
			'member'	=>	1,	//获取负责人
			'childrens' => 1// 显示所有下级
		);
		// 返回结果
		$result = array();
		// uda配置
		$options = array(
			'use_cache' => 0
		);
		try {
			$list = $uda->doit($request, $result, $options);
		} catch (help_exception $h) {
			return $this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			return $this->_admincp_system_message($e);
		}
		unset($request, $options);

		// 输出给前端js区域列表
		$list = array();
		$this->__reset_region_list($result, $list);
		// 输出给前端js的区域人员列表
		$member_list = array();
		$this->__reset_region_member_list($result, $member_list);


		$this->view->set('list', rjson_encode($list));
		$this->view->set('member_list', rjson_encode($member_list));
		$this->output('system/shop/region');
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
		$filename = '畅移云工作_区域批量导入';
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
		// 写入临时储存
		rfwrite($this->__tmp_data_path, "<?php\r\n\$excel_data = ".var_export($excel_parse_data, true).";");

		list($field, $list) = $excel_parse_data;

		if (($c1 = count($field)) != ($c2 = count($this->__fields))) {
			$this->_json_message(1010, '导入的区域列表文件格式不正确('.$c1.'/'.$c2.')，请使用模板导入');
		}

		// 转换提交的数据为批量导入需要的格式
		$output = $this->__create_data_list($field, $list);

		$this->_json_message(0, 'OK', $output);
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

		// 整理格式，以一级为标准
		$level_1 = $field[0];
		if (!isset($data[$level_1])) {
			$this->_admincp_error_message(1002, '一级区域数据异常');
			return false;
		}

		// 整理数据
		$list = array();
		foreach ($data[$level_1] as $_id => $_val) {
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
	 * 批量分步导入
	 * @return void
	 */
	protected function _execute_import() {

		// 读取各个分区、负责人信息
		$level[1] = trim((string)$this->request->post('level_1'));
		$master[1] = trim((string)$this->request->post('level_1_master'));
		$level[2] = trim((string)$this->request->post('level_2'));
		$master[2] = trim((string)$this->request->post('level_2_master'));
		$level[3] = trim((string)$this->request->post('level_3'));
		$master[3] = trim((string)$this->request->post('level_3_master'));

		// 由于只允许创建无分区或者有且只有三级分区，因此每个分区均需要判断
		if (!$level[1]) {
			$this->_json_message(1011, '一级区域必须填写');
		}
		if (!$level[2]) {
			$this->_json_message(1012, '二级区域必须填写');
		}
		if (!$level[3]) {
			$this->_json_message(1013, '三级区域必须填写');
		}

		/**
		 * 思路：
		 * 由于三级区域同时写入，因此三级按顺序分别导入，第一级的上级ID为0
		 * 第二级的上级ID为第一级的ID 。。。
		 */
		// 上级区域ID
		$parentid = 0;
		// 循环写入三级区域
		for ($i = 1; $i <= 3; $i++) {

			// 当前的分区信息
			$current = array();
			// 尝试写入当前分区
			if (!$this->__import_region_single($parentid, $level[$i], $master[$i], $current)) {

				// 如果写入失败，则直接退出，此处未必能执行（由于该方法抛出异常），仅做意外的补充处理完善逻辑
				break;
			}
			// 当前分区的ID，也是下级分区的父级ID
			$parentid = $current['placeregionid'];
		}

		// 输出成功信号
		$this->_json_message();
		return true;
	}

	/**
	 * 批量导入界面
	 * @return void
	 */
	protected function _execute_batch() {

		$this->output('system/shop/region_batch');
	}

	/**
	 * 添加
	 * @return void
	 */
	public function _execute_ajax_add() {

		$post = array(
			'placetypeid' => $this->_placetypeid,
			'parentid' => $this->request->get('parentid'),
			'deepin' => $this->request->get('deepin'),
		);
		$post['name'] = $post['deepin'].'级区域'.substr(microtime(1), -3);
		$uda = &uda::factory('voa_uda_frontend_common_place_region_add');
		try {
			$result = array();
			$rs = $uda->doit($post, $result);
			$this->_json_message(0, 'OK', $result);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
	}

	/**
	 * 删除指定区域
	 * @return void
	 */
	protected function _execute_delete() {

		$id = $this->request->get('placeregionid');
		$uda = &uda::factory('voa_uda_frontend_common_place_region_delete');
		try {
			$result = array();
			$rs = $uda->doit(array('placeregionid'=>$id), $result);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 删除成功
		$this->_admincp_success_message('删除操作完毕', $this->cpurl($this->_module, $this->_operation, $this->_subop));
	}

	/**
	 * 编辑
	 * @return void
	 */
	public function _execute_ajax_edit() {

		$post = array(
			'placeregionid'	=>	$this->request->get('placeregionid'),
			'name'	=>	$this->request->get('name'),
		);
		$uda = &uda::factory('voa_uda_frontend_common_place_region_edit');
		$result = array();
		$rs = $uda->doit($post, $result);
		if($rs) {
			$this->_ajax_message(0, '', $result);
		}else{
			$this->_ajax_message(1, '编辑失败');
		}
	}

	/**
	 * 绑定负责人
	 * @return void
	 */
	public function _execute_bindmaster() {

		$contact_id = (int) $this->request->get('contact_id');
		$region_id = (int) $this->request->get('region_id');
		$lv = voa_d_oa_common_place_member::LEVEL_CHARGE;	//负责人级别

		$uda_place_member_update = new voa_uda_frontend_common_place_member_update();
		try {

			$result = array();

			// 更新人员
			$uda_place_member_update->doit(array(
				'id' => $region_id,
				'type' => voa_d_oa_common_place_member::TYPE_REGION,
				'level' => $lv,
				'uid' => $contact_id ? array($contact_id) : array()
			), $result);

		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error(print_r($e, true));
			$this->_admincp_system_message($e);
		}

		$this->_ajax_message(0, '', array());
	}

	/**
	 * 导入一个区域
	 * @param number $parentid 上级区域ID
	 * @param string $name 区域名称
	 * @param string $master 负责人信息：手机号、微信号、邮箱三者其一
	 * @param array $region (引用结果)区域信息
	 * @return boolean
	 */
	private function __import_region_single($parentid, $name, $master, &$region) {

		$service_place = new voa_s_oa_common_place_region();
		$service_place_member = new voa_s_oa_common_place_member();

		// 尝试找到负责人id
		$master_uid = array();
		if ($master) {
			$uda_member_find = &uda::factory('voa_uda_frontend_member_find');
			// 由于用户输入的是三类帐号任意之一，因此，需三个条件同时给出，以便于查询到具体的人员
			$find_uid_request = array(
				'mobile' => explode($service_place_member->uid_comma, $master),
				'email' => explode($service_place_member->uid_comma, $master),
				'weixinid' => explode($service_place_member->uid_comma, $master)
			);
			// 获取具体的人员uid
			try {
				$uda_member_find->doit($find_uid_request, $master_uid);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
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

		// 存在此分区信息，直接返回（但尝试导入分区负责人）
		if ($region && !$master_uid) {
			// 未提供负责人，则表明不更新，则直接输出
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
	 * 重新整理区域列表，以适应前端需要
	 * @param array $list
	 * @param array $reset_list (引用结果)
	 * @return boolean
	 */
	private function __reset_region_list($list, &$reset_list) {

		$reset_list = array();
		$reset_list['level'] = array();
		if (!empty($list['level'])) {
			foreach ($list['level'] as $_id => $_val) {
				$reset_list['level'][$_id] = $_val;
			}
		}
		$reset_list['data'] = array();
		if (!empty($list['data'])) {
			// 剔除前端不需要的数据
			foreach ($list['data'] as $_id => $_data) {
				$reset_list['data'][(int)$_id] = array(
					'placeregionid' => (int)$_data['placeregionid'],
					'parentid' => (int)$_data['parentid'],
					'deepin' => (int)$_data['deepin'],
					'name' => (string)$_data['name'],
					'updated' => (int)$_data['updated']
				);
			}
		}

		return true;
	}

	/**
	 * 重新整理区域人员列表，以适应前端需要
	 * @param array $list
	 * @param array $reset_list (引用结果)
	 * @return boolean
	 */
	private function __reset_region_member_list($list, &$reset_list) {

		$reset_list = array();

		// 目前业务需求只有负责人，没有相关人
		$uids = array();
		$region_uid_list = array();
		if (!empty($list['member'])) {
			foreach ($list['member'] as $_placeregionid => $_data) {
				$region_uid_list[$_placeregionid] = !empty($_data[voa_d_oa_common_place_member::LEVEL_CHARGE])
						? $_data[voa_d_oa_common_place_member::LEVEL_CHARGE] : array();
				foreach ($region_uid_list[$_placeregionid] as $_uid) {
					$region_uid_list[] = $_uid;
				}
				unset($_uid);
			}
			unset($_data);
		}
		// 存在uid，则检出用户信息
		$user_list = array();
		if (!empty($uids)) {
			$uda_getuserlist = &uda::factory('voa_uda_frontend_member_getuserlist');
			$request = array('uids' => $uids);
			try {
				$uda_getuserlist->doit($request, $user_list);
			} catch (help_exception $h) {
				// 不需要输出错误
				//$this->_admincp_error_message($h);
			} catch (Exception $e) {
				// 不需要输出错误
				logger::error($e);
				//$this->_admincp_system_message($e);
			}
		}

		// 整理出用户信息并输出转换为前端需要的格式
		$reset_list = array();
		foreach ($region_uid_list as $_regionid => $_uids) {
			$reset_list[$_regionid]['user_list'] = '';
			$reset_list[$_regionid]['selector'] = array();
			if (empty($_uids) || !is_array($_uids)) {
				continue;
			}
			$_comma = '';
			foreach ($_uids as $_uid) {
				$_user = voa_h_user::get($_uid);
				if (empty($_user)) {
					continue;
				}
				$reset_list[$_regionid]['selector'][] = array(
					'id' => $_uid,
					'name' => $_user['m_username'],
					'input_name' => 'contacts'
				);
				$reset_list[$_regionid]['user_list'] .= $_comma.$_user['m_username'];
				$_comma = ', ';
			}
			unset($_uid);
		}
		unset($_regionid, $_uids);

		return true;
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
