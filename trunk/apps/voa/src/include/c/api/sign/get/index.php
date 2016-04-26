<?php

/**
 * 打卡首页
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/9/9
 * Time: 下午2:16
 */
class voa_c_api_sign_get_index extends voa_c_api_sign_base {
	// 需要使用的S层
	protected $_serv_member = null;
	protected $_serv_department = null;
	protected $_serv_batch = null;
	/* 当前用户ID */
	protected $_m_uid = null;

	public function execute() {
		// 实例化需要的S层
		$this->_serv_member = &service::factory('voa_s_oa_member_department');
		$this->_serv_department = &service::factory('voa_s_oa_sign_department');
		$this->_serv_batch = &service::factory('voa_s_oa_sign_batch');

		// 获取班次信息
		if (!$this->__get_batch_info()) {
			return false;
		};
		$info = $this->__get_batch_info();

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

			//判断签到次数计算传过去的sr_id
			$re_data = $this->qian_pass($work_on, $work_off);
			$detail = $re_data['detail'];
			$sr_id = $re_data['sr_id'];

			// 备注
			$sign_detail = $this->get_remark($sr_id);

			// 跳到备注的方法
			$beizhu = $this->beizhu($info, $work_on);
			$si_on = $beizhu['si_on'];
			$sign_type = $beizhu['sign_type'];

			$this->_result = array(
				'allow_sign' => $allow_sign, // 判断当前打什么卡 1: 上班卡 2: 下班卡
				'sign_detail' => $sign_detail, // 备注列表
				'detail' => $detail, // 签到记录ID 判断是否显示添加 备注按钮
				'p_set' => $p_set, // 打卡开始/结束时间
				'work_on' => $work_on, // 上班的签到记录
				'work_off' => $work_off, // 下班的签到记录
				'sign_type' => $sign_type, // 当前的签到类型; 1:打完上班卡,应该打下班卡 2:打下班卡 给签到接口的数据
				'on_signtime_hi' => $on_signtime_hi, // 上班签到按钮上面的时间
				'off_signtime_hi' => $off_signtime_hi, // 下班签到按钮上面的时间
				'sbid' => $info['sbid'] //
		);
		}

		// 获取微信js api调用签名信息
		$this->_get_jsapi("['getLocation']");


		return $this->_result;
	}

	/**
	 * 获m_uid 和 所在的部门
	 * @param $dep 部门ID
	 * @return bool
	 */
	private function __get_mem_info(&$dep) {
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
	private function __get_batch_info() {
		$get = $this->request->getx();
		if (!empty(trim($get['batchid']))) {
			$batchid = $get['batchid'];
		} else {
			$this->_errcode = '20000';
			$this->_errmsg = '缺少必要参数:batchid';
			return false;
		}

		//用户选择了班次
		$info = $this->_serv_batch->get($batchid);

		return $info;
	}

	/**
	 * 计算其他数据属性
	 * @param $info
	 * @return mixed
	 */
	public function first_property($info) {
		$wo_days = unserialize($info['work_days']);
		$current_week = rgmdate(startup_env::get('timestamp'), 'w');
		$allow_sign = in_array($current_week, $wo_days) ? 1 : 2;

		// 起始时间和结束时间
		$ymd = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		// 开始时间为工作时间前6小时
		$btime = rstrtotime($ymd . ' ' . $this->formattime($info['work_begin'])) - 3600 * 6;
		// 结束时间为工作时间后9小时

		$work_end = $this->formattime($info['work_end']);
		$work_e = substr($work_end, 0, 2);

		if ($work_e - 24 > 0) {
			$etime = $this->totime($ymd, $work_end) + 3600 * 9;
		} else {
			$etime = rstrtotime($ymd . ' ' . $this->formattime($info['work_end'])) + 3600 * 9;
		}

		// 判断打卡设置
		$sb_set = $info['sb_set'];
		// 默认读取当天的签到情况
		$records = $this->get_by_time($btime, $etime);

		// 数据过滤
		$fmt = &uda::factory('voa_uda_frontend_sign_format');
		$fmt->sign_record_list($records);

		$return_data['allow_sign'] = $allow_sign;
		$return_data['sb_set'] = $sb_set;
		$return_data['records'] = $records;

		return $return_data;
	}

	/**
	 * 更具时间获取签到记录
	 * @param $btime
	 * @param $etime
	 * @return array
	 */
	public function get_by_time($btime, $etime) {
		$serv = &service::factory('voa_s_oa_sign_record');
		$conds['sr_signtime >= ?'] = $btime;
		$conds['sr_signtime <= ?'] = $etime;
		$conds['m_uid'] = $this->_m_uid;
		$records = $serv->list_by_conds($conds);
		if (!$records) {
			$records = array();
		}

		return $records;
	}

	/**
	 * 获取上下班卡
	 * @param $records
	 * @param $sb_set
	 * @return mixed
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

	/**
	 * 判断那种卡未打, 设置对应的时间
	 * @param $work_on
	 * @param $sb_set
	 * @param $info
	 * @param $on_signtime_hi
	 * @param $off_signtime_hi
	 * @return mixed
	 */
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
		$tmp_end = $this->formattime($info['work_end']);

		if (substr($tmp_end, 0, 2) >= 24) {
			$stime = (substr($tmp_end, 0, 2) - 24) . substr($tmp_end, 3, 2);
			$tmp_end = '次日' . $this->formattime($stime);
		}
		$p_set['work_end_hi'] = $tmp_end;

		$is_no_get['on_signtime_hi'] = $on_signtime_hi;
		$is_no_get['off_signtime_hi'] = $off_signtime_hi;
		$is_no_get['p_set'] = $p_set;

		return $is_no_get;
	}

	/**
	 * 格式数字为时间
	 * @param $num
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
	 * 判断签到次数计算传过去的sr_id
	 * @param $work_on
	 * @param $work_off
	 * @return mixed
	 */
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

	/**
	 * 获取备注
	 * @param $sr_id
	 * @return array
	 */
	public function get_remark($sr_id) {
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

	/**
	 * 备注方法
	 * @param $info
	 * @param $work_on
	 * @return array
	 */
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
	 * 获取微信jsapi调用签名信息
	 * @param string $jsapi_list 需要调用的微信jsapi模块
	 * @todo 注入模板变量jsapi
	 * + corpid
	 * + timestamp
	 * + nonce_str
	 * + signature
	 */
	protected function _get_jsapi($jsapi_list = '[]') {

		$wxqy_service = new voa_wxqy_service();
		$jsapi = $wxqy_service->jsapi_signature();

		$this->view->set('jsapi', $jsapi);
		$this->view->set('jsapi_list', $jsapi_list);

		// 使用微信jsapi接口
		$this->view->set('use_wxjsapi', 1);
	}

}
