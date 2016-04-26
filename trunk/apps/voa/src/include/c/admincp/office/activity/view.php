<?php

/**
 * voa_c_admincp_office_askfor_view
 * 企业后台 - 审批流 - 详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_activity_view extends voa_c_admincp_office_activity_base {

	public function execute() {
		//获取数据
		$serv = &service::factory('voa_s_oa_activity');
		$acid = $this->request->get('acid');
		if (empty($acid)) {
			$this->message('error', '没有获取到 ' . $this->_module_plugin['cp_name'] . ' 详情数据');
		}
		$activity = array();
		$activity = $serv->get($acid);
		if (empty($activity)) {
			$this->message('error', '没有获取到 ' . $this->_module_plugin['cp_name'] . ' 详情数据');
		}
		$activity['content'] = nl2br($activity['content']);
		$serv_invite = &service::factory('voa_s_oa_activity_invite');

		//yanzhipeng
		$invite = $serv_invite->list_by_conds(array('acid = ?' => $acid));
		$users = null;
		if ($invite) {
			$ddp = array();
			foreach ($invite as $vs) {
				if ($vs['type'] == 1) { //部门
					if (isset($vs['primary_id']) && $vs['primary_id'] == -1) {
						$users .= "<button class='btn btn-primary bnt-sm'>全公司</button>&nbsp;&nbsp;";
					} else {
						$department = voa_h_department::get($vs['primary_id']);
						//可能会出现重复，进行数组过滤
						$ddp[$department['cd_name']] = $department['cd_name'];
					}
				}
				if (isset($vs['type']) && $vs['type'] == 2) { //人员
					$user = voa_h_user::get($vs['primary_id']);
					$users.= "<button class='btn btn-info btn-sm'>". $user['m_username']."(个人)</button>&nbsp;&nbsp;";
				}
			}
			//加上部门的
			if($ddp){
				foreach ($ddp as $k => $v) {
					$users .= "<button class='btn btn-primary btn-sm'>" . $v . "(部门)</button>&nbsp;&nbsp;";
				}
			}
			$activity['usernames'] = $users;
		}
		$activity['invite'] = $invite;

		//判断导出内部报名
		if (($export = $this->request->get('export')) &&
			$this->request->get('export') == 'inner') {

			$list = $this->_partake_search($acid, null);
			if(!empty($list)){
				foreach ($list as $k => $v) {
					$list[$k]['created'] = rgmdate($list[$k]['created']);
				}
			}
			$this->__export($export, $list, $activity);
			return false;
		}
		//判断导出外部报名
		if (($export = $this->request->get('export')) &&
			$this->request->get('export') == 'outer') {

			$ex_list = $this->_ex_outsider_search($acid, null);
			if(!empty($ex_list)){
				foreach ($ex_list as $k => $v) {
					$ex_list[$k]['created'] = rgmdate($ex_list[$k]['created']);
				}
			}
			$this->__export($export, $ex_list, $activity);
			return false;
		}

		// 获取内部报名人
		list($total, $multi, $list) = $this->_partake_search($acid);
		// 数据整理
		foreach ($list as $k => $v) {
			$list[$k]['created'] = rgmdate($list[$k]['created']);
		}

		// 获取外部报名人
		list($ex_total, $ex_multi, $ex_list) = $this->_ex_outsider_search($acid);
		foreach ($ex_list as $k => $v) {
			$ex_list[$k]['created'] = rgmdate($ex_list[$k]['created']);
			$ex_list[$k]['remark'] = rhtmlspecialchars($ex_list[$k]['remark']);
			$ex_list[$k]['other'] = unserialize($ex_list[$k]['other']);
			foreach ($ex_list[$k]['other'] as $key => &$val) {
				$val = rhtmlspecialchars($val);
			}
		}

		$activity['start_time'] = rgmdate($activity['start_time']);
		$activity['end_time'] = rgmdate($activity['end_time']);
		$activity['cut_off_time'] = rgmdate($activity['cut_off_time']);
		$image = array();
		if (!empty($activity['at_ids'])) {
			$at_ids = explode(',', $activity['at_ids']);
			foreach ($at_ids as $value) {
				$image[] = '<a href="' . voa_h_attach::attachment_url($value, 0) . '" target="_blank">
								<img src="' . voa_h_attach::attachment_url($value, 45) . '"
								border="0" alt="" style="max-width:64px;max-height:32px;" /></a>';
			}
		}
		//展示数据
		$this->view->set('activity', $activity);
		$this->view->set('image', $image);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('ex_list', $ex_list);
		$this->view->set('ex_total', $ex_total);
		$this->view->set('ex_multi', $ex_multi);
		$this->output('office/activity/view');
	}

	/**
	 *查询一个活动的参与人员
	 * @param int $acid
	 * @param int $perpage
	 *return array($total, $multi, $list)
	 */
	protected function _partake_search($acid, $perpage = 12) {
		$list = array();
		$multi = null;
		$conds = array();
		//查询条件
		$conds = array('acid = ?' => $acid);
		$orderby['updated'] = 'DESC';
		//获取数据
		$serv = &service::factory('voa_s_oa_activity_partake');
		if ($perpage === null) {
			return $serv->list_by_conds($conds, null, $orderby);
		}
		$total = $serv->count_by_conds($conds);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;

			$list = $serv->list_by_conds($conds, $page_option, $orderby);
		}
		return array($total, $multi, $list);
	}

	/**
	 * 查询活动的外部报名人员
	 * @param $acid
	 * @param int $perpage
	 * @return array($total, $multi, $list);
	 */
	public function _ex_outsider_search($acid, $perpage = 12) {
		$list = array();
		$multi = null;
		$conds = array();
		// 查询条件
		$conds = array('acid' => $acid);
		$orderby['updated'] = 'DESC';
		// 获取数据
		$serv = &service::factory('voa_s_oa_activity_outsider');
		if ($perpage === null) {
			return $serv->list_by_conds($conds, null, $orderby);
		}
		$total = $serv->count_by_conds($conds);
		if ($total > 0) {
			$pager_options = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true
			);
			$multi = pager::make_links($pager_options);
			pager::resolve_options($pager_options);
			$page_option[0] = $pager_options['start'];
			$page_option[1] = $perpage;
			$list = $serv->list_by_conds($conds, $page_option, $orderby);
		}
		return array($total, $multi, $list);
	}

	private function __export($export, $list, $activity) {
		// 待输出的数据，数组格式
		$data = array();
		$filename = '活动报名.csv';
		switch ($export) {
			//内部报名名单
			case 'inner':

				$filename = $activity['title'] . '_内部报名名单' . '.csv';
				// 标题栏 - 字段名称
				$data[] = array(
					'username' => '报名人',
					'created' => '报名时间',
					'remark' => '备注',
					'status' => '状态'
				);
				// 遍历数据每行一条
				foreach ($list as $_row) {
					$status = '';
					if ($_row['check'] == 1) {
						$status = '已签到';
					} elseif ($_row['type'] == 1) {
						$status = '已报名';
					} elseif ($_row['type'] == 2) {
						$status = '申请取消中';
					} elseif ($_row['type'] == 3) {
						$status = '已取消';
					}
					$data[] = array(
						'username' => $_row['name'],
						'created' => $_row['created'],
						'remark' => $_row['remark'],
						'status' => $status
					);
				}
				break;
			//外部报名名单
			case 'outer':

				$filename = $activity['title'] . '_外部报名名单' . '.csv';
				// 标题栏 - 字段名称
				$data[0] = array(
					'outname' => '姓名',
					'outphone' => '手机号',
					'remark' => '备注',
					'created' => '报名时间'
				);
				$fields = array();
				//扩展字段
				if (!empty($activity['outfield'])) {
					$fields = unserialize($activity['outfield']);
					if (is_array($fields)) {
						unset($fields['outphone'], $fields['outname'], $fields['remark']);
						foreach ($fields as $field) {
							$data[0][$field['name']] = $field['name'];
						}
					}
				}
				$data[0]['status'] = '状态';
				// 遍历数据每行一条
				foreach ($list as $_row) {
					$temp = array(
						'outname' => $_row['outname'],
						'outphone' => $_row['outphone'],
						'remark' => $_row['remark'],
						'created' => $_row['created']
					);
					if (is_array($fields)) {
						// 反序列化自定义数据
						$_row['other'] = unserialize($_row['other']);
						// 遍历赋值
						foreach ($fields as $field) {
							$temp[$field['name']] = isset($_row['other'][$field['name']]) ? $_row['other'][$field['name']] : '';
						}
					}
					$temp['status'] = $_row['check'] == 1 ? '已签到' : '已报名';
					$data[] = $temp;
				}
				break;
		}


		// 转换为csv字符串
		$csv_data = array2csv($data);

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

		exit;
	}
}
