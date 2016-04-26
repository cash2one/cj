<?php
/**
 * voa_c_admincp_office_namecard_list
 * 企业后台/微办公管理/微名片/名片列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_inspect_shop extends voa_c_admincp_office_inspect_base {
	public $error = '';
	public $errno = 0;
	protected $_excel_fields = array(
		'#' => array('name'=>'#', 'width'=>'5',),
		'csp_name' => array('name'=>'店名*', 'width'=>16,),
		'cr_name_parent' => array('name'=>'城市*', 'width'=>14,),
		'cr_name' => array('name'=>'区域*', 'width'=>30,),
		'csp_address' => array('name'=>'地址*', 'width'=>100,),
	);

	protected function _init() {

		$cache_config = voa_h_cache::get_instance()->get('plugin.inspect.setting', 'oa');
		$this->_excel_fields['cr_name_parent']['name'] = $cache_config['title_city']."*";
		$this->_excel_fields['cr_name']['name'] = $cache_config['title_region']."*";
		$this->view->set('cache_config', $cache_config);

	}

	public function execute() {

		$this->_init();

		$acts = array('edit', 'delete', 'upload', 'download', 'list');
		$act = $this->request->get('act');
		$act = $act && in_array($act, $acts) ? $act : 'list';
		$func = '_ac_'.$act;
		$this->$func();

		$this->view->set('getRegionUrl', $this->cpurl($this->_module, $this->_operation, 'plan', $this->_module_plugin_id, array('act'=>'getregionlist')));
		$this->view->set('getShopUrl', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array('act'=>'getshoplist')));

		$this->view->set('addUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit')));
		$this->view->set('downloadUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'download')));
		$this->view->set('uploadUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'upload')));
		$this->view->set('defaultUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'edit', 'csp_id'=>'')));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'delete', 'csp_id'=>'')));

		if ($act == 'edit') {
			$this->output('office/inspect/edit_shop');
		} else {
			$this->output('office/inspect/shop');
		}
	}

	private function __assign_csp_id(&$condi, &$search) {

		if (empty($search['csp_ids'])) {
			return false;
		}

		$db = &service::factory('voa_s_oa_common_shop');
		$shops = $db->list_by_pks(explode(',', $search['csp_ids']));
		$search['csp_names'] = array();
		foreach ($shops as $item) {
			$search['csp_names'][] = $item['csp_name'];
			$condi['csp_id'][] = $item['csp_id'];
		}

		if (!empty($condi['csp_id'])) {
			$search['csp_names'] = implode(',', $search['csp_names']);
			$condi['csp_id'] = $condi['csp_id'];
		}
	}

	private function __assign_district(&$condi, &$search) {

		if (empty($search['district'])) {
			return false;
		}

		$db = &service::factory('voa_s_oa_common_region');
		$regions = $db->list_by_conds(array('cr_parent_id' => $search['city']));
		// for search form
		$search['district_org'] = $regions;
		//unset($regions);
		$db = &service::factory('voa_s_oa_common_shop');
		$shops = $db->list_by_conds(array('cr_id' => $search['district']));
		if ($shops) {
			$shop_ids = array();
			foreach ($shops as $val) {
				$shop_ids[] = $val['csp_id'];
			}

			$condi['csp_id'] = $shop_ids;
		} else {
			$condi['csp_id'] = '';
		}

		return true;
	}

	private function __assign_city(&$condi, &$search) {

		if (empty($search['city'])) {
			return false;
		}

		$db = &service::factory('voa_s_oa_common_region');
		$regions = $db->list_by_conds(array('cr_parent_id' => $search['city']));
		// for search form
		$search['district_org'] = $regions;
		if ($regions) {
			$regions_ids = array();
			foreach ($regions as $val) {
				$regions_ids[] = $val['cr_id'];
			}

			$db = &service::factory('voa_s_oa_common_shop');
			$shops = $db->list_by_conds(array('cr_id' => $regions_ids));
			if ($shops) {
				$shop_ids = array();
				foreach ($shops as $val) {
					$shop_ids[] = $val['csp_id'];
				}

				$condi['csp_id'] = $shop_ids;
			} else {
				$condi['csp_id'] = '';
			}
		} else {
			$condi['csp_id'] = '';
		}

		return true;
	}

	protected function _ac_list() {

		$condi = array();
		$search = array();
		$search['city'] = array();
		$search['district'] = array();
		$post = array();
		if ($this->request->post('submit')) {
			$post = $this->request->postx();
		} elseif ($this->request->get('submit')) {
			$post = $this->request->getx();
		}

		if ($post) {
			$search = $post['search'];
			$this->__assign_csp_id($condi, $search);
			if (!$this->__assign_district($condi, $search)) {
				$this->__assign_city($condi, $search);
			}
		}

		list($total, $multi, $list) = $this->_list($condi);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('region', $this->_get_region_list());

		$this->view->set('search', $search);
	}

	protected function _ac_download() {

		// 模板文件下载
		$filename = '畅移门店模板';
		$this->_action_downloadtemplate($filename);
	}

	protected function _ac_upload() {

		$filevar = 'files';
		$filepath = '';
		$result = array();
		if (!$this->_get_uploadfile($filevar, $filepath)) {
			echo json_encode(array('result' => array('status' => $this->erron, 'error' => $this->error)));
		}

		if ($this->_parse_excel_data($filepath, $result)) {
			$error = array();
			$sucesstotal = 0;
			$errortotal = 0;
			foreach ($result[1] as $key => $item) {
				$data = array();
				foreach ($result[0] as $k2 => $val) {
					if (empty($item[$k2])) {
						$error[] = array('key' => $val, 'msg' => '值为空', 'num' => $key);
						$errortotal ++;
						continue 2;
					}

					$data[$val] = $item[$k2];
				}

				$existed_condi = array('csp_name' => $data['csp_name'], 'csp_address' => $data['csp_address']);
				$db = &service::factory('voa_s_oa_common_shop');
				$existed = $db->count_by_conds($existed_condi);
				if (!empty($existed)) {
					$error[] = array('key' => 'csp_name', 'msg' => '数据已经存在: '.$data['csp_name'], 'num' => $key);
					$errortotal ++;
					continue;
				}

				$this->_save_shop($data);
				$sucesstotal ++;
			}
			/** 更新地区和门店的缓存 */
			$this->_update_shop_region_cache();
			echo json_encode(array('status' => 100, 'error' => $error, 'successtotal' => $sucesstotal, 'errortotal' => $errortotal));
		} else {
			/** 更新地区和门店的缓存 */
			$this->_update_shop_region_cache();
			echo json_encode(array('result' => array('status' => $this->erron, 'error' => $this->error)));
		}

		exit;
	}

	protected function _ac_edit() {

		$csp_id = (int)$this->request->get('csp_id');
		$data = array();

		if ($this->request->post('submit')) {
			$data['csp_name'] = $this->request->post('csp_name');
			$data['cr_name_parent'] = $this->request->post('cr_name_parent');
			$data['cr_name'] = $this->request->post('cr_name');
			$data['csp_address'] = $this->request->post('csp_address');
			$data['csp_id'] = $csp_id;
			// 如果大区小区不为空则保存
			if ($data['cr_name_parent'] && $data['cr_name']) {
				$this->_save_shop($data);
				/** 更新地区和门店的缓存 */
				$this->_update_shop_region_cache();
				echo json_encode(array('result'=>array('status'=>'100')));
			} else {
				echo json_encode(array('result'=>array('status'=>'200', "msg"=>"大区小区不能为空")));
			}
			exit;
		} elseif ($csp_id) {
			$db = &service::factory('voa_s_oa_common_shop');
			$data = $db->get($csp_id);
			$this->_shop_format($data);
		}

		$this->view->set('data', $data);
	}

	protected function _ac_delete() {

		$csp_id = (int)$this->request->get('csp_id');
		if (!empty($csp_id)) {
			$this->_delete_shop($csp_id);
		} else {
			$ids = $this->request->post('delete');
			if (!empty($ids)) {
				foreach ($ids as $val) {
					$this->_delete_shop($val);
				}
			}
		}

		/** 更新地区和门店的缓存 */
		$this->_update_shop_region_cache();
		header('location: '.$this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
		exit;
	}

	/** 更新门店和地区的缓存 */
	protected function _update_shop_region_cache() {

		voa_h_cache::get_instance()->get('shop', 'oa', true);
		voa_h_cache::get_instance()->get('region', 'oa', true);
	}

	protected function _shop_format(&$data) {

		if (empty($data['cr_id'])) {
			return true;
		}

		$uda = new voa_uda_frontend_common_region_get();
		$area = array();
		$uda->execute(array('cr_id' => $data['cr_id']), $area);
		if (empty($area)) {
			return true;
		}

		$data['cr_name'] = $area['cr_name'];
		$city = array();
		$uda->execute(array('cr_id' => $area['cr_parent_id']), $city);
		if (!empty($city)) {
			$data['cr_name_parent'] = $city['cr_name'];
		}

		return true;
	}

	protected function _list($condi) {

		// 每页显示数
		$condi['page'] = $this->request->get('page');
		$condi['perpage'] = 20;

		// 总数
		$uda = new voa_uda_frontend_common_shop_list();
		$list = array();
		$uda->execute($condi, $list);

		// 分页显示
		$multi = '';
		// 管理员列表
		$total = $uda->get_total();

		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $uda->get_perpage(),
			'current_page' => $uda->get_page(),
			'show_total_items' => true,
		);
		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		// 格式化列表输出
		foreach ($list as &$_ca) {
			$this->_shop_format($_ca);
		}

		return array($total, $multi, $list);
	}

	protected function _delete_shop($csp_id) {

		$uda = new voa_uda_frontend_common_shop_get();
		$shop = array();
		$uda->execute(array('csp_id' => $csp_id), $shop);

		$data = array();
		$data['csp_status'] = voa_d_oa_common_shop::STATUS_DELETE;
		$data['csp_id'] = $csp_id;
		$data['csp_deleted'] = time();

		$this->_save_shop($data);
		$this->_delete_region($shop['cr_id']);
	}

	protected function _delete_region($cr_id) {

		$cr_id = (int)$cr_id;
		// 如果存在地区id 则下一步操作
		if (empty($cr_id)) {
			return true;
		}

		$dbshop = &service::factory('voa_s_oa_common_shop');
		$dbregion = &service::factory('voa_s_oa_common_region');
		// 获取大区id
		$region = $dbregion->get($cr_id);
		$cr_parent_id = $region['cr_parent_id'];

		// 如果没有查到有其它数据使用该小区
		if (!$dbshop->count_by_conds(array('cr_id'=>$cr_id))) {
			// 则标记删除
			$dbregion->delete($cr_id);
		}

		// 如果大区存在
		if ($cr_parent_id) {
			// 如果没有其它商店使用该大区
			if (!$dbshop->count_by_conds(array('cr_id'=>$cr_parent_id))) {
				// 如果没有其它小区使用该大区
				if (!$dbregion->count_by_conds(array('cr_parent_id'=>$cr_parent_id))) {
					// 则删除该大区
					$dbregion->delete($cr_parent_id);
				}
			}
		}
	}

	protected function _save_shop($data) {

		$dbregion = &service::factory('voa_s_oa_common_region');
		$dbshop = &service::factory('voa_s_oa_common_shop');

		if (!empty($data['cr_name_parent']) && !empty($data['cr_name'])) {
			$city = $data['cr_name_parent'];
			unset($data['cr_name_parent']);

			$area = $data['cr_name'];
			unset($data['cr_name']);

			$cityid = '';
			$region = $dbregion->get_by_conds(array('cr_name' => $city, 'cr_parent_id' => 0));
			if (!empty($region)) {
				$cityid = $region['cr_id'];
			} elseif(!empty($city)) {
				$region['cr_name'] = $city;
				$region['cr_status'] = 1;
				$region['cr_created'] = time();
				$region = $dbregion->insert($region);
				$cityid = $region['cr_id'];
			}

			$region = $dbregion->get_by_conds(array('cr_name' => $area, 'cr_parent_id' => $cityid));
			if (!empty($region)) {
				$data['cr_id'] = $region['cr_id'];
			} elseif(!empty($area)) {
				$region['cr_name'] = $area;
				$region['cr_parent_id'] = $cityid;
				$region['cr_status'] = 1;
				$region['cr_created'] = time();
				$region = $dbregion->insert($region);
				$data['cr_id'] = $region['cr_id'];
			}
		}

		if (!empty($data['csp_id'])) {
			$csp_id = $data['csp_id'];
			$old_shop = $dbshop->get($csp_id);
			// 如果小区id更改
			$old_region = $dbregion->get($old_shop['cr_id']);
			if ($old_shop['cr_id'] == $data['cr_id'] && $old_region['cr_parent_id'] != $cityid && !empty($area) && !empty($cityid)) {
				$region = array();
				$region['cr_name'] = $area;
				$region['cr_parent_id'] = $cityid;
				$region['cr_status'] = 1;
				$region['cr_created'] = time();
				$data['cr_id'] = $dbregion->insert($region);
			}

			unset($data['csp_id']);
			$dbshop->update($csp_id, $data);
			// 删除原来没用的大小区
			$this->_delete_region($old_shop['cr_id']);
		} else {
			$data['csp_status'] = 1;
			$data['csp_created'] = time();
			$dbshop->insert($data);
		}

	}

	protected function _excel_data($data, $area = false) {

		$init_fields = $this->_excel_fields;
		$field2colnum = array();//字段与excel列字母对应关系
		$titleString = array();//excel 标题栏文字
		$titleWidth = array();//excel 标题栏宽度
		$excelData = array();//excel 行数据
		$ord = 65;//第一列字母A的ASCII码值
		foreach ($init_fields AS $key => $arr) {
			$colCode = chr($ord);
			$field2colnum[$key] = $colCode;
			$titleString[$colCode] = $arr['name'];
			$titleWidth[$colCode] = $arr['width'];
			$ord++;
		}

		$i = 0;
		foreach ($data AS $row) {
			foreach ($field2colnum AS $k => $col) {
				$excelData[$i][$col] = isset($row[$k]) ? $row[$k] : '';
			}

			$i++;
		}

		return array($titleString, $titleWidth, $excelData);
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
	 * 提供给 Excel 模板使用的例子数据，注意字段需要与 voa_uda_frontend_addressbook_base->excel_fields 进行对应
	 * @see voa_uda_frontend_addressbook_base;
	 * @return array
	 */
	protected function _template_data() {

		return array (
			0 => array (
				'#'=>'#',
				'csp_name' => '七星路门店',
				'cr_name_parent' => '上海',
				'cr_name' => '普陀',
				'csp_address' => '七星路112号',
			),
		);
	}

	/**
	 * 获取上传的通讯录excel文件绝对路径
	 * @param string $file_var 上传控件名
	 * @param string $filepath <strong style="color:red">引用结果</strong>绝对路径
	 * @return boolean
	 */
	protected  function _get_uploadfile($file_var, &$filepath) {

		$upload = isset($_FILES[$file_var]) ? $_FILES[$file_var] : array();
		if (empty($upload) || !isset($upload['error'])) {
			$this->errmsg(1001, '对不起，请正确上传  Excel 文件。');
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
				$errMsg = '您只能上传大小不超过 '.$upload_max_filesize.' 的 Excel 文件';
				break;
			case 3:
				$errMsg = '文件上传失败，请返回重试。';
				break;
			case 4:
				$errMsg = '请上传 Excel 文件。';
				break;
			case 6:
				$errMsg = '服务器临时目录错误，上传失败。';
				break;
			case 7:
				$errMsg = '服务器文件写入错误，上传失败。';
				break;
			default:
				$errMsg = '上传文件发生未知错误。';
				break;
		}

		if ($errMsg) {
			$this->errmsg(1002, $errMsg);
			return false;
		}

		if (!is_readable($upload['tmp_name'])) {
			$this->errmsg(1003, '上传的文件读取失败，请返回重试。');
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
	protected  function _parse_excel_data($filepath, &$result) {

		if (!is_readable($filepath)) {
			$this->errmsg(1001, '上传的文件读取失败，请返回重试。');
			return false;
		}

		$data = file_get_contents($filepath, FALSE, NULL, 0, 8);
		if ($data != pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1)) {
			$this->errmsg(1002, '上传的文件不是标准的模板格式，请使用下载的模板');
			return false;
		}

		$error_reporting = error_reporting();
		error_reporting(0);
		$excel = new excel();
		$dataList = $excel->read_from_xsl($filepath, 0, array());
		error_reporting($error_reporting);

		if (empty($dataList)) {
			$this->errmsg(1003, '没有读取到可用的数据，请确认上传的 Excel 文件正确');
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

	/**
	 * 设置错误信息
	 * @param int $errno 错误代号
	 * @param string $error 错误详情
	 */
	public function errmsg($errno, $error = '') {

		$this->errno = (int)$errno;
		$this->error = (string)$error;
	}
}
