<?php

/**
 * voa_c_admincp_office_sign_list
 * 企业后台/微办公管理/考勤签到/地理位置记录列表
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_upposition extends voa_c_admincp_office_sign_base {
	// xls横坐标
	private $__letter = array(
		'A',
		'B',
		'C',
		'D',
		'E',
		'F',
		'G',
		'H',
		'I',
		'J',
		'K',
		'L',
		'M',
		'N',
		'O',
		'P',
		'Q',
		'R',
		'S',
		'T',
		'U',
		'V',
		'W',
		'X',
		'Y',
		'Z'
	);

	public function execute() {
		// 默认搜索数据
		$searchDefault = array(
			'm_username' => '',
			'signtime_min' => '',
			'signtime_max' => '',
			'cd_id' => ''
		);
		$issearch = $this->request->get('issearch');
		$perpage = 12;

		// 请求的是导出操作 else 获取列表数据
		if ($this->request->get('is_dump')) {
			$perpage = 10000;
			list($total, $multi, $searchBy, $list, $conditions) = $this->_search_sign_location($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
			$this->__dump_list($conditions);

			return true;
		} else {
			list($total, $multi, $searchBy, $list, $conditions) = $this->_search_sign_location($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
		}
		//默认部门
		if (! empty($searchBy['cd_id'])) {
			$dep_cache = $deplist = voa_h_cache::get_instance()->get('department', 'oa');
			$cd_name = $dep_cache[$searchBy['cd_id'][0]]['cd_name'];
			// 根据部门id取部门名称
			$searchBy['dep_default'] = array(array('id' => $searchBy['cd_id'][0], 'cd_name' => $cd_name, 'isChecked' => (bool)true));
		} else {
			$searchBy['dep_default'] = array();
		}
		$searchBy['dep_default'] = rjson_encode(array_values($searchBy['dep_default']));

		//获取该月第一天和最后一天日期
		list($begin_d, $end_d) = $this->get_m_day();
		
		$this->view->set('begin_d', $begin_d);
		$this->view->set('end_d', $end_d);
		$this->view->set('searchBy', $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('detailUrlBase', $this->cpurl($this->_module, $this->_operation, 'updetail', $this->_module_plugin_id, array('sl_id' => '')));

		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->output('office/sign/upposition');

	}

	/**
	 * 搜索签到记录
	 * @param       $cp_pluginid
	 * @param       $issearch
	 * @param array $searchDefaults
	 * @param int   $perpage
	 * @return array
	 */
	protected function _search_sign_location($cp_pluginid, $issearch, $searchDefaults = array(), $perpage = 10) {

		// 搜索条件
		$searchBy = array();
		$conditions = array();
		// 如果为搜索
		if ($issearch) {
			//查询条件
			foreach ($searchDefaults AS $_k => $_v) {
				if (isset($_GET[$_k]) && $this->request->get($_k) != $_v) {
					if ($this->request->get($_k) != null) {
						$searchBy[$_k] = $this->request->get($_k);
					} else {
						$searchBy[$_k] = $_v;
					}
				}
			}
			$searchBy = array_merge($searchDefaults, $searchBy);
		} else {
			$searchBy = $searchDefaults;
		}

		//组合搜索条件
		if (!empty($searchBy)) {
			$this->_add_condi($conditions, $searchBy);
		}

		$list = array();
		$serv_location = &service::factory('voa_s_oa_sign_location');
		// 获取条件总数
		$total = $serv_location->count_by_conds($conditions);
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			// 获取分页信息
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$orderby ['sl_signtime'] = 'DESC';
			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;

			$tmp = $serv_location->list_by_conds($conditions, $page_option, $orderby);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_sign_location($_data);
			}
			//获取部门
			$list = $this->get_dep($list);
			unset($tmp);
		}

		return array($total, $multi, array_merge($searchDefaults, $searchBy), $list, $conditions);
	}

	/**
	 *状态判断
	 * @param int conds
	 * @param array searchBy
	 */
	protected function _add_condi(&$conds, $searchBy) {
		if (!empty($searchBy['signtime_min'])) { //开始时间
			$conds['sl_signtime >= ?'] = rstrtotime($searchBy['signtime_min']);
		}
		if (!empty($searchBy['signtime_max'])) {//结束时间
			$conds['sl_signtime <= ?'] = rstrtotime($searchBy['signtime_max']) + 86400;
		}
		if (!empty($searchBy['m_username'])) {//姓名
			$conds["m_username like ?"] = "%" . $searchBy['m_username'] . "%";
		}
		if (!empty($searchBy['cd_id'])) {//部门
			//查该部门里的所有人
			$serv_mem_dep = &service::factory('voa_s_oa_member_department');
			$conditions['cd_id'] = $searchBy['cd_id'];
			$mem_list = $serv_mem_dep->fetch_all_by_conditions($conditions);
			$ids = array();
			if (! empty($mem_list)) {
				// 取所有人的id
				foreach ($mem_list as $_mem) {
					$ids[] = $_mem['m_uid'];
				}
			}
			$conds['m_uid IN (?)'] = $ids;
		}

	}

	/**
	 * 导出.xls文件
	 * @param $conditions
	 * @return bool
	 */
	private function __dump_list($conditions) {

		// 构造异常列表

		//$zip = new ZipArchive ();
		//	$path = voa_h_func::get_sitedir () . 'excel/';
		//$zipname = $path . 'sign' . date ( 'YmdHis', time () );
		//$zip->open ( $zipname . '.zip', ZipArchive::CREATE );

		// 汇总表需要的数据
		$serv = &service::factory('voa_s_oa_sign_location');

		$list = $serv->list_by_conds($conditions);
		if (!empty($list)) {
			$list = $this->get_dep($list);
			foreach ($list as &$val) {
				$val['_sl_signtime'] = rgmdate($val['sl_signtime'], 'Y-m-d H:i');
			}
		} else {
			$list = array();
		}

		$path_out = $this->get_out($list);

		//$zip->addFile ( $path_out, 'out.xls' );

		//$zip->close ();
		//$this->__put_header ( $zipname . '.zip' );
		//$this->__clear ( $path );
		return false;
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

	/**
	 * 考勤汇总表
	 * @param unknown $list
	 * @return string
	 */
	public function get_out($list) {
		$excel = new excel ();
		// Excel表格式,这里简略写了8列
		$letter = $this->__letter;
		// 表头数组
		$tableheader = array(
			'外勤记录表'
		);
		// 填充表头信息
		for ($i = 0; $i < count($tableheader); $i ++) {
			$excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
		}
		// 居中，加粗
		$excel->getActiveSheet()->getStyle('A1:K3')->applyFromArray(array(
			'font' => array(
				'bold' => true
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			)
		));
		// 设置宽度
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

		$i = 1;
		// 表格数组
		$data [0] = array(
			'统计日期:' . date('Y-m-d', time())
		);
		$data [1] = array(
			'序号',
			'姓名',
			'部门',
			'上报时间',
			'ip',
			'地址',

		);
		if (!empty($list)) {
			foreach ($list as $val) {

				$data [] = array(
					$i,
					$val['m_username'],
					$val['cd_name'],
					$val['_sl_signtime'],
					$val['sl_ip'],
					$val['sl_address'],
				);
				$i ++;


			}
		}

		// 填充表格信息
		for ($i = 2; $i <= count($data) + 1; $i ++) {
			$j = 0;
			foreach ($data [$i - 2] as $key => $value) {
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
				$j ++;
			}
		}
		// 合并
		$excel->getActiveSheet()->mergeCells('A1:k1');
		$excel->getActiveSheet()->mergeCells('A2:k2');

		// $excel->getActiveSheet()->unmergeCells('A1:F1'); // 拆分

		// 创建Excel输入对象
		$write = new PHPExcel_Writer_Excel5 ($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");;
		header('Content-Disposition:attachment;filename="out.xls"');
		header("Content-Transfer-Encoding:binary");

		$path = voa_h_func::get_sitedir() . 'excel/';

		// $write->save('php://output');
		//$write->save ( $path . "out.xls" );
		$write->save('php://output');

		//	$filepath = $path . 'out.xls';
		//return $filepath;
	}

	/**
	 * 匹配人员部门
	 * @param unknown $list
	 * @return unknown
	 */
	public function get_dep($list) {

		// 部门缓存
		$dep_cache = $deplist = voa_h_cache::get_instance()->get('department', 'oa');

		// 取出list里的m_uids
		$m_uids = array();
		foreach($list as $k => $v) {
			$m_uids[] = $v['m_uid'];
		}
		// 去重
		$m_uids = array_unique($m_uids);
		$mem_dep = $this->_get_all_member_department($m_uids);

		//人员部门匹配
		$cdnames = array();
		foreach ($mem_dep as &$_mem_dep) {
			foreach ($_mem_dep['cd_ids'] as $_v) {
				//临时存储部门id
				$cdnames [] = $dep_cache[$_v]['cd_name'];
			}
			//拼接部门
			$_mem_dep['cd_name'] = implode(',', $cdnames);
			unset($cdnames);
		}
		//关联部门
		foreach ($list as &$_val) {
			$_val['cd_name'] = $mem_dep[$_val['m_uid']]['cd_name'];
		}

		return $list;
	}
}
