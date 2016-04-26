<?php
/**
 * voa_c_admincp_office_askfor_list
 * 企业后台 - 审批流 - 列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_list extends voa_c_admincp_office_askfor_base {

	const ASKING = 1; // 审批中
	const ASKPASS = 2; // 审核通过
	const TURNASK = 3; // 转审批
	const ASKFAIL = 4; // 审批不通过
	const COPYASK = 5; // 抄送
	const PRESSASK = 6; // 催办
	const CENCEL = 7; // 已撤销

	const DRAFT = 5; // 草稿

	public function execute() {

		/** 搜索默认值 */
		$searchDefaults = array(
			'm_uid' => 0,
			'm_username' =>'',
			'cd_id' => -1,
			'af_subject' => '',
			'af_status' => '-1',
			'aft_id' => -1,
			'type' => -1,
			'begin' => '',
			'end' => '',
		);
		$issearch = $this->request->get('issearch') ? 1 : 0;
		$isdownload = $this->request->get('isdownload') ? 1 : 0;
		$searchBy = array();
		foreach ( $searchDefaults AS $_k => $_v ) {
			if ( isset($_GET[$_k]) && $this->request->get($_k) != $_v ) {
				$searchBy[$_k] = $this->request->get($_k);
			}
		}
		$perpage = 12;
		list($total, $multi, $askforList) = $this->_askfor_search($issearch, $isdownload, $searchDefaults, $searchBy, $perpage);

		/** 获取所有启用的审批流程  **/
		$templates = $this->_service_single('askfor_template', $this->_module_plugin_id, 'fetch_all_for_is_use');

		$this->view->set('askforList', $askforList);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('templates', $templates);
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('af_id' => '')));
		$this->view->set('viewBaseUrl', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('af_id' => '')));
		$this->view->set('formActionUrl', '/Askfor/Apicp/Askfor/Export');
		$this->view->set('departmentList', $this->_department_list);
		$this->view->set('askforStatusDescriptions', $this->_askfor_status_descriptions);
		$this->view->set('searchBy', array_merge($searchDefaults, $searchBy));
		$this->view->set('issearch', $issearch);
		$this->view->set('searchByUidBaseUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('issearch'=>1, 'm_uid'=>'')));
		$this->view->set('searchByDepartmentBaseUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('issearch'=>1, 'cd_id'=>'')));
		$this->view->set('searchByStatusBaseUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('issearch'=>1, 'af_status'=>'')));

		$this->output('office/askfor/new_list');

	}

	/**
	 * 搜索审批流
	 * @param number $issearch
	 * @param array $searchDefaults
	 * @param array $searchBy
	 * @param number $perpage
	 * @return array(total, multi, list)
	 */
	protected function _askfor_search($issearch = 0, $isdownload = 0, $searchDefaults = array(), $searchBy = array(), $perpage = 12) {

		/** 搜索条件 */
		$conditions = array('af.af_status<' => voa_d_oa_askfor::STATUS_REMOVE);
		if ($issearch || $isdownload) {
			foreach ( $searchDefaults AS $_k => $_v ) {
				if ( isset($searchBy[$_k]) && $_v != $searchBy[$_k] ) {
					$v = $searchBy[$_k];

					/** 检查数据合法性 */
					if ( $_k == 'af_status' ) {
						//检查状态
						if ( isset($this->_askfor_status_descriptions[$v]) ) {
							$conditions[$_k] = $v;
						}
					} elseif ( $_k == 'cd_id' ) {
						//检查部门
						if ( isset($this->_department_list[$v]) ) {
							$conditions[$_k] = $v;
						}
					} elseif( $_k == 'begin'){
							$conditions['created > ?'] = rstrtotime($v);
					}elseif($_k == 'end'){
							$conditions['created < ?'] = rstrtotime($v);
					}elseif($_k == 'aft_id'){
						$conditions[$_k] = $v;
					}
				}
			}
		}

		$list = array();

//		导出 / 搜索
		if ($isdownload) {

			$this->__output($conditions);

		} else {
			$total = $this->_service_single('askfor', $this->_module_plugin_id, 'count_all_by_condition', $conditions);
			$multi = '';
			if ( $total > 0 ) {
				$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
				);
				$multi = pager::make_links($pagerOptions);
				pager::resolve_options($pagerOptions);
				$tmp = $this->_service_single('askfor', $this->_module_plugin_id, 'fetch_all_by_condition', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);

				foreach ( $tmp AS $_af_id => $_af ) {
					$list[$_af_id] = $this->_askfor_format($_af);
				}
				unset($tmp);
			}
		}

		return array($total, $multi, $list);

	}

	/**
	 * 导出
	 * @param $conditions 条件
	 * @return bool
	 * @throws controller_exception
	 */
	private function __output($conditions) {

		// 查询条数
		$total = $this->_service_single('askfor', $this->_module_plugin_id, 'count_all_by_condition', $conditions);
		if ($total == 0) {
			$this->_error_message('没有可以导出的数据');
		}
		// 初始化 压缩
		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir() . 'excel/';
		$zipname = $path . 'enterprise' . date('YmdHis', time());
		// 单个文件的数据条数
		$limit = 1000;
		$serv_customdata = &service::factory('voa_s_oa_askfor_customdata');
		$serv_proc = &service::factory('voa_s_oa_askfor_proc');

		if (!file_exists($zipname)) {
			$zip->open($zipname . '.zip', ZipArchive::CREATE);

			// 页数
			$times = ceil($total/$limit);

			// 根据页数进行导出 就多少个csv文件
			for ($i = 1; $i <= $times; $i ++) {
				$result = null;
				// 分页查询
				$pagerOptions = array(
					'per_page' => $limit,
					'current_page' => $i,
				);
				pager::resolve_options($pagerOptions);

				// 获取数据
				$list = $this->_service_single('askfor', $this->_module_plugin_id, 'fetch_all_by_condition', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);

				$af_ids = array_column($list, 'af_id');
				// 查询自定义字段数据
				$custom_data = $serv_customdata->fetch_by_af_id($af_ids);
				// 合并 自定义字段数据
				foreach ($list as &$val) {
					foreach ($custom_data as $_val) {
						if ($val['af_id'] == $_val['af_id']) {
							$val['custom'][] = $_val;
							continue;
						}
					}
				}

				// 查询 审批进度表
				$proc_data = $serv_proc->fetch_by_af_ids($af_ids);
				foreach ($list as &$val) {
					foreach ($proc_data as $_val) {
						// 如果是审批
						if ($val['af_id'] == $_val['af_id'] && in_array($_val['afp_condition'], array(self::ASKING, self::ASKPASS, self::TURNASK, self::ASKFAIL))) {
							$val['approvers'][] = $_val;
							continue;
						// 如果是抄送人
						} elseif ($val['af_id'] == $_val['af_id'] && $_val['afp_condition'] == self::COPYASK) {
							$val['copy'][] = $_val;
						}
					}
				}

				// 生成csv文件
				$result = $this->__create_csv($list, $i, $path);
				if ($result) {
					$zip->addFile($result, $i . '.csv');
				}
			}
			$zip->close();
			$this->__put_header($zipname . '.zip');
			$this->__clear($path);

			return false;
		}

	}

	/**
	 * 生成csv文件
	 */
	private function __create_csv($list, $i, $path) {

		if (!is_dir($path)) {
			mkdir($path, '0777');
		}
		$data = array();

		$filename = $i . '.csv';
		$data[0] = array(
			'申请人',
			'申请时间',
			'审批状态',
			'审批标题(固定流程名称)',
			'自定义字段',
			'抄送人',
			'审批内容',
			'审批人',
		);

		foreach ($list as $val) {
			switch ($val['af_condition']) {
				case (self::ASKING) :
					$af_condition = '审批申请中';
					break;
				case (self::ASKPASS) :
					$af_condition = '审批通过';
					break;
				case (self::TURNASK) :
					$af_condition = '转审批';
					break;
				case (self::ASKFAIL) :
					$af_condition = '审批不通过';
					break;
				case (self::DRAFT) :
					$af_condition = '草稿';
					break;
				case (self::PRESSASK) :
					$af_condition = '已催办';
					break;
				case (self::CENCEL) :
					$af_condition = '撤销';
					break;
				default :
					$af_condition = '未知';
					break;
			}

			// 整合自定义字段数据
			$customdata = '';
			if (isset($val['custom']) && !empty($val['custom'])) {
				foreach ($val['custom'] as $v) {
					$customdata .= $v['name'] . ':' . $v['value'] . '  ';
				}
			}

			// 抄送人数据整合
			$copy = '';
			if (isset($val['copy']) && !empty($val['copy'])) {
				foreach ($val['copy'] as $v) {
					$copy .= $v['m_username'] . '  ';
				}
			}

			// 审批人数据整合
			$approvers = '';
			if (isset($val['approvers']) && !empty($val['approvers'])) {
				// 自由流程 / 固定流程
				if ($val['aft_id'] == 0) {
					foreach ($val['approvers'] as $v) {
						// 匹配审批状态
						switch ($v['afp_condition']) {
							case (self::ASKING) :
								$afp_condition = '审批申请中';
								break;
							case (self::ASKPASS) :
								$afp_condition = '审批通过';
								break;
							case (self::TURNASK) :
								$afp_condition = '转审批';
								break;
							case (self::ASKFAIL) :
								$afp_condition = '审批不通过';
								break;
							default :
								$afp_condition = '未知';
								break;
						}
						// 审批人数据
						$approvers .= $v['m_username'] . $afp_condition;
					}
				} else {
					$by_level = array();
					// 把审批人 按 级数 分类
					foreach ($val['approvers'] as $v) {
						$by_level[$v['afp_level']][] = $v;
					}
					// 审批人数据整合
					foreach ($by_level as $k => $v) {
						$approvers .= '第' . $k . '级审批人 : ';
						foreach ($v as $key => $val) {
							// 匹配审批状态
							switch ($val['afp_condition']) {
								case (self::ASKING) :
									$afp_condition = '审批申请中';
									break;
								case (self::ASKPASS) :
									$afp_condition = '审批通过';
									break;
								case (self::TURNASK) :
									$afp_condition = '转审批';
									break;
								case (self::ASKFAIL) :
									$afp_condition = '审批不通过';
									break;
								default :
									$afp_condition = '未知';
									break;
							}
							$approvers .= $val['m_username'] . $af_condition . '  ';
						}
					}
				}
			}

			$temp = array(
				'm_username' => $val['m_username'],
				'af_created' => !empty($val['af_created']) ? rgmdate($val['af_created'], 'Y-m-d H:i') : '',
				'af_condition' => $af_condition,
				'af_subject' => !empty($val['af_subject']) ? str_replace(PHP_EOL, '', $val['af_subject']) : '', // 去掉换行
				'customdata' => str_replace(PHP_EOL, '', $customdata),
				'copy' => $copy,
				'af_message' => !empty($val['af_message']) ? str_replace(PHP_EOL, '', $val['af_message']) : '',
				'approvers' => $approvers
			);

			$data[] = $temp;
		}

		$csv_data = array2csv($data);
		$fp = fopen($path . $filename, 'w');
		fwrite($fp, $csv_data); // 写入数据
		fclose($fp); // 关闭文件句柄

		return $path . $filename;
	}

	/**
	 * 下载输出至浏览器
	 */
	private function __put_header($zipname) {

		if (!file_exists($zipname)) {
			exit("下载失败");
		}
		$file = fopen($zipname, "r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . filesize($zipname));
		Header("Content-Disposition: attachment; filename=" . basename($zipname));
		echo fread($file, filesize($zipname));
		$buffer = 1024;
		while (!feof($file)) {
			$file_data = fread($file, $buffer);
			echo $file_data;
		}
		fclose($file);
	}

	/**
	 * 清理产生的临时文件
	 */
	private function __clear($path) {

		$dh = opendir($path);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				unlink($path . $file);
			}
		}
	}

}
