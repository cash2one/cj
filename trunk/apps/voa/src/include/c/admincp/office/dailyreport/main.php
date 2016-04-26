<?php
/**
 * voa_c_admincp_office_dailyreport_list
 * 企业后台/微办公管理/日报/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_dailyreport_main extends voa_c_admincp_office_dailyreport_base {

	public function execute() {
		$this->output('office/dailyreport/dailyreport_main');
	}

	/**
	 * 搜索报告记录
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_dailyreport($cp_pluginid, $issearch, $searchDefault = array(), $perpage = 10) {

		/**
		 * 搜索条件
		 */
		$conditions = array();
		/**
		 * 搜索字段
		 */
		$searchBy = array();
		/**
		 * 如果为搜索
		 */
		if ($issearch) {
			foreach ($searchDefault as $_k => $_v) {
				if (!isset($_GET[$_k])) {
					continue;
				}
				$v = $this->request->get($_k);
				if ($_v == $v || !is_scalar($v)) {
					continue;
				}
				$v = trim($v);
				$searchBy[$_k] = $v;

				if (strpos($_k, 'cab_realname_') === 0) {
					// 真实姓名字段
					if (!$v) {
						continue;
					}

					if ($_k == 'cab_realname_author') {
						// 搜索报告发送者
						$conditions['m_username'] = array(
							'%' . addcslashes($v, '%_') . '%',
							'like'
						);
					} elseif ($_k == 'cab_realname_receive') {
						// 搜索报告接收者

						// 不搜索全部，默认只搜索90天内的
						$time_after = startup_env::get('timestamp') - 86400 * 90;

						$find_mem_conditions = array();
						$find_mem_conditions['m_username'] = array(
							'%' . addcslashes($v, '%_') . '%',
							'like'
						);
						$find_mem_conditions['drm_status'] = array(voa_d_oa_dailyreport_mem::STATUS_REMOVE, '<');
						if (isset($_GET['begintime']) && (validator::is_date($_begin = $this->request->get('begintime')))) {
							// 指定了提交时间，则以该时间为起点
							$find_mem_conditions['drm_updated'] = array(
								rstrtotime($_begin),
								'>='
							);
						}
						if (isset($_GET['endtime']) && (validator::is_date($_end = $this->request->get('endtime')))) {
							// 指定了结束时间，则以时间为结束
							$find_mem_conditions['drm_created'] = array(
								rstrtotime($_end) + 86400,
								'<'
							);
						}

						// 根据报告接收者找到报告id
						$mem_list = $this->_service_single('dailyreport_mem', $cp_pluginid, 'fetch_by_conditions', $find_mem_conditions);
						if (empty($mem_list)) {
							$conditions['dr_id'] = 0;
						} else {
							$conditions['dr_id'] = array();
							//$conditions['dr_id'][0] = 0;
							$tmp_dr_ids = array(0);
							foreach ($mem_list as $_mem) {
								//$conditions['dr_id'][$_mem['dr_id']] = $_mem['dr_id'];
								$tmp_dr_ids[$_mem['dr_id']] = $_mem['dr_id'];
							}

							$conditions['dr_id'] = array($tmp_dr_ids);
						}
					}
				} elseif ($_k == 'begintime' || $_k == 'endtime') {
					// 搜索时间范围
					if ($v && validator::is_date($v)) {
						$_v_time = rstrtotime($v);
						if ($_k == 'endtime') {
							$_v_time = $_v_time + 86400;
							$conditions['dr_created'] = array(
								$_v_time,
								'<'
							);
						} else {
							$conditions['dr_updated'] = array(
								$_v_time,
								'>='
							);
						}
					}
				} elseif ($_k == 'dr_subject') {
					// 搜索标题
					if ($v) {
						$conditions['dr_subject'] = array(
							'%' . addcslashes($v, '%_') . '%',
							'like'
						);
					}
				} elseif ($_k == 'dr_type') {
					// 搜索日报类型
					if ($v) {
						if ($v != 0) {
							$conditions['dr_type'] = $v;
						}
					}
				} else {
					$conditions[$_k] = $v;
				}
			}
		}

		$list = array();
		$total = $this->_service_single('dailyreport', $cp_pluginid, 'count_by_conditions', $conditions);
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$list = $this->_service_single('dailyreport', $cp_pluginid, 'fetch_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			$m_uids = array();
			foreach ($list as $_data) {
				if (!isset($m_uids[$_data['m_uid']])) {
					$m_uids[$_data['m_uid']] = $_data['m_uid'];
				}
			}
			$users = voa_h_user::get_multi($m_uids);
			$uda = &uda::factory('voa_uda_frontend_dailyreport_format');
			foreach ($list as &$_data) {
				$uda->format($_data, isset($users[$_data['m_uid']]) ? $users[$_data['m_uid']] : array());
			}
			unset($_data);
		}

		return array(
			$total,
			$multi,
			array_merge($searchDefault, $searchBy),
			$list
		);
	}

	/**
	 * 导出CSV文件
	 * @param array $list
	 */
	private function __dump_list(array $list) {

		// 待输出的数据，数组格式
		$data = array();
		// 标题栏 - 字段名称
		$data[] = array(
			'realname' => '提交人',
			'department' => '部门',
			'subject' => '标题',
			'reporttime' => '提交时间'
		);
		// 遍历数据每行一条
		foreach ($list as $_row) {
			$data[] = array(
				'realname' => $_row['_realname'],
				'department' => $_row['_department'],
				'subject' => $_row['dr_subject'],
				'reporttime' => $_row['_created']
			)
			;
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
}
