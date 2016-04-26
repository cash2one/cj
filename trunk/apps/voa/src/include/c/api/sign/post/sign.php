<?php

/**
 * 签到及上报操作
 * $Author$
 * $Id$
 */
class voa_c_api_sign_post_sign extends voa_c_api_sign_base {

	public function execute() {
		// 验证数据
		if (!$this->__execute()) {
			return false;
		}

		//验证班次信息
		$serv_member = &Service::factory('voa_s_oa_member_department');
		$conds['m_uid'] = startup_env::get('wbs_uid');
		$deplist = $serv_member->fetch_all_by_conditions($conds);
		$serv_batch = &service::factory('voa_s_oa_sign_batch');

		$dlist = array ();
		foreach ($deplist as $_dep) {
			$dlist[] = $_dep['cd_id'];
		}
		//获取所有上级部门id
		$uplist = $this->get_up_department($dlist);

		// 检测班次的合法性
		$this->check_sbid($uplist);

		//签到返回数据
		$record = $this->return_mark_data($serv_batch);

		// 构造返回值 
		$this->_result = array (
			'id' => $record['sr_id'],
			'signtime' => $record['sr_signtime'],
			'ip' => $record['sr_ip'],
			'type' => $record['sr_type'],
			'longitude' => $record['sr_longitude'],
			'latitude' => $record['sr_latitude'],
			'address' => $record['sr_address'],
			'time' => rgmdate(startup_env::get('timestamp'), 'H:i')
		);

		return true;
	}

	/**
	 * 地理位置签到
	 * @param $location
	 * @param $info
	 * @param $record
	 * @return bool
	 */
	private function __sign($location, $info, &$record) {
		$sign = new voa_sign_handle();
		$record = array (
			'sr_longitude' => $this->_params['longitude'],
			'sr_latitude' => $this->_params['latitude'],
			'sr_address' => !empty($location['address']) ? $location['address'] : '',
			'sr_type' => $this->_params['type']
		);

		$overrange = '0';
		//地理位置签到
		if (!$sign->sign($overrange, $record, $this->_member, voa_sign_handle::TYPE_LOCATION, $location, $info)) {

			if ($sign->error) {
				$this->_set_errcode($sign->error);
			} else {
				$ac = '操作';
				if ($record) {
					$ac = voa_d_oa_sign_record::TYPE_ON == $record['sr_type'] ? '签到' : (voa_d_oa_sign_record::TYPE_OFF == $record['sr_type'] ? '签退' : '上报地理位置');
				}
				$this->_set_errcode(voa_errcode_api_sign::SIGN_FAILED, $ac);
			}

			return false;
		}

		// 如果返回了空值
		if (empty($record)) {
			$this->_set_errcode(voa_errcode_api_sign::SIGN_DUPLICATE);

			return false;
		}
		//超出范围
		if ($overrange == 1) {
			$this->_set_errcode(voa_errcode_oa_sign::OVER_SIGN_RANGE);

			return false;
		}

		return true;
	}

	/**
	 * 获取位置信息
	 * @param $location
	 * @return bool
	 */
	private function __get_address(&$location) {
		$location = array ();
		if (empty($this->_params['longitude']) && empty($this->_params['longitude'])) {
			if (!$this->__get_address_by_ip($location)) {
				return false;
			}
		} else {
			if (!$this->_get_address($this->_params['longitude'], $this->_params['latitude'], $location)) {
				if (!$this->__get_address_by_ip($location)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * 根据id获取班次
	 * @param $serv_batch
	 * @param $batlist
	 * @param $batidlist
	 * @return bool
	 */
	private function __id_get_batch($serv_batch, $batlist, &$batidlist) {
		$batidlist = array ();
		$conds_endbat['sbid IN (?)'] = $batlist;
		$batlist = $serv_batch->list_by_conds($conds_endbat);

		foreach ($batlist as $_batli) {
			if ($_batli['enable'] == 1) {
				$batidlist[] = $_batli['sbid'];
			}
		}

		return true;
	}

	/**
	 * 验证数据
	 * @return bool
	 */
	private function __execute() {
		// 接受的参数
		$fields = array (
			'longitude' => array ('type' => 'string', 'required' => true),// 经度
			'latitude' => array ('type' => 'string', 'required' => true),// 纬度
			'sbid' => array ('type' => 'int', 'required' => true),// 班次ID
			'type' => array ('type' => 'int', 'required' => true)// 1=签到 or 2=签退
		);

		// 基本变量检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		return true;
	}

	/**
	 * 通过IP地址获取位置信息
	 * @param array $location
	 * @return boolean
	 */
	private function __get_address_by_ip(array &$location) {

		$ip = controller_request::get_instance()->get_client_ip();
		$ip2address = new ip2address();
		if (!$ip2address->get($ip)) {
			$ip2address->result['address'] = '无法获取地理位置';
			//$this->_errcode = $ip2address->errcode;
			//$this->_errmsg = $ip2address->errmsg;
			//return false;
		}

		$location = array (
			'longitude' => 0,
			'latitude' => 0,
			'address' => $ip2address->result['address']
		);

		return true;
	}

	/**
	 * [get_up_department 获取所有的上级部门]
	 * @param  [type] $dlist [description]
	 * @return [type]        [description]
	 */
	public function get_up_department($dlist) {
		$uplist = $dlist;

		foreach ($dlist as $_de) {
			$upid = $_de;
			// 获取所有上级部门
			while (!in_array(0, $uplist)) {
				$upid = $this->get_upid($upid);
				$uplist [] = $upid;
			}
			unset($upid);
		}

		return $uplist;
	}


	/**
	 * [check_sbid 检测班次的合法性]
	 * @return [type] [description]
	 */
	public function check_sbid($uplist) {
		//部门对应班次
		$serv_batdep = &Service::factory('voa_s_oa_sign_department');
		$conds_bat['department IN (?)'] = $uplist; // 11 9 0
		$bdepartment = $serv_batdep->list_by_conds($conds_bat);

		$batlist = array ();
		foreach ($bdepartment as $_bat) {
			$batlist[] = $_bat['sbid'];
		}

		//根据id查询班次
		$serv_batch = &service::factory('voa_s_oa_sign_batch');
		$batidlist = array ();
		$this->__id_get_batch($serv_batch, $batlist, $batidlist);

		//在权限班次数组内则合法
		if (!in_array($this->_params['sbid'], $batidlist)) {
			$this->_set_errcode(voa_errcode_oa_sign::BATCH_UNDEFINED);

			return false;
		}
	}


	// 签到返回数据
	public function return_mark_data($serv_batch) {
		//查询该班次
		$sbid = $this->_params['sbid'];
		$info = $serv_batch->get($sbid);
		$work_days = unserialize($info['work_days']);
		// 不是工作日不需要签到签退
		$current_week = rgmdate(startup_env::get('timestamp'), 'w'); // 获取当前是礼拜几
		if (!in_array($current_week, $work_days)) {
			return $this->_set_errcode(voa_errcode_oa_sign::IS_NOT_WORK_DAY);
		}
		// 获取经纬度
		if (!$this->_get_location($this->_params['longitude'], $this->_params['latitude'])) {
			$this->_params['longitude'] = '';
			$this->_params['latitude'] = '';
		}

		$location = array ();
		// 获取位置信息
		if (!$this->__get_address($location)) {
			return false;
		};
		// 地理位置签到
		if (!$this->__sign($location, $info, $record)) {
			// var_dump($record);die;
			//return false;
			return $record;
		};

		// var_dump($record);die;	
		return $record;

	}

}
