<?php
/**
 * voa_c_admincp_office_sign_list
 * 企业后台/微办公管理/考勤签到/签到记录列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
// 如果是导出操作则不自动加入header头
define('NO_AUTO_HEADER', isset($_GET['is_dump']));

class voa_c_admincp_office_sign_blist extends voa_c_admincp_office_sign_base {

	public function execute() {

//		$searchDefault = array('name' => '', 'address' => '');
//		$issearch = $this->request->get('issearch');
//		$perpage = 15;
//
//		// 如果请求的是导出操作
//		if ($this->request->get('is_dump')) {
//			$perpage = 10000;
//			list($total, $multi, $searchBy, $list) = $this->_search_sign_record($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
//			$this->__dump_list($list);
//		} else {
//			list($total, $multi, $searchBy, $list) = $this->_search_sign_record($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
//		}
//
//		$this->view->set('signStatus', $this->_sign_status);
//		$this->view->set('signType', $this->_sign_type);
//		$this->view->set('signStatusSet', $this->_sign_status_set);
//		$this->view->set('signTypeSet', $this->_sign_type_set);
//		$this->view->set('searchBy', $searchBy);
//		$this->view->set('issearch', $issearch);
//		$this->view->set('multi', $multi);
//		$this->view->set('list', $list);
//		$this->view->set('total', $total);
//		$this->view->set('add_url_base', $this->cpurl($this->_module, $this->_operation, 'badd', $this->_module_plugin_id));
//		$this->view->set('timestamp', startup_env::get('timestamp'));
//		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'badd', $this->_module_plugin_id, array('sbid' => '')));
//		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'bdelete', $this->_module_plugin_id, array('sbid' => '')));
//		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'bdelete', $this->_module_plugin_id, array('sbid' => array())));
//		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
//		$this->view->set('detailUrlBase', $this->cpurl($this->_module, $this->_operation, 'detail', $this->_module_plugin_id, array('sr_id' => '')));
		
		$this->output('office/sign/blist');
	}

	/**
	 * 搜索签到记录
	 * 
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_sign_record($cp_pluginid, $issearch, $searchDefault = array(), $perpage = 10) {

		/**
		 * 搜索条件
		 */
		$searchBy = array();
		$conditions = array();
		if ($issearch) {
			// 查询条件
			foreach ($searchDefault as $_k => $_v) {
				if (isset($_GET[$_k]) && $this->request->get($_k) != $_v) {
					if ($this->request->get($_k) != null) {
						$searchBy[$_k] = $this->request->get($_k);
					} else {
						$searchBy[$_k] = $_v;
					}
				}
			}
			$searchBy = array_merge($searchDefault, $searchBy);
		} else {
			$searchBy = $searchDefault;
		}
		if (! empty($searchBy['name'])) { // 班次名称
			$conditions["name like ?"] = "%" . $searchBy['name'] . "%";
		}
		if (! empty($searchBy['address'])) { // 地址
			$conditions["address like ?"] = "%" . $searchBy['address'] . "%";
		}
		
		$list = array();
		$serv = &service::factory('voa_s_oa_sign_batch');
		// 获取分页信息
		$total = $serv->count_by_conds($conditions);
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array('total_items' => $total, 'per_page' => $perpage, 'current_page' => $this->request->get('page'), 'show_total_items' => true);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$orderby['work_begin'] = 'ASC';
			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$tmp = $serv->list_by_conds($conditions, $page_option, $orderby);
			$list = array();
			if (! empty($tmp)) {
				$this->_format($tmp, $list);
			}
		}
		
		return array($total, $multi, array_merge($searchDefault, $searchBy), $list);
	}

	/**
	 * 格式化
	 * 
	 * @param $in
	 * @param $list
	 */
	public function _format($in, &$list) {

		$dep = array();
		$this->_getdepartment($in, $dep);
		
		// 判断是否是全公司
		$serv_de = &service::factory('voa_s_oa_common_department');
		
		if (! empty($dep)) {
			foreach ($dep as &$val) {
				
				$val['work_days'] = unserialize($val['work_days']);
				foreach ($val['work_days'] as &$v) {
					if ($v == 1) {
						$v = '一';
					} elseif ($v == 2) {
						$v = '二';
					} elseif ($v == 3) {
						$v = '三';
					} elseif ($v == 4) {
						$v = '四';
					} elseif ($v == 5) {
						$v = '五';
					} elseif ($v == 6) {
						$v = '六';
					} else {
						$v = '日';
					}
					$v = '周' . $v;
				}
				$val['_work_days'] = implode('、', $val['work_days']);
				$val['work_begin'] = $this->formatnum($val['work_begin']);
				$val['work_end'] = $this->formatnum($val['work_end']);
				$val['work_begin'] = substr($val['work_begin'], 0, 2) . ':' . substr($val['work_begin'], 2, 2);
				$end_start = substr($val['work_end'], 0, 2);
				
				if ($end_start >= 24) {
					$end_start = '次日' . ($end_start - 24);
				}
				$val['work_end'] = $end_start . ':' . substr($val['work_end'], 2, 2);
				$val['start_begin'] = date('Y-m-d', $val['start_begin']);
				$val['start_end'] = empty($val['start_end']) ? '' : date('Y-m-d', $val['start_end']);
				if (! empty($val['_department'])) {
					$val['_department'] = implode('、', $val['_department']);
				}
			}
		} else {
			$dep = array();
		}
		
		$list = $dep;
	}

	/**
	 * 导出CSV文件
	 * 
	 * @param array $list
	 */
	private function __dump_list(array $list) {
		
		// 待输出的数据，数组格式
		$data = array();
		// 标题栏 - 字段名称
		$data[] = array('username' => '签到人', 'type' => '签到类型', 'status' => '签到状态', 'signtime' => '签到时间', 'ip' => 'IP 地址', 'address' => '地理位置');
		// 遍历数据每行一条
		foreach ($list as $_row) {
			$data[] = array('username' => $_row['m_username'], 'type' => $_row['_type'], 'status' => $_row['_status'], 'signtime' => $_row['_signtime'], 'ip' => $_row['sr_ip'], 'address' => $_row['sr_address']);
		}
		
		// 转换为csv字符串
		$csv_data = array2csv($data);
		
		$filename = 'sign_' . rgmdate(startup_env::get('timestamp'), 'YmdHis') . '.csv';
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: text/csv");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Coentent_Length: ' . strlen($csv_data));
		echo $csv_data;
		
		exit();
	}

	/**
	 * 获取部门名称
	 * 
	 * @param $in 班次数据
	 * @param $out
	 */
	public function _getdepartment($in, &$out) {

		$dep = voa_h_cache::get_instance()->get('department', 'oa'); // 部门信息
		$dep_batch = &service::factory('voa_s_oa_sign_department');
		
		$dep_list = $dep_batch->list_all(); // 部门班次关联信息
		
		/*
		 * 循环$in （班次设置）$dep_list （部门班次关联数据）
		 * 获取部门名称
		 */
		foreach ($in as $_key => &$_val) {
			foreach ($dep_list as $_dep) {
				
				if ($_dep['sbid'] == $_val['sbid']) {
					//班次部门表里的id等于部门id
					$_val['department'][] = $_dep['department'];
					foreach ($_val['department'] as $_k => &$_d) {
						if (! empty($dep[$_d]['cd_name'])) {
							$_val['_department'][] = $dep[$_d]['cd_name'];
							$_val['_department'] = array_unique($_val['_department']);
						}
					}
				}
			}
		}
		
		$out = $in;
	}

}
