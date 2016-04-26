<?php

/**
 * voa_uda_frontend_event_get
 * 应用uda
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_event_get extends voa_uda_frontend_event_base {

	//审核表
	protected $serv;
	protected $serv_partake;

	public function __construct() {
		parent::__construct();
		$this->serv = &service::factory('voa_s_oa_event');
		$this->serv_partake = &service::factory('voa_s_oa_event_partake');
	}

	/**
	 * 获取筛选项
	 * @return json
	 */
	public function getoption() {
		$array = array("all" => "全部", "nostart" => "未开始的", "doing" => "已开始的", "closeds" => "已结束的");
		return json_encode($array, true);
	}

	public function doit(array $request, &$result) {
		$page_option[0] = $request['start'];
		$page_option[1] = $request['limit'];
		$orderby = array(
			'created' => 'DESC'
		);
		$conds = array();
		switch ($request['action']) {
			case 'mine' : // 读取我发起的活动
				$conds = array(
					'm_uid' => startup_env::get('wbs_uid')
				);
				break;
			case 'join' : // 读取我参与的活动
				$join_conds = array(
					'm_uid' => startup_env::get('wbs_uid')
				);
				$list = $this->serv_partake->list_by_conds($join_conds, $page_option, $orderby);
				$acids = array();
				foreach ($list as $k => $v) {
					if (!isset($acids[$v['acid']])) {
						$acids[] = $v['acid'];
					}
				}
				if (empty($acids)) {
					return array();
				}
				$conds['acid'] = $acids;
				$page_option = array();
				break;
			case 'all' : // 读取全部的活动
				$conds = array();
				break;
			case 'nostart' : // 读取未开始的活动
				$conds['start_time > ?'] = startup_env::get('timestamp');
				break;
			case 'doing' : // 读取进行中的活动
				$conds['start_time <= ?'] = startup_env::get('timestamp');
				$conds['end_time >= ?'] = startup_env::get('timestamp');
				break;
			case 'closeds' : // 读取已结束的活动
				$conds['end_time < ?'] = startup_env::get('timestamp');
				break;
		}
		$result = $this->serv->list_by_conds($conds, $page_option, $orderby);

		return true;
	}

	/*
	 *整理数据
	 *@param array list
	 *@return array data
	 */
	public function listformat($list, &$data) {
		$datas = array();
		if (empty($list)) {
			return true;
		}

		// 关联到的所有活动id
		$acids = array();
		// 关联到的所有用户id
		$m_uids = array();
		foreach ($list as $_p) {
			if (!isset($acids[$_p['acid']])) {
				$acids[$_p['acid']] = $_p['acid'];
			}
			if (!isset($m_uids[$_p['m_uid']])) {
				$m_uids[$_p['acid']] = $_p['m_uid'];
			}
		}
		unset($_p);

		// 活动 - 内部参与人数 key=活动id,value=人数
		$member_counts = $this->serv_partake->list_count_by_conds($acids);
		// 重构数组
		foreach ($member_counts as $k => $v) {
			$mem_counts[$v['acid']] = $v['_count'];
		}

		// 活动 - 外部参与人数 key=活动id,value=人数
		$outsider_counts = $this->serv_outsider->list_count_by_conds($acids);
		foreach ($outsider_counts as $k => $v) {
			$out_counts[$v['acid']] = $v['_count'];
		}

		// 相关发布者的信息
		$members = voa_h_user::get_multi($m_uids);

		// 标记类别排序状态
		// 按type进行排序
		// key = type值，value = 排序号
		$type_status = array(
			2 => 3,
			1 => 2,
			3 => 1,
		);

		// 遍历数据重组格式化
		$tmp = array();
		$orderby_type = array();
		$orderby_time = array();
		foreach ($list as $_p) {

			// 当前活动内部人员参与数
			$count = isset($mem_counts[$_p['acid']]) ? $mem_counts[$_p['acid']] : 0;
			$_acid = $_p['acid'];
			if ($_p['outsider'] == 1) {
				// 当前活动允许外部人员参与
				$count = $count + (isset($out_counts[$_p['acid']]) ? $out_counts[$_p['acid']] : 0);
				$_acid = urlencode(authcode($_p['acid'], 'zhoutao', 'ENCODE', '0'));
			}
			// 当前活动发起者头像信息
			$_p['_avatar'] = isset($members[$_p['m_uid']]) ? $members[$_p['m_uid']]['m_face'] : voa_h_user::avatar(0);
			$_p['_avatar'] = 'background-image: url(' . $_p['_avatar'] . ')';
			// 活动状态
			$ctype = $this->_check_type($_p['start_time'], $_p['end_time']);
			// 数据的数据
			$datas[$_p['acid']] = array(
				'acid' => $_acid,
				'title' => htmlspecialchars($_p['title']),
				'uname' => $_p['uname'],
				'np' => $_p['np'],
				'anp' => $count,
				'ctype' => $ctype[0],
				'ctype1' => $ctype[1],
				'updated' => rgmdate($_p['updated'], 'Y-m-d H:i'),
				'_avatar' => $_p['_avatar']
			);
			// 类别
			$orderby_type[$_p['acid']] = isset($type_status[$ctype[1]]) ? $type_status[$ctype[1]] : -1;
			// 时间
			$orderby_time[$_p['acid']] = $_p['created'];
		}

		array_multisort($orderby_type, SORT_DESC, SORT_NUMERIC,
			$orderby_time, SORT_DESC, SORT_NUMERIC,
			$datas);

		$data = $datas;
		unset($datas);

		return true;
	}

	/**
	 *活动按状态排序
	 * @param array data
	 * @param array datas
	 *return string data
	 */
	private function _sort(&$data, $datas) {
		//排序
		if (!empty($datas[2])) {
			foreach ($datas[2] as $v) {
				$data[] = $v;
			}
		}
		if (!empty($datas[1])) {
			foreach ($datas[1] as $v) {
				$data[] = $v;
			}
		}
		if (!empty($datas[3])) {
			foreach ($datas[3] as $v) {
				$data[] = $v;
			}
		}
	}

	/**
	 * 判断是不是组织人扫描
	 * @param $in    二维码信息
	 * @param $out    是否成功签到
	 * @return bool
	 * @throws help_exception
	 */
	public function judgem($in, &$out) {
		if ($in['acid'] == '' || $in['npe'] == '') {
			$out = 1;
			return true; //没有输入正确的活动或者报名人id
		}
		$data = $this->serv->get_by_conds($in['acid']);
		if (empty($data['acid'])) {
			$out = 2;
			return true; //没有这个活动
		}
		if ($in['m_uid'] != $data['m_uid'] && !$this->_check_issue($in['m_uid'])) {
			$out = 3;
			return true; //不是组织人扫描或者没有权限扫描
		}
		$partake = array(
			"m_uid" => $in['npe'],
			"acid" => $data['acid']
		);
		$this->apartake($partake, $out);
		return true;
	}

	/**
	 * 判断外部报名是不是组织人扫描
	 * @param $in   活动信息
	 * @param $out    是否成功签到
	 * @param $remark 备注
	 * @return bool
	 * @throws help_exception
	 */
	public function outjudgem($in, &$out, &$remark) {
		if ($in['m_uid'] != 0) {
			$act = array(
				'acid' => $in['acid'],
			);
		} else {
			$out = 3;
			return true;
		}
		$data = $this->serv->get_by_conds($act);
		if (empty($data['acid'])) {
			$out = 2;
			return true; //没有这个活动
		}
		if ($in['m_uid'] != $data['m_uid'] && !$this->_check_issue($in['m_uid'])) {
			$out = 3;
			return true; //不是组织人扫描或者没有权限扫描
		}
		$outpartake = array(
			'acid' => $in['acid'],
			'outname' => $in['outname'],
			'outphone' => $in['outphone']
		);
		$result = null;
		$this->outapartake($outpartake, $result, $remark);
		$out = $result;
		return true;
	}

	/**
	 * 外部完成签到
	 * @param $in 报名人信息和活动ID
	 * @param $out 是否签到成功
	 * @param $remark 备注
	 * @return bool
	 */
	public function outapartake($in, &$out, &$remark) {
		$data = $this->serv_outsider->list_by_conds($in);
		$oapid = null;
		$remark = null;
		foreach ($data as $k => $v) {
			$oapid = $v['oapid'];
			$remark = $v['remark'];
			$check = $v['check'];
		}
		if ($check == 1) {
			$out = 4;
			return true;
		}
		$type = array("check" => 1);
		$out = $this->serv_outsider->update_by_conds(array('oapid' => $oapid), $type);
		return true;
	}

	/**
	 * 完成签到
	 * @param $in 报名人ID和活动ID
	 * @param $out 是否签到成功
	 * @return bool
	 */
	public function apartake($in, &$out) {
		$data = $this->serv_partake->list_by_conds($in);
		$apid = null;
		foreach ($data as $k => $v) {
			$apid = $v['apid'];
			if ($v['check'] == 1) {
				$out = 4;
				return true;
			}
		}
		$type = array("check" => 1);
		$out = $this->serv_partake->update_by_conds(array('apid' => $apid), $type);
		return true;
	}

	/**
	 * 根据acid查询单条活动数据
	 * @param $in
	 * @param $out
	 * @return bool
	 * @throws help_exception
	 */
	public function getact($in, &$out) {
		$fields = array(
			array('acid', self::VAR_INT, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields, $in)) {
			return false;
		}
		$data = $this->serv->get_by_conds($data);

		if (empty($data)) {
			return false;
		} else {
			$out = $data;
		}
		return true;
	}

	/**
	 * 处理根据getact方法读取出来的数据
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function handle($data) {

		$data['start_data'] = rgmdate($data['start_time'], "Y-m-d");
		$data['start_time'] = rgmdate($data['start_time'], "H:i");
		$data['end_data'] = rgmdate($data['end_time'], "Y-m-d");
		$data['end_time'] = rgmdate($data['end_time'], "H:i");
		$data['cut_off_data'] = rgmdate($data['cut_off_time'], "Y-m-d");
		$data['cut_off_time'] = rgmdate($data['cut_off_time'], "H:i");
		$data['address'] = $data['province'].$data['city'].$data['area'].$data['street'];
		$user_info = voa_h_user::get($data['m_uid']);
		$data['username'] = isset($user_info['m_username']) ? $user_info['m_username'] : '';
		return $data;
	}

	/**
	 * 验证权限
	 * @param int $m_uid
	 * return boolen
	 * */
	protected function _check_issue($m_uid){

		//获取配置
		$p_setting = voa_h_cache::get_instance()->get('plugin.event.setting', 'oa');
		//获取权限配置
		$data_cd_ids = array();
		$data_m_uids = array();
		$all = isset($p_setting['all']) ? (int)$p_setting['all'] : '';

		//当选择全公司时
		if ($all == 1 && !empty($m_uid)) {

			return true;
		}

		//判断权限是否存在
		if(isset($p_setting['cd_ids']) || isset($p_setting['m_uids'])) {

			$p_setting['cd_ids'] = array_filter($p_setting['cd_ids']);
			$p_setting['m_uids'] = array_filter($p_setting['m_uids']);

			if($p_setting['cd_ids'] ) {

				$data_cd_ids = $p_setting['cd_ids'];
			}

			if($p_setting['m_uids'] ) {

				$data_m_uids = $p_setting['m_uids'];
			}
		}

		//判断是否在可签到人员列表中
		if( $data_m_uids && in_array($m_uid, $data_m_uids)){

			return true;
		}

		//查看是否选择了全公司
		if($data_cd_ids){

			$serv_d = new voa_s_oa_common_department();
			$cd_ids = array();
			$cd_ids = $serv_d->fetch_all_by_key($data_cd_ids);
			if($cd_ids){

				//获取部门的上级ID
				$parentids = array();
				$parentids = array_column($cd_ids, 'cd_upid');
				$parentid = 0;
				//如果上级ID中有0的说明选择了全公司
				if($parentids && in_array($parentid, $parentids)){

					return true;
				}
			}
		}

		//判断当前用户部门是否在可签到部门中

		//查看当前用户所在部门列表，多部门
		$serv_m = new voa_s_oa_member_department();
		$cd_ids = $serv_m->fetch_all_by_uid($m_uid);

		//判断是否在可签到部门列表中
		if( $data_cd_ids && $cd_ids){

			foreach($cd_ids as $v){

				if(in_array($v, $data_cd_ids)){

					return true;
				}
			}

		}

		return false;
	}
}
