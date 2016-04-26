<?php
/**
 * list.php
 * 云工作后台/系统设置/门店管理/门店列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_shop_list extends voa_c_admincp_system_shop_base {

	/** 门店搜索条件字段 */
	private $__search_by_field = array(
		'placeregionid' => 0,
		'name' => '',
		'uid' => '',
	);

	public function execute() {

		// 每页显示数
		$limit = 15;
		// 是否为搜索
		$is_search = $this->request->get('is_search');
		// 请求参数
		$request = array(
			'placetypeid' => $this->_placetypeid
		);
		// 请求的搜索条件
		if ($is_search) {
			$this->__get_search_by($request);
		}
		// 读取列表结果
		$result = array();
		if (!$this->__get_list($limit, $request, $result)) {
			return false;
		}
		// 分页链接
		$multi = '';
		if (!empty($result['option']['total'])) {
			$pager_options = array(
				'total_items' => $result['option']['total'],
				'per_page' => $result['option']['limit'],
				'current_page' => $result['option']['page'],
				'show_total_items' => true,
			);
			$multi = pager::make_links($pager_options);
			unset($pager_options);
		}
		// 整理列表
		$this->__format_result_list($result);
		// 结果列表
		$list = $result['result'];
		// 初始化搜索条件
		$search_by = array();
		$this->__output_search_by($result['fields'], $search_by);

		// 是否搜索
		$this->view->set('is_search', $is_search);
		// 分页链接
		$this->view->set('multi', $multi);
		// 结果总数
		$this->view->set('total', $result['option']['total']);
		// 结果列表
		$this->view->set('list', $list);
		// 搜索条件初始化（当前搜索条件）
		$this->view->set('search_by', $search_by);
		// 列表首页url
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation
				, $this->_subop, $this->_module_plugin_id));
		// 搜索页url
		$this->view->set('form_search_url', $this->cpurl($this->_module, $this->_operation
				, $this->_subop, $this->_module_plugin_id));
		// 删除门店基本url
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation
				, 'delete', $this->_module_plugin_id, array('placeid' => '')));
		// 批量删除门店form url
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation
				, 'delete', $this->_module_plugin_id));
		// 编辑门店基本url
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation
				, 'edit', $this->_module_plugin_id, array('placeid' => '')));

		$this->output('system/shop/list');
	}

	/**
	 * 整理结果列表
	 * @param array $result (引用结果)
	 * $result['result']
	 * + 门店相关字段
	 * + _placeregion 所在分区列表
	 * + _placemember_master 负责人列表
	 * + _placemember_others 相关人列表
	 * @return void
	 */
	private function __format_result_list(&$result) {

		foreach ($result['result'] as &$_place) {

			// 当前场所id
			$_placeid = $_place['placeid'];

			// 场所所在分区名
			$_place['_placeregion'] = array();
			// 场所负责人
			$_place['_placemember_master'] = array();
			// 场所相关人
			$_place['_placemember_others'] = array();
			// 提取场所所在分区
			if (isset($result['placeregion'][$_placeid])) {
				$_place_region = $result['placeregion'][$_placeid];
				// 按分区级别排序
				sort($_place_region);
				foreach ($_place_region as $_region) {
					$_place['_placeregion'][] = $_region['name'];
				}
				unset($_place_region, $_region);
			}
			// 提取场所负责人
			if (isset($result['placemember'][$_placeid][voa_d_oa_common_place_member::LEVEL_CHARGE])) {
				$_uids = array_keys($result['placemember'][$_placeid][voa_d_oa_common_place_member::LEVEL_CHARGE]);
				$this->__get_place_member($_uids, $_place['_placemember_master']);
				unset($_uids);
			}
			// 提取场所相关人
			if (isset($result['placemember'][$_placeid][voa_d_oa_common_place_member::LEVEL_NORMAL])) {
				$_uids = array_keys($result['placemember'][$_placeid][voa_d_oa_common_place_member::LEVEL_NORMAL]);
				$this->__get_place_member($_uids, $_place['_placemember_others']);
				unset($_uids);
			}

			unset($_placeid);
		}
		unset($_place);
	}

	/**
	 * 提取请求的搜索条件
	 * @param array $request (引用结果)
	 * @return boolean
	 */
	private function __get_search_by(&$request) {

		$_name = (string)$this->request->get('name');
		$_address = (string)$this->request->get('address');
		$_uid = (int)$this->request->get('uid');
		$_placeregionid = (int)$this->request->get('placeregionid');
		if ($_name) {
			$request['name'] = $_name;
		}
		if ($_address) {
			$request['address'] = $_address;
		}
		if ($_uid) {
			$request['uid'] = array($_uid);
		}
		if ($_placeregionid) {
			$request['placeregionid'] = $_placeregionid;
		}
		unset($_name, $_address, $_uid, $_placeregionid);

		return true;
	}

	/**
	 * 获取门店列表
	 * @param number $limit 每页展示数
	 * @param array $request 搜索参数
	 * @param array $result (引用结果)门店列表
	 * @return boolean
	 */
	private function __get_list($limit, $request, &$result) {

		// 载入uda
		$uda = &uda::factory('voa_uda_frontend_common_place_list');
		// 内部配置信息
		$options = array(
			'page' => (int)$this->request->get('page'),
			'limit' => $limit,
			'from_db' => 1
		);

		// 读取列表
		try {
			$list = $uda->doit($request, $result, $options);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
			return false;
		}

		return true;
	}

	/**
	 * 输出搜索条件
	 * @param array $fields 列表结果集内的搜索条件
	 * @param array $search_by (引用结果)输出的搜索条件
	 * @return boolean
	 */
	private function __output_search_by($fields, &$search_by) {

		// 搜索条件
		$search_by = $fields;
		// 选人组件需要用到的参数初始化数据
		$search_by['_user_selector'] = array();
		// 存在用户uid，则初始化给前端选人组件
		if (!empty($search_by['uid'])) {

			// 通过uid找到用户列表
			$uda_member = new voa_uda_frontend_member_getuserlist();
			$user_list = array();
			try {
				$uda_member->doit(array('uids' => $search_by['uid']), $user_list);
			} catch (help_exception $h) {
				// 无关的错误信息，不需要接收以及记录
			} catch (Exception $e) {
				// 无关的错误信息，不需要接收以及记录
				logger::error($e);
			}

			// 构造搜索表单选人组件初始化值
			foreach ($search_by['uid'] as $_uid) {
				if (!isset($user_list[$_uid])) {
					continue;
				}
				$_user = $user_list[$_uid];
				$search_by['_user_selector'][] = array(
					'id' => $_uid,
					'name' => $_user['m_username'],
					'input_name' => 'uid'
				);
			}
		}
		$search_by['_user_selector'] = rjson_encode($search_by['_user_selector']);

		// 为未定义的搜索条件设置默认值
		foreach ($this->__search_by_field as $_k => $_v) {
			if (!isset($search_by[$_k])) {
				$search_by[$_k] = $_v;
			}
		}
		unset($_k, $_v);

		// 搜索了门店区域，则提取相关区域家谱，以利于前端输出
		$search_by['_init_placeregionid'] = array();
		if ($search_by['placeregionid']) {
			$uda_region_get = &uda::factory('voa_uda_frontend_common_place_region_get');
			try {

			} catch (help_exception $h) {
				// 无关结果，忽略报错
				//$this->_admincp_error_message($h);
			} catch (Exception $e) {
				// 无关结果，忽略报错
				logger::error($e);
				//$this->_admincp_system_message($e);
			}
		}

		return true;
	}

	/**
	 * 获取指定uid的用户信息
	 * @param array $uids
	 * @param array $user_list
	 * @return boolean
	 */
	private function __get_place_member(array $uids, array &$user_list) {

		static $uda_getuserlist;
		if (empty($uda_getuserlist)) {
			$uda_getuserlist = &uda::factory('voa_uda_frontend_member_getuserlist');
		}

		if (empty($uids)) {
			$user_list = array();
			return true;
		}

		// 待格式输出的数据
		$user_list = array();
		// 原始数据
		$result = array();
		// 请求uda的参数
		$request = array(
			'uids' => $uids
		);
		try {
			$uda_getuserlist->doit($request, $result);
		} catch (help_exception $h) {
			// 无关结果，不抛出错误
			//$this->_admincp_error_message($h);
		} catch (Exception $e) {
			// 无关结果，不抛出错误
			logger::error($e);
			//$this->_admincp_system_message($e);
		}

		// 遍历以提取用户名
		foreach ($result as $_m) {
			$user_list[] = $_m['m_username'];
		}
		unset($_m);

		return true;
	}

}
