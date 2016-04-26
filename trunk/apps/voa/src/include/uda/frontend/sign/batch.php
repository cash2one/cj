<?php

/**
 * @author Burce
 */
class voa_uda_frontend_sign_batch extends voa_uda_frontend_sign_base {

	/**
	 * 格式 数据
	 *
	 * @param $in
	 * @param $list
	 * @return bool
	 */
	protected function _formater($in, &$list) {

		$dep = array();
		$this->_getdepartment($in, $dep);

		// 判断是否是全公司
		$serv_de = &service::factory('voa_s_oa_common_department');

		$dep['work_days'] = unserialize($dep['work_days']);
		foreach ($dep['work_days'] as &$v) {
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
		$dep['_work_days'] = implode('、', $dep['work_days']);
		$dep['work_begin'] = $this->formatnum($dep['work_begin']);
		$dep['work_end'] = $this->formatnum($dep['work_end']);
		$dep['_work_begin'] = substr($dep['work_begin'], 0, 2) . ':' . substr($dep['work_begin'], 2, 2);
		$end_start = substr($dep['work_end'], 0, 2);

		if ($end_start >= 24) {
			$end_start = '次日' . ($end_start - 24);
		}
		$dep['_work_end'] = $end_start . ':' . substr($dep['work_end'], 2, 2);
		$dep['start_begin'] = date('Y-m-d', $dep['start_begin']);
		$dep['start_end'] = empty($dep['start_end']) ? ' ' : date('Y-m-d', $dep['start_end']);
		$dep['_department'] = implode('、', $dep['_department']);

		$list = $dep;

		return true;
	}

	/**
	 * 添加
	 *
	 * @param $in
	 * @return bool
	 */
	public function add($in) {

		$serv = &service::factory('voa_s_oa_sign_batch');
		$serv_department = &service::factory('voa_s_oa_sign_department');
		$data = array();

		if (! $this->__deal_function_add_data($in, $data)) {
			return false;
		}

		// 记录签到/签退时间
		if (!empty($data['sign_on'])) {
			$work_begin = $data['work_begin'];
		}

		if (!empty($data['sign_off'])) {
			$work_end = $data['work_end'];
		}

		// 判断部门是否冲突
		$serv_depa = &service::factory('voa_s_oa_sign_department');
		// 判断部门是否冲突
		$conds_ct['department IN (?)'] = $in['department'];

		$dep_list = $serv_depa->list_by_conds($conds_ct); // 部门列表
		if (! empty($dep_list)) {
			$sb_list = array();
			foreach ($dep_list as $_dep) {
				$sb_list[] = $_dep['sbid']; // 班次信息
			}
			$conds_result['enable'] = 1;
			$conds_result['sbid IN (?)'] = $sb_list;
			$result = $serv->list_by_conds($conds_result); // 班次表里查询 班次
			$today = startup_env::get('timestamp');
			// 判断班次是否可用
			if ($data['start_begin'] > $today) {
				$data['enable'] = 0;
			}
			$result_end = array();
			if (! empty($result)) { // 从可用班次里取符合时间的班次
				foreach ($result as $_resu) {
					$min_t = $_resu['start_begin'];
					if (! empty($_resu['start_end'])) {
						// 结束日期
						$max_t = $_resu['start_end'] + 86400;
						if ($_resu['enable'] == 1 && $today < $max_t && $today > $min_t) {
							$result_end[] = $_resu;
						}
						if ($max_t < $data['start_begin']) {
							$result_end = array();
						}
					} else { // 可用，并且开启时间小于当天
						if ($_resu['enable'] == 1 && $today > $min_t) {
							$result_end[] = $_resu;
						}
					}
				}
			}
			// 冲突提示
			if ($result_end) {
				$this->errcode = '10521';
				$this->errmsg = '该部门已存在班次';

				return false;
			}
		}
		$sbid = $serv->insert($data);

		if (! empty($in['department'])) {
			if ($sbid) {
				// 构造插入部门数据
				$dep_info = array();
				foreach ($in['department'] as $val) {
					$dep_info[] = array('department' => $val, 'sbid' => $sbid['sbid']);
				}
				$serv_department->insert_multi($dep_info);
			}
		}

		// 新增签到计划任务
		if (!empty($data['sign_on'])) {
			$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_on', $work_begin));
			$this->__add_task(md5($origin_str), $work_begin, 'sign_on');
		}

		// 新增签退计划任务
		if (!empty($data['sign_off'])) {
			$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_off', $work_end));
			$this->__add_task(md5($origin_str), $work_end, 'sign_off');
		}

		return true;
	}

	/**
	 * 处理add方法的数据
	 *
	 * @param $in
	 * @param $data
	 * @return bool
	 */
	private function __deal_function_add_data($in, &$data) {
		// 处理数据
		if (! $this->__deal_function_add_data_in($in, $data)) {
			return false;
		}
		// 判断非负数

		// 判断结束时间
		if ($data['work_begin'] > $data['work_end']) {
			$this->errcode = '12006';
			$this->errmsg = '工作结束时间必须大于开始时间';

			return false;
		}
		if ($in['start_end'] != '') {
			$data['start_end'] = strtotime($in['start_end']);
			if ($data['start_end'] < $data['start_begin']) {
				$this->errcode = '12005';
				$this->errmsg = '结束时间必须大于开始时间';

				return false;
			}
		} else {
			$data['start_end'] = '';
		}
		$data['address_range'] = $in['address_range'];
		if (count($in['sb_set']) < 2) {
			$data['sb_set'] = $in['sb_set'][0];
		} else {
			$data['sb_set'] = 3;
		}
		if (isset($in['sbid'])) {
			$data['sbid'] = $in['sbid'];
		}
		//判断班次名称是否已存在
		$serv_bat = &Service::factory('voa_s_oa_sign_batch');
		$conds['name = ?'] = $data['name'];
		//数据库里已经存在该数据
		if($serv_bat->list_by_conds($conds)){
			$this->errcode = '12008';
			$this->errmsg = '该班次名称已被使用';
			return false;
		}
		//插入值
		$data['late_range'] = $in['late_range'];
		$data['department'] = $in['department'];
		$data['remind_on'] = $in['remind_on'];
		$data['remind_off'] = $in['remind_off'];
		$data['come_late_range'] = $in['come_late_range'];
		$data['leave_early_range'] = $in['leave_early_range'];
		if (! empty($in['latitude'])) {
			$data['latitude'] = $in['latitude'];
		}
		if (! empty($in['longitude'])) {
			$data['longitude'] = $in['longitude'];
		}

		// 新增的值 add by ppker
		$data['range_on'] = $in['range_on'];
		$data['sign_on'] = $in['sign_on'];
		$data['sign_off'] = $in['sign_off'];
		// 过滤提交的值
		if (0 == $in['range_on']) {
			$data['address'] = '';
			if (isset($in['latitude']) && isset($data['latitude'])) {
				$data['latitude'] = '';
			}
			if (isset($in['longitude']) && isset($data['longitude'])) {
				$data['longitude'] = '';
			}
			$data['address_range'] = 0;
		} else {
			if (empty($data['latitude'])) {
				$this->errcode = '12016';
				$this->errmsg = '考勤位置经度不得为空';
				return false;
			}
			if (empty($data['longitude'])) {
				$this->errcode = '12019';
				$this->errmsg = '考勤位置纬度不得为空';
				return false;
			}
			if (empty($data['address'])) {
				$this->errcode = '12020';
				$this->errmsg = '请在地图上设置考勤地址';
				return false;
			}
			if (empty($data['address_range'])) {
				$this->errcode = '12021';
				$this->errmsg = '考勤范围不得为空';
				return false;
			}

		}

		if (0 == $in['sign_on']) {
			$data['remind_on'] = '';
		} else {
			if (empty($data['remind_on'])) {
				$this->errcode = '12017';
				$this->errmsg = '签到提示不能为空';
				return false;
			}
		}

		if (0 == $in['sign_off']) {
			$data['remind_off'] = '';
		} else {
			if (empty($data['remind_off'])) {
				$this->errcode = '12018';
				$this->errmsg = '签退提示不能为空';
				return false;
			}
		}

		return true;
	}

	/**
	 * 删除任务
	 * @param int $taskid 任务id
	 */
	public function __del_task($taskid, $type) {

		$setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
		return $rpc_crontab->Del_by_taskid_domain_type($taskid, $setting['domain'], $type);
	}

	/**
	 * 更新计划任务
	 * @param int $taskid 计划任务ID
	 * @param string $runtime 执行时间点
	 * @param string $type 任务类型
	 */
	public function __update_task($taskid, $runtime, $type) {

		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
		$h = substr($runtime, 0, -2);
		$m = substr($runtime, -2);
		$h = 23 < $h ? ($h - 24) : $h;
		$ts = rstrtotime(rgmdate(startup_env::get('timestamp'), 'Y-m-d') . " {$h}:{$m}:00");
		if ('sign_on' == $type) {
			$ts -= 300;
		} else {
			$ts += 300;
		}
		// 添加签退计划任务
		return $rpc_crontab->Update(array(
			'taskid' => $taskid,
			'domain' => $this->_sitesets['domain'],
			'type' => $type,
			'taskid' => $taskid,
			'runtime' => $ts,
			'endtime' => 0,
			'looptime' => 86400
		));
	}

	/**
	 * 新增计划任务
	 * @param int $taskid 计划任务ID
	 * @param string $runtime 执行时间点
	 * @param string $type 任务类型
	 */
	public function __add_task($taskid, $runtime, $type) {

		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
		$h = substr($runtime, 0, -2);
		$m = substr($runtime, -2);
		$h = 23 < $h ? ($h - 24) : $h;
		$ts = rstrtotime(rgmdate(startup_env::get('timestamp'), 'Y-m-d') . " {$h}:{$m}:00");
		if ('sign_on' == $type) {
			$ts -= 300;
		} else {
			$ts += 300;
		}
		// 添加签退计划任务
		return $rpc_crontab->add(array(
			'taskid' => $taskid,
			'domain' => $this->_sitesets['domain'],
			'type' => $type,
			'ip' => '',
			'runtime' => $ts,
			'endtime' => 0,
			'looptime' => 86400,
			'times' => 0,
			'runs' => 0
		));
	}

	/**
	 * 处理__deal_function_add_data方法的 in数据
	 *
	 * @param $in
	 * @param $data
	 * @return bool
	 */
	private function __deal_function_add_data_in($in, &$data) {
		// 判断是否为空
		if (empty($in['name'])) {
			$this->errcode = '10004';
			$this->errmsg = '班次名称不能为空';

			return false;
		}
		// 判断非负数
		if (isset($in['address_range']) && !empty($in['address_range'])) {
			if ($in['address_range'] < 500) {
				$this->errcode = '10015';
				$this->errmsg = '考勤范围不能小于500米111';
				return false;
			}
		}

		// 判断非负数
		if ($in['late_range'] < 0) {
			$this->errcode = '10016';
			$this->errmsg = '时间设置不能小于0';

			return false;
		}

		if (empty($in['work_begin'])) {
			$this->errcode = '10005';
			$this->errmsg = '开始时间不能为空';

			return false;
		}
		if (empty($in['department'])) {
			$this->errcode = '10005';
			$this->errmsg = '部门不能为空';

			return false;
		}
		if (empty($in['work_end'])) {
			$this->errcode = '10006';
			$this->errmsg = '结束时间不能为空';

			return false;
		}

		if (empty($in['sb_set'])) {
			$this->errcode = '10009';
			$this->errmsg = '请设置打卡模式';

			return false;
		}

		// 赋值
		$data['name'] = $in['name'];
		$data['work_begin'] = (int)$in['work_begin'];
		$data['work_end'] = (int)$in['work_end'];
		$data['address'] = $in['address'];
		$data['work_days'] = serialize($in['work_days']);
		$data['start_begin'] = strtotime($in['start_begin']);

		return true;
	}

	/**
	 * 获取班次信息
	 *
	 * @param $sbid 班次ID
	 * @param $out
	 * @return bool
	 */
	public function edit($sbid, &$out) {

		if (empty($sbid)) {
			$this->errcode = '10056';
			$this->errmsg = '获取编辑信息失败';
			exit();
		}

		// 获取数据, 格式化
		$serv = &service::factory('voa_s_oa_sign_batch');
		$list = $serv->get($sbid);
		$data[0] = $list;
		$da = array();
		$this->_formater($list, $da);
		$out = $da;

		return true;
	}

	/**
	 * 获取部门信息
	 *
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function _getdepartment($in, &$out) {

		$dep = voa_h_cache::get_instance()->get('department', 'oa');
		$dep_batch = &service::factory('voa_s_oa_sign_department');

		$dep_list = $dep_batch->list_all();

		// 获取部门名称
		foreach ($dep_list as $_dep) {

			if ($_dep['sbid'] == $in['sbid']) {

				$in['department'][] = $_dep['department'];
				foreach ($in['department'] as $_k => &$_d) {
					$in['_department'][] = $dep[$_d]['cd_name'];
					$in['_department'] = array_unique($in['_department']);
				}
			}
		}

		$out = $in;

		return true;
	}

	/**
	 * 更新
	 *
	 * @param $in
	 * @return bool
	 */
	public function update($in) {

		$data = array();
		// 处理获取的数据
		if (! $this->__deal_data_in_update($in, $data)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_sign_batch');
		$serv_dep = &service::factory('voa_s_oa_sign_department');

		// 部门表原来数据
		$conds['sbid'] = $data['sbid'];
		$list = $serv_dep->list_by_conds($conds);

		// 判断部门是否冲突
		$conds_ct['department IN (?)'] = $in['department'];

		// 记录签到/签退时间
		if (!empty($data['sign_on'])) {
			$work_begin = $data['work_begin'];
		}

		if (!empty($data['sign_off'])) {
			$work_end = $data['work_end'];
		}

		// 获取班次
		$batch = $serv->get($data['sbid']);
		$dep_list = $serv_dep->list_by_conds($conds_ct);
		if (! empty($dep_list)) {
			if (! $this->__not_empty_dep_list($dep_list, $serv, $data, $in)) {
				return false;
			}
		}

		// 部门列表
		if (! empty($list)) {
			if (! $this->__not_empty_list($list, $serv, $data, $serv_dep)) {
				return false;
			}
		}

		// 删除签到计划任务
		if (empty($data['sign_on'])) {
			$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_on', $batch['work_begin']));
			$this->__del_task(md5($origin_str), 'sign_on');
		} else { // 更新计划任务
			if (!empty($batch) && $work_begin != $batch['work_begin']) {
				$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_on', $batch['work_begin']));
				logger::error(md5($origin_str).','.$origin_str);
				$this->__del_task(md5($origin_str), 'sign_on');
			}

			$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_on', $work_begin));
			$this->__update_task(md5($origin_str), $work_begin, 'sign_on');
		}

		// 删除签退计划任务
		if (empty($data['sign_off'])) {
			$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_off', $batch['work_end']));
			$this->__del_task(md5($origin_str), 'sign_off');
		} else { // 更新计划任务
			if (!empty($batch) && $work_begin != $batch['work_end']) {
				$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_off', $batch['work_end']));
				logger::error(var_export($batch, true));
				$this->__del_task(md5($origin_str), 'sign_off');
			}

			$origin_str = implode(',', array($this->_sitesets['domain'], 'sign_off', $work_end));
			$this->__update_task(md5($origin_str), $work_end, 'sign_off');
		}

		return true;
	}

	/**
	 * 如果$list 不空的处理
	 *
	 * @param $list
	 * @param $serv
	 * @param $data
	 * @param $serv_dep
	 * @return bool
	 */
	private function __not_empty_list($list, $serv, $data, $serv_dep) {

		$de_list = array();
		foreach ($list as $val) {
			$de_list[] = $val['department'];
		}
		if ($serv->update($data['sbid'], $data)) {

			// 删除部门
			$sub = array_diff($de_list, $data['department']);
			if (! empty($sub)) {
				// 要删除部门的id
				$det_conds['sbid'] = $data['sbid'];
				$det_conds['department IN (?)'] = $sub;
				$serv_dep->delete_by_conds($det_conds);
			}

			// 添加部门
			$add = array_diff($data['department'], $de_list);

			// 构造插入部门数据
			$dep_info = array();
			if (! empty($add)) {
				foreach ($add as $department) {
					$dep_info[] = array('department' => $department, 'sbid' => $data['sbid']);
				}
				// 多条插入操作
				$serv_dep->insert_multi($dep_info);
			}
			// 报错提示
		} else {
			$this->errcode = '10003';
			$this->errmsg = '修改失败';

			return false;
		}

		return true;
	}

	/**
	 * 如果$dep_list 不空的处理
	 *
	 * @param $dep_list
	 * @param $serv
	 * @param $data
	 * @param $in
	 * @return bool
	 */
	private function __not_empty_dep_list($dep_list, $serv, $data, $in) {

		$sb_list = array();
		foreach ($dep_list as $_dep) {
			$sb_list[] = $_dep['sbid'];
		}

		// 条件
		$conds_result['enable'] = 1;
		$conds_result['sbid IN (?)'] = $sb_list;
		$result = $serv->list_by_conds($conds_result);
		$today = startup_env::get('timestamp');

		// 判断时间是否可用
		if ($data['start_begin'] > $today) {
			$data['enable'] = 0;
		}
		$result_end = array();
		if (! empty($result)) {
			foreach ($result as $_resu) {
				$min_t = $_resu['start_begin'];
				if (! empty($_resu['start_end'])) {
					$max_t = $_resu['start_end'] + 86400;
					if ($_resu['enable'] == 1 && $today < $max_t && $today > $min_t) {
						$result_end[$_resu['sbid']] = $_resu;
					}
					if ($max_t < $data['start_begin']) {
						$result_end = array();
					}
				} else {
					if ($_resu['enable'] == 1 && $today > $min_t) {
						$result_end[$_resu['sbid']] = $_resu;
					}
				}
			}
		}

		$conds_own['sbid'] = $in['sbid'];
		$own = $serv->list_by_conds($conds_own);
		// 冲突的部门不在本班次内
		if ($result_end != $own) {
			if ($result_end) {
				$this->errcode = '10521';
				$this->errmsg = '该部门已存在班次';

				return false;
			}
		}

		return true;
	}

	/**
	 * 处理update方法数据
	 *
	 * @param $in
	 * @param $data
	 * @return bool
	 */
	private function __deal_data_in_update($in, &$data) {
		// 处理in 数据
		if (! $this->__deal_data_in_update_in($in, $data)) {
			return false;
		}
		if ($data['work_begin'] > $data['work_end']) {
			$this->errcode = '12006';
			$this->errmsg = '工作结束时间必须大于开始时间';

			return false;
		}
		if ($in['start_end'] != 0) {
			$data['start_end'] = strtotime($in['start_end']);
			if ($data['start_end'] < $data['start_begin']) {
				$this->errcode = '12005';
				$this->errmsg = '结束时间必须大于开始时间';

				return false;
			}
		} else {
			$data['start_end'] = '';
		}

		$data['address_range'] = $in['address_range'];

		if (count($in['sb_set']) < 2) {
			$data['sb_set'] = $in['sb_set'][0];
		} else {
			$data['sb_set'] = 3;
		}
		if (isset($in['sbid'])) {
			$data['sbid'] = $in['sbid'];
		}
		$data['late_range'] = $in['late_range'];
		$data['department'] = $in['department'];
		$data['remind_on'] = $in['remind_on'];
		$data['remind_off'] = $in['remind_off'];
		$data['come_late_range'] = $in['come_late_range'];
		$data['leave_early_range'] = $in['leave_early_range'];

		$data['range_on'] = $in['range_on'];
		$data['sign_on'] = $in['sign_on'];
		$data['sign_off'] = $in['sign_off'];


		if (! empty($in['latitude'])) {
			$data['latitude'] = $in['latitude'];
		}
		if (! empty($in['longitude'])) {
			$data['longitude'] = $in['longitude'];
		}
		// 进行过滤啊

		if (0 == $data['range_on']) {
			$data['address'] = '';
			$data['latitude'] = '';
			$data['longitude'] = '';
			$data['address_range'] =500;
		}

		if (0 == $data['sign_on']) {
			$data['remind_on'] = '';
		}
		if (0 == $data['sign_off']) {
			$data['remind_off'] = '';
		}
			//判断班次名称是否已存在
		$serv_bat = &Service::factory('voa_s_oa_sign_batch');
		$sbinfo = $serv_bat->get($in['sbid']);
		$conds['name = ?'] = $data['name'];
		//数据库里已经存在该名称
		if($sbinfo['name'] == $data['name']){
			//不做处理
		}else{
			//不是和自身冲突
			if($serv_bat->list_by_conds($conds)){
				$this->errcode = '12008';
				$this->errmsg = '该班次名称已被使用';
				return false;
			}
		}

		return true;
	}

	/**
	 * 处理 __deal_data_in_update 方法的in 数据
	 *
	 * @param $in
	 * @param $data
	 * @return bool
	 */
	private function __deal_data_in_update_in($in, &$data) {

		if (empty($in['name'])) {
			$this->errcode = '10004';
			$this->errmsg = '班次名称不能为空';

			return false;
		}
		if (empty($in['work_begin'])) {
			$this->errcode = '10005';
			$this->errmsg = '开始时间不能为空';

			return false;
		}
		if (empty($in['department'])) {
			$this->errcode = '10005';
			$this->errmsg = '部门不能为空';

			return false;
		}
		if (empty($in['work_end'])) {
			$this->errcode = '10006';
			$this->errmsg = '结束时间不能为空';

			return false;
		}
		if (1 == $in['range_on']) {
			if (empty($in['longitude']) || empty($in['latitude'])) {
				$this->errcode = '10007';
				$this->errmsg = '请指定考勤地点';
				return false;
			}
			if (empty($in['address'])) {
				$this->errcode = '10010';
				$this->errmsg = '请在地图上设置考勤地址';

				return false;
			}

			if (empty($in['address_range'])) {
				$this->errcode = '10008';
				$this->errmsg = '请指定考勤范围';

				return false;
			}

		}

		if (1 == $in['sign_on']) {
			if (empty($in['remind_on'])) {
				$this->errcode = '10028';
				$this->errmsg = '签到提醒不能设置为空';
				return false;
			}
		}

		if (1 == $in['sign_off']) {
			if (empty($in['remind_off'])) {
				$this->errcode = '10029';
				$this->errmsg = '签退提醒不能设置为空';
				return false;
			}
		}


		if (empty($in['sb_set'])) {
			$this->errcode = '10009';
			$this->errmsg = '请设置打卡模式';

			return false;
		}
		$data['name'] = $in['name'];
		$data['address'] = $in['address'];
		$data['work_begin'] = (int)$in['work_begin'];
		$data['work_end'] = (int)$in['work_end'];
		$data['work_days'] = serialize($in['work_days']);
		$data['start_begin'] = strtotime($in['start_begin']);

		return true;
	}

}
