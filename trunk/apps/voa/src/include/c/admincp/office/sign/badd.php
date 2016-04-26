<?php

/**
 * voa_c_admincp_office_sign_edit
 * 企业后台/微办公管理/考勤签到/编辑状态
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_badd extends voa_c_admincp_office_sign_base {
	public function execute() {

		$post = $this->request->postx();
		$get = $this->request->getx();
		$uda = &uda::factory('voa_uda_frontend_sign_batch');

		//添加操作
		if (!empty($post) && empty($post['sbid'])) {

			if ($uda->add($post)) {
				$this->message('success', '添加成功', $this->cpurl($this->_module, $this->_operation, 'blist', $this->_module_plugin_id), false);
			} else {
				$this->message('error', $uda->errmsg);
			}
		}
		
		//编辑数据加载显示操作
		$list = array();
		if (!empty($get['sbid'])) {
			// 加载数据
			$this->_edit_and_show($get, $uda, $list);
			// 添加编辑标示
			$this->view->set('is_edit', 1);
		} else {
			$this->view->set('default_departments', rjson_encode(array()));
		}

		//修改操作
		if (!empty($post['sbid'])) {
			if ($uda->update($post)) {
				$this->message('success', '更新成功', $this->cpurl($this->_module, $this->_operation, 'blist', $this->_module_plugin_id), false);
			} else {
				$this->message('error', $uda->errmsg);
			}
		}

		// 上班时间开始范围0：00 - 24:00
		$this->_start_area($list);
		// 上班结束时间范围
		$this->_end_area($list);
		$this->view->set('default_date', rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
		$this->output('office/sign/badd');
	}

	/**
	 * 上班结束时间范围
	 * @param $list
	 * @return bool
	 */
	protected function _end_area($list) {
		//上班时间结束范围0：00 - 24:00
		$i = 0;
		$j = '00';
		// 循环添加0点到24点之间的整点时间
		while ($i < 24) {

			$open = 0; // 开关
			if ($i < 10 && strlen($i) < 2) {
				$i = '0' . $i;
			}

			$str = '<option value =' . $i . $j . '>' . $i . ':' . $j . '</option>';
			if (!empty($list['work_end']) && $list['work_end'] == $i . $j) {
				$str_arr2 [] = '<option value =' . $i . $j . ' selected>' . $i . ':' . $j . '</option>';
				$open = 1;
			}
			if(empty($list['work_end']) && $i . $j == 1800){
				$str_arr2 [] = '<option value =' . $i . $j . ' selected>' . $i . ':' . $j . '</option>';
				$open = 1;
			}
			if (1 != $open) {
				$str_arr2 [] = $str;
			}
			$j += 30;
			if ($j % 60 == 0) {
				$i += 1;
				$j = '00';
			}
		}
		//上班时间结束范围24:00 - 36:00
		$i = 24;
		$j = '00';
		// 循环添加24点到次日12点半之间的整点时间
		while ($i <= 36) {
			if(!empty($list['work_end']) && $list['work_end'] == $i . $j){
			$str_arr2 [] = '<option value =' . $i . $j . ' selected>次日' . ($i - 24) . ':' . $j . '</option>';
			}else{
				$str_arr2 [] = '<option value =' . $i . $j . '>次日' . ($i - 24) . ':' . $j . '</option>';
			}
			$j += 30;
			if ($j % 60 == 0) {
				$i += 1;
				$j = '00';
			}
		}
		$this->view->set('str_arr2', $str_arr2);

		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));


		return true;
	}

	/**
	 * 上班时间开始范围0：00 - 24:00
	 * @param $list
	 * @return bool
	 */
	protected function _start_area($list) {
		$i = 0;
		$j = '00';
		// 循环添加下拉框中的0点到24点之间的整点时间
		while ($i < 24) {

			$open = 0; // 开关
			if ($i < 10 && strlen($i) < 2) {
				$i = '0' . $i;
			}

			$str = '<option value =' . $i . $j . '>' . $i . ':' . $j . '</option>';
			if (!empty($list['work_begin']) && $list['work_begin'] == $i . $j) {
				$str_arr [] = '<option value =' . $i . $j . ' selected>' . $i . ':' . $j . '</option>';
				$open = 1;
			}
			if(empty($list['work_begin']) && $i . $j == 900){
				$str_arr [] = '<option value =' . $i . $j . ' selected>' . $i . ':' . $j . '</option>';
				$open = 1;
			}
			if (1 != $open) {
				$str_arr [] = $str;
			}
			
			$j += 30;
			if ($j % 60 == 0) {
				$i += 1;
				$j = '00';
			}
		}

		$this->view->set('str_arr', $str_arr);

		return true;
	}

	/**
	 * 编辑数据加载显示操作
	 * @param $get get数据
	 * @param $uda uda实例化
	 * @return bool
	 */
	protected function _edit_and_show($get, $uda, &$list) {

		// 获取班次信息
		$list = array();
		$uda->edit($get['sbid'], $list);
		if ($uda->errmsg) {
			$this->message('error', $uda->errmsg);

			return false;
		}

		// 获取部门缓存
		$depart = voa_h_cache::get_instance()->get('department', 'oa');

		// 获取部门信息
		$default_departments = array();
		foreach ($list['department'] as $_d) {
			$default['id'] = $_d;
			$default['name'] = $depart[$_d]['cd_name'];
			$default['isChecked'] = (bool)true;
			$default_departments[] = $default;
		}
		$default_departments = rjson_encode(array_values($default_departments));

		$this->view->set('list', $list);
		$this->view->set('default_departments', $default_departments);

		return true;
	}

	/**
	 * 获取指定签到记录的重设备注日志
	 * @param     $cp_pluginid
	 * @param int $sr_id
	 * @param int $perpage
	 * @return array
	 * @throws controller_exception
	 */
	protected function _get_detail($cp_pluginid, $sr_id = 0, $perpage = 10) {
		// 获取日志总数
		$total = $this->_service_single('sign_detail', $cp_pluginid, 'count_all_by_sr_id', $sr_id);
		$list = array();
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true
			);
			// 分页信息
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$tmp = $this->_service_single('sign_detail', $cp_pluginid, 'fetch_all_by_sr_id', $sr_id, $pagerOptions ['start'], $pagerOptions ['per_page']);
			foreach ($tmp as $_id => $_data) {
				//
				$list [$_id] = $this->_format_sign_detail($_data);
			}
			unset ($tmp);
		}

		return array(
			$total,
			$multi,
			$list
		);
	}
}
