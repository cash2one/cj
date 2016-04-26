<?php

/**
 * 新打卡首页
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_index extends voa_c_frontend_sign_base {

	public function execute() {

		//header('Location: /h5/index.html?_ts=' . startup_env::get('timestamp') . '#/app/page/checking-in/inner');
		//$this->response->stop(); exit;
		$url = '/h5/index.html?_ts=' . startup_env::get('timestamp') . '#/app/page/checking-in/inner';
		$this->view->set('redirect_url', $url);
		$this->_output('mobile/redirect');
		exit;
		// 判断班次
		$uid = startup_env::get('wbs_uid');
		$serv_member = &service::factory('voa_s_oa_member_department');
		$serv_department = &service::factory('voa_s_oa_sign_department');
		$serv_batch = &service::factory('voa_s_oa_sign_batch');
		$conds_mem ['m_uid'] = $uid;
		$userinfo = $serv_member->fetch_all_by_conditions($conds_mem);

		$dep = array();
		// 获取所有当前用户所在的各个部门
		foreach ($userinfo as $_uinfo) {
			$dep [] = $_uinfo ['cd_id'];
		}

		// 获取班次信息
		$info = $this->__get_batch_info($dep, $serv_member, $serv_department, $serv_batch);

		// 显示时间
		// 初始化一些基础数据
		$this->initialize_same_data();

		// 获取到班次信息后计算其他属性
		if (isset ($info)) {
			// 开始的部分数据
			$first_property = $this->first_property($info);
			$allow_sign = $first_property['allow_sign'];
			$sb_set = $first_property['sb_set'];
			$records = $first_property['records'];

			// 剩下的数据
			$work_on = null;
			$work_off = null;
			$sign_detail = null;
			$on_signtime_hi = null;
			$off_signtime_hi = null;
			$sr_id = null;

			// 上班卡/下班卡
			$up_down_data = $this->up_down_work($records, $sb_set);
			$on_signtime_hi = $up_down_data['on_signtime_hi'];
			$work_on = $up_down_data['work_on'];
			$off_signtime_hi = $up_down_data['off_signtime_hi'];
			$work_off = $up_down_data['work_off'];


			// 判断那种卡未打, 设置对应的时间
			$up_down_data = $this->is_no_get($work_on, $sb_set, $info, $on_signtime_hi, $off_signtime_hi);
			$on_signtime_hi = $up_down_data['on_signtime_hi'];
			$off_signtime_hi = $up_down_data['off_signtime_hi'];
			$p_set = $up_down_data['p_set'];

			$sr_id = array();
			//判断签到次数计算传过去的sr_id
			$re_data = $this->qian_pass($work_on, $work_off);
			$detail = $re_data['detail'];
			$sr_id = $re_data['sr_id'];


			// 备注
			$sign_detail = $this->get_remark();

			// 跳到备注的方法
			$beizhu = $this->beizhu($info, $work_on);
			$si_on = $beizhu['si_on'];
			$sign_type = $beizhu['sign_type'];

			//分配变量到模板
			$this->__assign_var($allow_sign, $sr_id, $sign_detail, $detail, $p_set, $work_on, $si_on, $sign_type, $work_off, $on_signtime_hi, $off_signtime_hi, $info);
		}

		// 获取微信js api调用签名信息
		$this->_get_jsapi("['getLocation']");

		$this->_output('sign/index');
	}

	/**
	 * 获取班次信息
	 * @param $serv_member
	 * @param $serv_department
	 * @param $serv_batch
	 * @return bool|mixed
	 */
	private function __get_batch_info($dep, $serv_member, $serv_department, $serv_batch) {
		// 用户选择部门后
		$get = $this->request->getx();
		if (!empty ($get)) {
			$dep = array();
			$batchid = $get ['batchid'];
		}

		// 用户所在部门数量大于1
		if (count($dep) >= 2 && !isset ($batchid)) {
			$info = $this->gt_two_department(startup_env::get('wbs_uid'), $serv_member, $serv_department, $serv_batch, $dep);

		} elseif (isset ($batchid)) {//用户选择了班次
			$info = $serv_batch->get($batchid);
		} else {
			// 只有一个部门的情况
			$info = $this->just_one_department(startup_env::get('wbs_uid'), $serv_member, $serv_department, $serv_batch, $dep);
		}

		return $info;
	}

	/**
	 * 分配变量到模板
	 * @param $allow_sign
	 * @param $sr_id
	 * @param $sign_detail
	 * @param $detail
	 * @param $p_set
	 * @param $work_on
	 * @param $si_on
	 * @param $sign_type
	 * @param $work_off
	 * @param $on_signtime_hi
	 * @param $off_signtime_hi
	 * @param $info
	 */
	private function __assign_var(
		$allow_sign, $sr_id, $sign_detail, $detail, $p_set, $work_on, $si_on, $sign_type, $work_off, $on_signtime_hi, $off_signtime_hi, $info
	) {

		$this->view->set('allow_sign', $allow_sign);
		$this->view->set('sr_id', $sr_id);
		$this->view->set('sign_detail', $sign_detail);
		$this->view->set('detail_sr_id', $detail);
		$this->view->set('sign_set', $p_set);
		$this->view->set('work_on', $work_on);
		$this->view->set('si_on', $si_on);
		$this->view->set('sign_type', $sign_type);
		$this->view->set('work_off', $work_off);
		$this->view->set('on_signtime_hi', $on_signtime_hi);
		$this->view->set('off_signtime_hi', $off_signtime_hi);
		$this->view->set('navtitle', '签到');
		$this->view->set('work_off_unix', rstrtotime(rgmdate(startup_env::get('timestamp'), "Y-m-d") . ' ' . $this->formattime($info ['work_end'])));
		$this->view->set('sb_set', $info ['sb_set']);
		$this->view->set('sbid', $info ['sbid']);
	}

	/**
	 * @param unknown $uid
	 * @param unknown $btime
	 * @param unknown $etime
	 * @return multitype:
	 */
	public function get_by_time($uid, $btime, $etime) {
		$serv = &service::factory('voa_s_oa_sign_record');
		$conds ['sr_signtime >= ?'] = $btime;
		$conds ['sr_signtime <= ?'] = $etime;
		$conds ['m_uid'] = $uid;
		$records = $serv->list_by_conds($conds);
		if (!$records) {
			$records = array();
		}

		return $records;
	}

	/**
	 * @param unknown $uid
	 * @param unknown $btime
	 * @param unknown $etime
	 * @return multitype:
	 */
	public function get_by_time_location($uid, $btime, $etime) {
		$serv = &service::factory('voa_s_oa_sign_location');
		$conds ['sl_signtime >= ?'] = $btime;
		$conds ['sl_signtime <= ?'] = $etime;
		$conds ['m_uid'] = $uid;
		$records = $serv->list_by_conds($conds);
		if (!$records) {
			$records = array();
		}

		return $records;
	}


	/*部门1个以上*/
	public function gt_two_department($uid, $serv_member, $serv_department, $serv_batch, $dep) {
		// 查班次部门表里对应部门的班次
		$conds_balist ['department IN (?)'] = $dep;
		$sign_bat = $serv_department->list_by_conds($conds_balist);
		// 所有的班次
		$all_batch = array();
		if (!empty($sign_bat)) {
			foreach ($sign_bat as $_sib) {
				$all_batch [] = $_sib ['sbid'];
			}
		}
		// 根据条件查班次
		$conds_lb ['sbid IN (?)'] = $all_batch;
		$list_bat = $serv_batch->list_by_conds($conds_lb);
		// 取可用的班次
		$enable_batlist = array();
		if (!empty ($enable_batlist)) {
			foreach ($list_bat as $_enbat) {
				if ($_enbat ['enable'] == 1) {
					$enable_batlist ['sbid'] = $_enbat ['name'];
				}
			}
		}
		// 从上级部门中获取
		if (empty ($enable_batlist)) {
			$uplis = array();
			foreach ($dep as $_upid) {
				$upid = $_upid;
				// 获取所有上级部门
				while (!in_array(0, $uplis)) {
					$upid = $this->get_upid($upid);
					$uplis [] = $upid;
				}
			}

			$conds_upbat ['department IN (?)'] = $uplis;
			// 所有部门的班次
			$upbatch = $serv_department->list_by_conds($conds_upbat);
			$upbalist = array();
			if (!empty($upbatch)) {
				foreach ($upbatch as $_upb) {
					$upbalist [] = $_upb ['sbid'];
				}
			}
			$conds_dd ['sbid IN (?)'] = $upbalist;
			$upbat_list = $serv_batch->list_by_conds($conds_dd);
			$enable = array();
			// 未禁用班次
			$today = startup_env::get('timestamp');
			foreach ($upbat_list as $_enable) {
				$min_t = $_enable ['start_begin'];
				if (!empty ($_enable ['start_end'])) {
					//设置了结束时间
					$max_t = $_enable ['start_end'] + 86400;
					if ($_enable ['enable'] == 1 && $today < $max_t && $today > $min_t) {
						$enable [$_enable ['sbid']] = $_enable ['name'];
					}
				} else {//未设置结束时间
					if ($_enable ['enable'] == 1 && $today > $min_t) {
						$enable [$_enable ['sbid']] = $_enable ['name'];
					}
				}
			}
		}
		//可用 班次数量大于1 才给用户选择框
		if (!empty ($enable) && count($enable) > 1) {
			$this->view->set('select', 1);

			$this->view->set('batchlist', $enable);
			unset ($enable, $upbat_list, $upbatch, $conds_upbat);
		} elseif (count($enable) == 1) {//可用班次数量为1 默认选择
			$sbid = key($enable);
			$info = $serv_batch->get($sbid);
		} else {//错误提示
			$this->_error_message('没有可用的班次');

			return false;
		}

		return $info;
	}

	// 只有一个部门的情况
	public function just_one_department($uid, $serv_member, $serv_department, $serv_batch, $dep) {
		//用户只有一个部门情况
		$conds ['department'] = $dep [0];
		$deplist = $serv_department->list_by_conds($conds);
		//获取部门对应有效班次
		if (!empty ($deplist)) {
			$infoid = $this->__get_department_batch($deplist, $serv_batch);
		}

		// 当前部门没有可用班次从上级部门找对应班次
		if (empty ($deplist) || empty ($infoid)) {

			$uplist = array(
				$dep [0]
			);
			$upid = $dep [0];
			// 获取所有上级部门
			while (!in_array(0, $uplist)) {
				$upid = $this->get_upid($upid);
				$uplist [] = $upid;
			}
			$conds_upbat ['department IN (?)'] = $uplist;
			// 所有部门的班次
			$upbatch = $serv_department->list_by_conds($conds_upbat);
			$upbalist = array();
			foreach ($upbatch as $_upb) {
				$upbalist [] = $_upb ['sbid'];
			}
			$conds_dd ['sbid IN (?)'] = $upbalist;
			$upbat_list = $serv_batch->list_by_conds($conds_dd);
			$enable = array();
			// 未禁用班次
			$today = startup_env::get('timestamp');
			foreach ($upbat_list as $_enable) {
				$min_t = $_enable ['start_begin'];
				if (!empty ($_enable ['start_end'])) {
					//设置了结束时间
					$max_t = $_enable ['start_end'] + 86400;
					if ($_enable ['enable'] == 1 && $today < $max_t && $today > $min_t) {
						$enable [] = $_enable;
					}
				} else {//未设置结束时间
					if ($_enable ['enable'] == 1 && $today > $min_t) {
						$enable [] = $_enable;
					}
				}
			}

			if (empty ($enable)) { // 所有上级部门都没有班次
				$this->_error_message('没有可用的班次');

				return false;
			} elseif (count($enable) == 1) { // 有一个上级部门有班次
				$info = reset($enable);
			} else { // 如果上级部门班次有多个

				$tmp_sbid = array();
				foreach ($enable as $_sim) {
					$tmp_sbid [] = $_sim ['sbid'];
				}
				// 有的上级没有班次
				$conds_sim ['sbid IN (?)'] = $tmp_sbid;
				$ublist = $serv_department->list_by_conds($conds_sim);
				$min = array();
				// 得到所有的有班次的上级
				foreach ($ublist as $u_d) {
					if (in_array($u_d ['department'], $uplist)) {
						$min [] = $u_d ['department'];
					}
				}

				$diff = array();
				//遍历存储每个上级部门的数量
				foreach ($min as &$_mi) {
					$min_uplist = array();
					$m_upid = $_mi;
					while (!in_array(0, $min_uplist)) {

						$m_upid = $this->get_upid($m_upid);
						$min_uplist [] = $m_upid;
					}
					//清除本次循环变量
					unset ($cdid);
					$diff [$_mi] = count($min_uplist);
					unset ($min_uplist);
				}

				//获取上级部门的班次id
				$endbatchid = $this->__get_updepartment_batch($diff, $_enable, $serv_department, $serv_batch);

				if ($endbatchid) {
					$info = $serv_batch->get($endbatchid);
				} else {
					$this->_error_message('没有可用班次');

					return false;
				}
			}
		} else { // 当前部门有班次

			$batlist = array();
			foreach ($deplist as $val) {
				$batlist [] = $val ['sbid'];
			}
			$conds_bat ['sbid IN (?)'] = $batlist;
			$batinfo = $serv_batch->list_by_conds($conds_bat);
			foreach ($batinfo as $_info) {
				if ($_info ['enable'] == 1) {
					$info = $_info;
				}
			}
		}

		return $info;

	}

	/**
	 * 获取上级部门的班次
	 * @param $diff
	 * @param $_enable
	 * @param $serv_department
	 * @param $serv_batch
	 * @return string
	 */
	private function __get_updepartment_batch($diff, $_enable, $serv_department, $serv_batch) {
		$newdiff = $diff;
		$endbatchid = '';
		// 循环找上级部门最多的部门
		while (!$endbatchid) {
			$val = max($diff);
			foreach ($newdiff as $_dikey => $_di) {
				if ($_di == $val) {
					$uupid = $_dikey;
				}
			}
			//获取到最近的上级部门id
			$conds_upmin ['department'] = $uupid;
			$minlist = $serv_department->list_by_conds($conds_upmin);
			$minlistid = array();
			foreach ($minlist as $_blid) {
				$minlistid [] = $_blid ['sbid'];
			}

			$conds_subid ['sbid IN (?)'] = $minlistid;
			$endlist = $serv_batch->list_by_conds($conds_subid);
			$today = startup_env::get('timestamp');
			//筛选可用的班次
			foreach ($endlist as $_endb) {
				$min_t = $_endb ['start_begin'];
				if (!empty ($_endb ['start_end'])) {
					$max_t = $_enable ['start_end'] + 86400;
					if ($_endb ['enable'] == 1 && $today < $max_t && $today > $min_t) {
						$endbatchid = $_endb ['sbid'];
					}
				} else {//未设置结束时间
					if ($_endb ['enable'] == 1 && $today > $min_t) {
						$endbatchid = $_endb ['sbid'];
					}
				}
			}
			//
			if (!empty ($diff)) {
				foreach ($diff as $_dif) {
					if ($_dif != $val) {
						$newdiff [] = $_dif;
					}
				}
			}
		}

		return $endbatchid;
	}

	/**
	 * 获取部门对应有效班次
	 * @param $deplist
	 * @param $serv_batch
	 * @return mixed
	 */
	private function __get_department_batch($deplist, $serv_batch) {
		//获取部门关联班次
		$tmp_bh = array();
		foreach ($deplist as $_cubid) {
			$tmp_bh [] = $_cubid ['sbid'];
		}
		$conds_curbat ['sbid IN (?)'] = $tmp_bh;
		$tmp_curbat = $serv_batch->list_by_conds($conds_curbat);
		$today = startup_env::get('timestamp');
		//判断是否可用，启用，结束时间是否符合
		foreach ($tmp_curbat as $_tcb) {
			$min_t = $_tcb ['start_begin'];
			if (!empty ($_tcb ['start_end'])) {//设置结束时间
				$max_t = $_tcb ['start_end'] + 86400;
				if ($_tcb ['enable'] == 1 && $today < $max_t && $today > $min_t) {
					$infoid = $_tcb ['sbid'];
				}
			} else {//未设置结束时间
				if ($_tcb ['enable'] == 1 && $today > $min_t) {
					$infoid = $_tcb ['sbid'];
				}
			}
		}

		return $infoid;
	}

	/**
	 * [initialize_same_data 初始化一些数据]
	 * @return [type] [description]
	 */
	public function initialize_same_data() {
		$timestamp = rstrtotime(rgmdate(startup_env::get('timestamp'), "Y-m-d H:i"));
		list ($cur_m, $cur_d, $cur_w) = explode(' ', rgmdate(startup_env::get('timestamp'), 'm d w'));

		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('cur_m', $cur_m);
		$this->view->set('cur_d', $cur_d);
		$this->view->set('cur_w', $cur_w);
		$this->view->set('timestamp', $timestamp);
	}


	// 获取配置
	public function get_remark() {
		$serv_sr = &service::factory('voa_s_oa_sign_detail');
		if (!empty($sr_id)) {
			$conds_detail ['sr_id in (?)'] = $sr_id;
			$sign_detail = $serv_sr->list_by_conds($conds_detail);
			if (!$sign_detail) {
				$sign_detail = array();
			}
		} else {
			$sign_detail = array();
		}

		return $sign_detail;
	}

	// 计算其他数据属性
	public function first_property($info) {
		$wo_days = unserialize($info['work_days']);
		$current_week = rgmdate(startup_env::get('timestamp'), 'w');
		$allow_sign = in_array($current_week, $wo_days);

		// 起始时间和结束时间
		$ymd = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		// 开始时间为工作时间前6小时
		$btime = rstrtotime($ymd . ' ' . $this->formattime($info ['work_begin'])) - 3600 * 6;
		// 结束时间为工作时间后9小时

		$work_end = $this->formattime($info ['work_end']);
		$work_e = substr($work_end, 0, 2);

		if ($work_e - 24 > 0) {
			$etime = $this->totime($ymd, $work_end) + 3600 * 9;
		} else {
			$etime = rstrtotime($ymd . ' ' . $this->formattime($info ['work_end'])) + 3600 * 9;
		}

		// 判断打卡设置
		$sb_set = $info ['sb_set'];
		// 默认读取当天的签到情况
		$records = $this->get_by_time(startup_env::get('wbs_uid'), $btime, $etime);

		// 数据过滤
		$fmt = &uda::factory('voa_uda_frontend_sign_format');
		$fmt->sign_record_list($records);

		$return_data['allow_sign'] = $allow_sign;
		$return_data['sb_set'] = $sb_set;
		$return_data['records'] = $records;

		return $return_data;
	}


	/**
	 * [up_down_work 获取上下班卡]
	 * @param  [type] $records [description]
	 * @return [type]          [description]
	 */
	public function up_down_work($records, $sb_set) {
		foreach ($records as $r) {
			if (voa_d_oa_sign_record::TYPE_ON == $r ['sr_type'] && in_array($sb_set, array(
					1,
					3
				))
			) {
				$on_signtime_hi = $r ['_signtime_hi'];
				$work_on = $r;
				//	$sr_id = $work_on ['sr_id'];
			} elseif (voa_d_oa_sign_record::TYPE_OFF == $r ['sr_type'] && in_array($sb_set, array(
					2,
					3
				))
			) {
				$off_signtime_hi = $r ['_signtime_hi'];
				$work_off = $r;
			}
		}

		$up_down_data['on_signtime_hi'] = isset($on_signtime_hi) ? $on_signtime_hi : array();
		$up_down_data['work_on'] = isset($work_on) ? $work_on : array();
		$up_down_data['off_signtime_hi'] = isset($off_signtime_hi) ? $off_signtime_hi : array();
		$up_down_data['work_off'] = isset($work_off) ? $work_off : array();

		return $up_down_data;
	}


	// 判断那种卡未打, 设置对应的时间
	public function is_no_get($work_on, $sb_set, $info, $on_signtime_hi, $off_signtime_hi) {
		if (empty ($work_on) && in_array($sb_set, array(
				1,
				3
			))
		) {
			$on_signtime_hi = rgmdate(startup_env::get('timestamp'), 'H:i');
		} elseif (empty ($work_off) && in_array($sb_set, array(
				2,
				3
			))
		) {
			$off_signtime_hi = rgmdate(startup_env::get('timestamp'), 'H:i');
		}

		// 取当前时间的月/日/周
		$p_set = array();
		$p_set ['work_begin_hi'] = $this->formattime($info ['work_begin']);
		$tmp_end = $this->formattime($info ['work_end']);

		if (substr($tmp_end, 0, 2) >= 24) {
			$stime = (substr($tmp_end, 0, 2) - 24) . substr($tmp_end, 3, 2);
			$tmp_end = '次日' . $this->formattime($stime);
		}
		$p_set ['work_end_hi'] = $tmp_end;

		$is_no_get['on_signtime_hi'] = $on_signtime_hi;
		$is_no_get['off_signtime_hi'] = $off_signtime_hi;
		$is_no_get['p_set'] = $p_set;

		return $is_no_get;
	}

	// 判断签到次数计算传过去的sr_id
	public function qian_pass($work_on, $work_off) {
		if (!empty($work_on)) {
			if (empty($work_off)) {
				$detail = $work_on['sr_id'];
				$sr_id[] = $work_on['sr_id'];
			} elseif (!empty($work_off)) {
				$detail = $work_off['sr_id'];
				$sr_id = array($work_on['sr_id'], $work_off['sr_id']);
			}

		} elseif (!empty($work_off)) {
			$detail = $work_off['sr_id'];
			$sr_id[] = $work_off['sr_id'];
		} else {
			$detail = '';
			$sr_id = array();
		}

		$re_data['detail'] = $detail;
		$re_data['sr_id'] = $sr_id;

		return $re_data;
	}

	// 备注方法
	public function beizhu($info, $work_on) {
		$si_on = 0;
		if (!empty($work_on)) {
			$si_on = 1;  // dd
		}
		//判断当前该打的卡
		if ($info['sb_set'] == 1) {
			$sign_type = 1; // dd
		} elseif ($info['sb_set'] == 2) {
			$sign_type = 2;
		} elseif ($info['sb_set'] == 3) {
			if (empty($work_on)) {
				$sign_type = 1;
			} else {
				$sign_type = 2;
			}
		}

		$re_data = array(
			'si_on' => $si_on,
			'sign_type' => $sign_type
		);

		return $re_data;
	}

}

