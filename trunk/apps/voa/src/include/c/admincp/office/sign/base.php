<?php

/**
 * voa_c_admincp_office_sign_base
 * 企业后台/微办公管理/签到考勤/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_base extends voa_c_admincp_office_base {
	/**
	 * 签到类型定义
	 */
	protected $_sign_type_set = array (
		'on' => voa_d_oa_sign_record::TYPE_ON,
		'off' => voa_d_oa_sign_record::TYPE_OFF
	);
	/**
	 * 签到类型文字描述
	 */
	protected $_sign_type = array (
		voa_d_oa_sign_record::TYPE_ON => '上班',
		voa_d_oa_sign_record::TYPE_OFF => '下班'
	);

	/**
	 * 签到状态定义
	 */
	protected $_sign_status_set = array (
		'unknown' => voa_d_oa_sign_record::STATUS_UNKNOWN,
		'work' => voa_d_oa_sign_record::STATUS_WORK,
		'late' => voa_d_oa_sign_record::STATUS_LATE,
		'leave' => voa_d_oa_sign_record::STATUS_LEAVE,
		/* 'absent' => voa_d_oa_sign_record::STATUS_ABSENT,
		'off' => voa_d_oa_sign_record::STATUS_OFF,
		'evection' => voa_d_oa_sign_record::STATUS_EVECTION,
		'remove' => voa_d_oa_sign_record::STATUS_REMOVE  */
	);
	/**
	 * 签到状态文字描述
	 */
	protected $_sign_status = array (
		voa_d_oa_sign_record::STATUS_UNKNOWN => '未知',
		voa_d_oa_sign_record::STATUS_WORK => '正常',
		voa_d_oa_sign_record::STATUS_LATE => '迟到',
		voa_d_oa_sign_record::STATUS_LEAVE => '早退',
		/* 	voa_d_oa_sign_record::STATUS_ABSENT => '旷工',
		   voa_d_oa_sign_record::STATUS_OFF => '请假',
		   voa_d_oa_sign_record::STATUS_EVECTION => '出差',
		   voa_d_oa_sign_record::STATUS_REMOVE => '删除' */
	);

	/**
	 * 申诉处理状态文字描述
	 */
	protected $_sign_plead_status = array (
		voa_d_oa_sign_plead::STATUS_UN_DONE => '未处理',
		voa_d_oa_sign_plead::STATUS_DONE => '已处理',
		voa_d_oa_sign_plead::STATUS_REMOVE => '已删除'
	);
	/**
	 * 申诉处理状态定义
	 */
	protected $_sign_plead_status_set = array (
		'un_done' => voa_d_oa_sign_plead::STATUS_UN_DONE,
		'done' => voa_d_oa_sign_plead::STATUS_DONE,
		'remove' => voa_d_oa_sign_plead::STATUS_REMOVE
	);

	/**
	 * 签到的设置配置
	 */
	protected $_sign_setting = array ();
	// 签到信息
	protected $_sign_base;
	// 班次对应信息
	public $batch_user = array ();
	/**
	 * 签到表操作
	 */
	protected $_serv_record = null;
	/**
	 * 地理位置上报表操作
	 */
	protected $_serv_location = null;
	/**
	 * 备注表操作
	 */
	protected $_serv_detail = null;

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		/**
		 * 获取签到的设置
		 */
		$this->_sign_setting = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');

		$navmenu = array ();
		$navmenu ['links'] = array ();
		$listUrl = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id);
		$navmenu ['links'] ['list'] = array (
			'icon' => 'fa-list',
			'url' => $listUrl,
			'name' => '签到记录'
		);

		$this->view->set('navmenu', $navmenu);

		$this->view->set('link_all', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array (
			'sr_type' => voa_d_oa_sign_record::TYPE_ALL
		)));
		$this->view->set('link_work_on', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array (
			'issearch' => 1,
			'sr_type' => voa_d_oa_sign_record::TYPE_ON
		)));
		$this->view->set('link_work_off', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id, array (
			'issearch' => 1,
			'sr_type' => voa_d_oa_sign_record::TYPE_OFF
		)));
		$this->view->set('link_upposition', $this->cpurl($this->_module, $this->_operation, 'upposition', $this->_module_plugin_id));

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);

		return true;
	}

	/**
	 * 获取所有人信息和所在的部门
	 * @return mixed
	 */
	protected function _get_all_member_department($m_uids) {

		// 人物表
		$serv_mem = &service::factory('voa_s_oa_member');
		// 人跟部门关联表
		$serv_department = &service::factory('voa_s_oa_member_department');

		$member_list = $serv_mem->fetch_all();
		$member_department = $serv_department->fetch_all();

		// 匹配人员信息
		$list = array();
		foreach ($m_uids as $key => $val) {
			foreach ($member_list as $_key => $_val) {
				if ($val == $_val['m_uid']) {
					$list[$val] = $member_list[$_key];
				}
			}
		}

		// 匹配人和部门
		foreach ($list as $k => &$v) {
			$v['cd_ids'] = array (); // 默认一个关联部门空数组
			foreach ($member_department as $_k => $_v) {
				if ($v['m_uid'] == $_v['m_uid']) {
					$v['cd_ids'][] = $_v['cd_id'];
				}
			}
			if (empty($v['cd_ids'])) {
				unset($list[$k]);
			}
		}

		return $list;
	}

	/**
	 * 获取部门关联的排班信息(开启的有班次信息
	 * @return mixed
	 */
	protected function _get_department_batch() {

		// 获取启用的班次表
		$serv_batch = &service::factory('voa_s_oa_sign_batch');
		$batch_data = $serv_batch->list_by_conds(array ('enable' => 1));
		// 判断班次是否过期
		foreach ($batch_data as $k => $v) {
			if (isset($v['start_end']) && $v['start_end'] != 0 && (int)$v['start_end'] < startup_env::get('timestamp')) {
				unset($batch_data[$k]);
			}
		}

		// 获取部门 班次关联表
		$serv_depart = &service::factory('voa_s_oa_sign_department');
		$department_batch = $serv_depart->list_all();

		// 获取部门关联表的 班次工作日信息
		$department_batch_result = array ();
		if (!$department_batch) return $department_batch_result;
		foreach ($department_batch as $k => &$v) {
			foreach ($batch_data as $_k => $_v) {
				// 匹配成功 班次信息赋值给关联数组
				if ($v['sbid'] == $_v['sbid'] && !empty($_v)) {
					$v['works'] = $_v;
				}
			}
			// 成功匹配班次的以部门ID为键值 班次信息为值 赋值新数组
			if (isset($v['works'])) {
				$department_batch_result[$v['department']] = $v;
			}
		}

		return $department_batch_result;
	}

	/**
	 * 匹配人员班次
	 * @param $m_uids 人员id
	 * @return mixed
	 */
	public function get_member_batch($m_uids) {

		//人员部门数组$mem 可用班次部门数组 $bdepartment
		$mem = $this->_get_all_member_department($m_uids);
        //  获取部门关联的排班信息
		$bdepartment = $this->_get_department_batch($m_uids);
		foreach ($mem as &$_mem) {
			$m_uid = $_mem['m_uid'];
			foreach ($_mem['cd_ids'] as $_cd_ids) {
				//当前部门是否在可用部门班次中
				if (isset($bdepartment[$_cd_ids])) {
					$_mem['batch'][$_cd_ids] = $bdepartment[$_cd_ids]['sbid'];
				} else {//查上级部门是否有班次
					$cdid = $_cd_ids;
					while (!isset($bdepartment[$cdid])) {
						$cdid = $this->get_upid($cdid);
						if (empty($cdid)) {
							break;
						}
					}
					//如果都没有班次
					if ($cdid == 0) {
						$_mem['batch'][$_cd_ids] = '0';
					} else {
						$_mem['batch'][$_cd_ids] = $bdepartment[$cdid]['sbid'];
					}
				}
			}
			unset($m_uid);
		}

		return $mem;
	}

	/**
	 * 当前部门是否在可用部门班次中
	 * @param unknown $bdepartment
	 * @param unknown $mem
	 * @param unknown $_cd_ids
	 */
	private function __get_ablebatch($bdepartment, $mem, $_cd_ids){

		if ( isset( $bdepartment[ $_cd_ids ] ) ) {
			$_mem['batch'][ $_cd_ids ] = $bdepartment[ $_cd_ids ]['sbid'];
		} else {//查上级部门是否有班次
			$cdids = $_cd_ids;
			while ( ! isset( $bdepartment[ $cdids ] ) ) {
				if ( $cdids == 0 ) {//遍历到顶级部门
					$cdids = 0;
					break;
				}
				$cdids = $this->get_upid( $cdids );
			}
			//如果都没有班次
			if ( $cdids == 0 ) {
				$_mem['batch'][ $_cd_ids ] = '0';
			} else {
				$_mem['batch'][ $_cd_ids ] = $bdepartment[ $cdids ]['sbid'];
			}
		}
		return $_mem['batch'][$_cd_ids] ;
	}

	/**
	 * 格式化签到信息数据
	 * @param array $record
	 * @return array
	 */
	protected function _format_sign_record($record = array ()) {

		//格式部门数据
		//部门列表
		$cache_department = voa_h_cache::get_instance()->get('department', 'oa');
        if($record['cd_id'] == 0){
            $record['cd_name'] = '全公司';
        }else{
            $record['cd_name'] = $cache_department[$record['cd_id']]['cd_name'];
        }
		$record ['_current'] = $record ['sr_signtime'];
		$record ['_signtime'] = rgmdate($record ['sr_signtime'], 'Y-m-d H:i');
		$record ['_signdate'] = rgmdate($record ['sr_signtime'], 'Y-m-d');
		$record ['_type'] = isset ($this->_sign_type [$record ['sr_type']]) ? $this->_sign_type [$record ['sr_type']] : '';
		$record ['_sign'] = isset ($this->_sign_status [$record ['sr_sign']]) ? $this->_sign_status [$record ['sr_sign']] : '';
		$record ['_updated'] = rgmdate(($record ['sr_created'] ? $record ['sr_created'] : $record ['sr_updated']), 'Y-m-d H:i');

		return $record;
	}

	/**
	 * 获取指定的签到记录信息
	 * @param number $sr_id
	 * @return array
	 */
	/* 	protected function _get_sign_record($sr_id) {
			$sign_record = $this->_serv_record->fetch_by_id ( $sr_id );
			if (empty ( $sign_record )) {
				return array ();
			}
			return self::_format_sign_record ( $sign_record );
		} */

	/**
	 * 获取指定的签到备注信息
	 * @param number $sr_id
	 * @return array
	 */
	protected function _get_sign_detail($sr_id) {
		$details = $this->_serv_detail->fetch_all_by_sr_id($sr_id);
		if (empty ($details)) {
			return array ();
		}

		return $details;
	}

	/**
	 * 格式化重设备注信息
	 * @param array $detail
	 * @return array
	 */
	protected function _format_sign_detail($detail) {
		$detail ['_updated'] = rgmdate($detail ['sd_updated'] ? $detail ['sd_updated'] : $detail ['sd_created'], 'Y-m-d H:i');
		$detail ['_reason'] = $this->_bbcode2html($detail ['sd_reason']);

		return $detail;
	}

	/**
	 * 格式化申诉字段数据
	 * @param array $plead
	 * @return array
	 */
	protected function _format_sign_plead($plead) {
		$plead ['_subject'] = rsubstr($this->_bbcode2html($plead ['sp_subject']), 80, ' ...');
		$plead ['_message'] = rsubstr($this->_bbcode2html($plead ['sp_message']), 80, ' ...');
		$plead ['_date'] = $plead ['sp_year'] . '年' . $plead ['sp_month'] . '月';
		$plead ['_created'] = rgmdate($plead ['sp_created'], 'Y-m-d H:i');
		$plead ['_updated'] = $plead ['sp_updated'] ? rgmdate($plead ['sp_updated'], 'Y-m-d H:i') : '';
		$plead ['_status'] = isset ($this->_sign_plead_status [$plead ['sp_status']]) ? $this->_sign_plead_status [$plead ['sp_status']] : '';

		return $plead;
	}

	/**
	 * 获取指定位置的上报记录
	 * @param number $sl_id
	 * @return array
	 */
	/* 	protected function _get_sign_location($sl_id) {
			$location = $this->_serv_location->fetch_by_id ( $sl_id );
			if (empty ( $location )) {
				return array ();
			}
			return self::_format_sign_location ( $location );
		} */

	/**
	 * 格式化上报信息数据
	 * @param array $record
	 * @return array
	 */
	protected function _format_sign_location($signLocation = array ()) {
		$signLocation ['_current'] = $signLocation ['sl_signtime'];
		$signLocation ['_signtime'] = rgmdate($signLocation ['sl_signtime'], 'Y-m-d H:i');
		$signLocation ['_signdate'] = rgmdate($signLocation ['sl_signtime'], 'Y-m-d');
		$signLocation ['_updated'] = rgmdate(($signLocation ['sl_created'] ? $signLocation ['sl_created'] : $signLocation ['sl_updated']), 'Y-m-d H:i');

		return $signLocation;
	}

	/**
	 * 获取指定用户一段时间的签到记录
	 * @param number $uid
	 * @param number $btime
	 * @param number $etime
	 * @return boolean|array
	 */
	/* protected function _get_sign_record_by_uid_time($uid, $btime, $etime) {
		$serv_sr = &service::factory ( 'voa_s_oa_sign_record', array (
				'pluginid' => startup_env::get ( 'pluginid' ) 
		) );
		$signRecord = $serv_sr->fetch_by_uid_time ( $uid, $btime, $etime );
		if (empty ( $signRecord )) {
			return false;
		}
		return $signRecord;
	} */
	/**
	 * 格式数字为时间
	 * @param unknown $num
	 * @return string
	 */
	public function formattime($num) {
		if (strlen($num) == 0) {
			$time = '00:00';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '00:0' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00:' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . ':' . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . ':' . $min;

			return $time;
		}
	}

	/**
	 * 格式数字补为四位
	 * @param unknown $num
	 * @return string|unknown
	 */
	public function formatnum($num) {
		if (strlen($num) == 0) {
			$time = '0000';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '000' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . $min;

			return $num;
		}
	}

	/**
	 * 获取当前上班迟到时间
	 * @param int $ts 打卡时间戳
	 */
	public function on_status($ts, $work_begin, $late_range) {
		$late = 0;
		$late_range = $late_range ['ss_value'];
		$work_begin = $this->_to_seconds($this->formattime($work_begin));
		$remainder = $this->_to_seconds(rgmdate($ts, 'H:i'));
		if ($remainder > $work_begin + $late_range) {
			$late = $remainder - ($work_begin + $late_range);
			$late = $this->_to_minute($late);
		}

		return $late;
	}

	/**
	 * 获取当前下班早退时间
	 * @param int $ts 打卡时间戳
	 */
	public function off_status($ts, $work_end, $leave_early_range) {
		$leave_early_range = $leave_early_range ['ss_value'];
		$early = 0;
		$work_end = $this->_to_seconds($this->formattime($work_end));
		$remainder = $this->_to_seconds(rgmdate($ts, 'H:i'));
		if ($remainder < ($work_end - $leave_early_range)) {
			$early = $work_end - $leave_early_range - $remainder;
			$early = $this->_to_minute($early);
		}

		return $early;
	}

	/**
	 * 把时间转成对应的秒数
	 */
	protected function _to_seconds($hi) {
		$a = explode(':', $hi);
		@list ($h, $i) = explode(':', $hi);

		return $h * 3600 + $i * 60;
	}

	/**
	 * 把秒转化为分钟*
	 */
	public function _to_minute($seconds) {
		$minute = ceil($seconds / 60);

		return $minute;
	}

	/**
	 * 获取上级部门id
	 * @param unknown $cd_id
	 * @return unknown
	 */
	public function get_upid($cd_id) {

		static $deplist = null;

		if ($deplist === null) {
			$deplist = voa_h_cache::get_instance()->get('department', 'oa');
		}

		$upid = isset($deplist[$cd_id]) ? $deplist[$cd_id]['cd_upid'] : 0;

		return $upid;
	}

	/**
	 * 打印
	 * @param unknown $list
	 * @return mixed
	 */
	public function print_r($list) {
		echo '<pre>';

		return print_r($list);
		die;
	}
	/**
	 * 获取当前月第一天日期和最后一天日期
	 * @return multitype:string
	 */
	public function get_m_day() {
		// 获取时间月份
		$begin_d = date('Y-m-01', strtotime(date("Y-m-d")));

		$end_d = date('Y-m-d', strtotime("$begin_d +1 month -1 day"));
		return array($begin_d, $end_d);
	}
}
