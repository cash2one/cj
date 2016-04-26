<?php
/**
 * 获取用户的班次ID
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/9/11
 * Time: 上午10:00
 */

class voa_c_api_sign_get_batch extends voa_c_api_sign_base {
	// 需要使用的S层
	protected $_serv_member = null;
	protected $_serv_department = null;
	protected $_serv_batch = null;
	// 用户ID
	protected $_m_uid = null;

	public function execute() {
		// 实例化需要的S层
		$this->_serv_member = &service::factory('voa_s_oa_member_department');
		$this->_serv_department = &service::factory('voa_s_oa_sign_department');
		$this->_serv_batch = &service::factory('voa_s_oa_sign_batch');

		// 获取人物 相关信息
		if (!$this->__get_mem_info($dep)) {
			return false;
		};

		// 获取班次信息
		if (!$this->__get_batch_info($dep)) {
			return false;
		};
		$info = $this->__get_batch_info($dep);

		// 如果用户 有多班次 那么提供选择
		if (isset($info['batchlist'])) {
			$this->_result = $info;
			return $this->_result;
		}
	}

	/**
	 * 获m_uid 和 所在的部门
	 * @param $dep 部门ID
	 * @return bool
	 */
	private function __get_mem_info(&$dep) {
		// 获取当前人物ID
		$this->_m_uid = startup_env::get('wbs_uid');
		$conds_mem['m_uid'] = $this->_m_uid;
		if (empty($conds_mem['m_uid'])) {
			$this->_errcode = '10000';
			$this->_errmsg = '丢失用户ID数据';

			return false;
		}
		// 获取当前人物信息
		$userinfo = $this->_serv_member->fetch_all_by_conditions($conds_mem);
		// 获取所有当前用户所在的各个部门
		$dep = array();
		foreach ($userinfo as $_uinfo) {
			$dep[] = $_uinfo['cd_id'];
		}

		return true;
	}

	/**
	 * 获取班次信息
	 * @param $dep
	 * @return bool
	 */
	private function __get_batch_info($dep) {
		// 用户所在部门数量大于1
		if (count($dep) >= 2) {
			if (!$info = $this->gt_two_department($dep)) {
				return false;
			};
		} else {
			// 只有一个部门的情况
			if (!$info = $this->just_one_department($dep)) {
				return false;
			};
		}

		return $info;
	}

	/**
	 * 当用户所在部门大于等于2个
	 * @param $dep
	 * @return bool
	 */
	public function gt_two_department($dep) {
		// 查班次部门表里对应部门的班次
		$conds_balist['department IN (?)'] = $dep;
		$sign_bat = $this->_serv_department->list_by_conds($conds_balist);

		// 所有的班次
		$all_batch = array();
		if (!empty($sign_bat)) {
			// 把对应的班次ID放入新数组
			foreach ($sign_bat as $_sib) {
				$all_batch[] = $_sib['sbid'];
			}
		}

		// 根据条件查班次
		$conds_lb['sbid IN (?)'] = $all_batch;
		$list_bat = $this->_serv_batch->list_by_conds($conds_lb);

		// 取可用的班次
		$enable_batlist = array();
		if (!empty($enable_batlist)) {
			foreach ($list_bat as $_enbat) {
				if ($_enbat['enable'] == 1) {
					$enable_batlist['sbid'] = $_enbat['name'];
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
					$uplis[] = $upid;
				}
			}

			// 所有部门的班次
			$conds_upbat['department IN (?)'] = $uplis;
			$upbatch = $this->_serv_department->list_by_conds($conds_upbat);
			$upbalist = array();
			if (!empty($upbatch)) {
				foreach ($upbatch as $_upb) {
					$upbalist[] = $_upb['sbid'];
				}
			}
			$conds_dd['sbid IN (?)'] = $upbalist;
			$upbat_list = $this->_serv_batch->list_by_conds($conds_dd);
			$enable = array();
			// 未禁用班次
			$today = startup_env::get('timestamp');
			if (!empty($upbat_list)) {
				foreach ($upbat_list as $_enable) {
					$min_t = $_enable['start_begin'];
					if (!empty($_enable['start_end'])) {
						//设置了结束时间
						$max_t = $_enable['start_end'] + 86400;
						if ($_enable['enable'] == 1 && $today < $max_t && $today > $min_t) {
							$enable[$_enable['sbid']] = $_enable['name'];
						}
					} else {//未设置结束时间
						if ($_enable['enable'] == 1 && $today > $min_t) {
							$enable[$_enable ['sbid']] = $_enable['name'];
						}
					}
				}
			}
		}

		// 返回 班次 列表
		if (!empty($enable)) {
			$info['batchlist'] = $enable;
			return $info;
		} else {//错误提示
			$this->_errcode = '10001';
			$this->_errmsg = '没有可用的班次';
			return false;
		}
	}

	/**
	 * 只有一个部门的情况
	 * @param $dep
	 * @return mixed
	 */
	public function just_one_department($dep) {
		//用户只有一个部门情况
		$conds['department'] = $dep [0];
		$deplist = $this->_serv_department->list_by_conds($conds);
		//获取部门对应有效班次
		if (!empty ($deplist)) {
			$infoid = $this->__get_department_batch($deplist);
		}

		// 当前部门没有可用班次从上级部门找对应班次
		if (empty($deplist) || empty($infoid)) {
			$uplist = array(
				$dep[0]
			);
			$upid = $dep[0];
			// 获取所有上级部门
			while (!in_array(0, $uplist)) {
				$upid = $this->get_upid($upid);
				$uplist[] = $upid;
			}
			$conds_upbat['department IN (?)'] = $uplist;
			// 所有部门的班次
			$upbatch = $this->_serv_department->list_by_conds($conds_upbat);
			$upbalist = array();
			if (!empty($upbatch)) {
				foreach ($upbatch as $_upb) {
					$upbalist[] = $_upb ['sbid'];
				}
			} else {
				$this->_errcode = '10003';
				$this->_errmsg = '没有可用的班次';

				return false;
			}
			$conds_dd['sbid IN (?)'] = $upbalist;
			$upbat_list = $this->_serv_batch->list_by_conds($conds_dd);
			$enable = array();
			// 未禁用班次
			$today = startup_env::get('timestamp');
			foreach ($upbat_list as $_enable) {
				$min_t = $_enable['start_begin'];
				if (!empty ($_enable['start_end'])) {
					//设置了结束时间
					$max_t = $_enable['start_end'] + 86400;
					if ($_enable['enable'] == 1 && $today < $max_t && $today > $min_t) {
						$enable[] = $_enable;
					}
				} else {//未设置结束时间
					if ($_enable['enable'] == 1 && $today > $min_t) {
						$enable[] = $_enable;
					}
				}
			}

			if (empty($enable)) { // 所有上级部门都没有班次
				$this->_errcode = '10002';
				$this->_errmsg = '没有可用的班次';

				return false;
			} elseif (count($enable) == 1) { // 有一个上级部门有班次
				$info = reset($enable);
			} else { // 如果上级部门班次有多个
				$tmp_sbid = array();
				foreach ($enable as $_sim) {
					$tmp_sbid[] = $_sim ['sbid'];
				}
				// 有的上级没有班次
				$conds_sim['sbid IN (?)'] = $tmp_sbid;
				$ublist = $this->_serv_department->list_by_conds($conds_sim);
				$min = array();
				// 得到所有的有班次的上级
				foreach ($ublist as $u_d) {
					if (in_array($u_d['department'], $uplist)) {
						$min[] = $u_d['department'];
					}
				}

				$diff = array();
				//遍历存储每个上级部门的数量
				foreach ($min as &$_mi) {
					$min_uplist = array();
					$m_upid = $_mi;
					while (!in_array(0, $min_uplist)) {
						$m_upid = $this->get_upid($m_upid);
						$min_uplist[] = $m_upid;
					}
					//清除本次循环变量
					unset($cdid);
					$diff[$_mi] = count($min_uplist);
					unset($min_uplist);
				}

				//获取上级部门的班次id
				$endbatchid = $this->__get_updepartment_batch($diff, $_enable);

				if ($endbatchid) {
					$info = $this->_serv_batch->get($endbatchid);
				} else {
					$this->_errcode = '10003';
					$this->_errmsg = '没有可用的班次';

					return false;
				}
			}
		} else { // 当前部门有班次
			$batlist = array();
			foreach ($deplist as $val) {
				$batlist[] = $val['sbid'];
			}
			$conds_bat['sbid IN (?)'] = $batlist;
			$batinfo = $this->_serv_batch->list_by_conds($conds_bat);
			foreach ($batinfo as $_info) {
				if ($_info ['enable'] == 1) {
					$info = $_info;
				}
			}
		}

		return array(
			'batchlist' => array(
				$info['sbid'] => $info['name']
			)
		);
	}

	/**
	 * 获取部门对应有效班次
	 * @param $deplist
	 * @return mixed
	 */
	private function __get_department_batch($deplist) {
		//获取部门关联班次
		$tmp_bh = array();
		foreach ($deplist as $_cubid) {
			$tmp_bh[] = $_cubid['sbid'];
		}
		$conds_curbat['sbid IN (?)'] = $tmp_bh;
		$tmp_curbat = $this->_serv_batch->list_by_conds($conds_curbat);
		$today = startup_env::get('timestamp');
		//判断是否可用，启用，结束时间是否符合
		foreach ($tmp_curbat as $_tcb) {
			$min_t = $_tcb['start_begin'];
			if (!empty ($_tcb['start_end'])) {//设置结束时间
				$max_t = $_tcb['start_end'] + 86400;
				if ($_tcb['enable'] == 1 && $today < $max_t && $today > $min_t) {
					$infoid = $_tcb['sbid'];
				}
			} else {//未设置结束时间
				if ($_tcb['enable'] == 1 && $today > $min_t) {
					$infoid = $_tcb['sbid'];
				}
			}
		}

		return $infoid;
	}

	/**
	 * 获取上级部门的班次
	 * @param $diff
	 * @param $_enable
	 * @return string
	 */
	private function __get_updepartment_batch($diff, $_enable) {
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
			$conds_upmin['department'] = $uupid;
			$minlist = $this->_serv_department->list_by_conds($conds_upmin);
			$minlistid = array();
			foreach ($minlist as $_blid) {
				$minlistid[] = $_blid['sbid'];
			}

			$conds_subid['sbid IN (?)'] = $minlistid;
			$endlist = $this->_serv_batch->list_by_conds($conds_subid);
			$today = startup_env::get('timestamp');
			//筛选可用的班次
			foreach ($endlist as $_endb) {
				$min_t = $_endb['start_begin'];
				if (!empty ($_endb['start_end'])) {
					$max_t = $_enable['start_end'] + 86400;
					if ($_endb['enable'] == 1 && $today < $max_t && $today > $min_t) {
						$endbatchid = $_endb['sbid'];
					}
				} else {//未设置结束时间
					if ($_endb ['enable'] == 1 && $today > $min_t) {
						$endbatchid = $_endb['sbid'];
					}
				}
			}
			if (!empty ($diff)) {
				foreach ($diff as $_dif) {
					if ($_dif != $val) {
						$newdiff[] = $_dif;
					}
				}
			}
		}

		return $endbatchid;
	}
}
