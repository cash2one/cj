<?php

/**
 * voa_uda_frontend_activity_view
 * 应用uda
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_activity_view extends voa_uda_frontend_activity_base {

	public function __construct() {
		parent::__construct();
		$this->serv_exterior = &service::factory('voa_s_oa_activity_outsider');
		$this->serv_interior = &service::factory('voa_s_oa_activity_partake');
	}

	/*
	 *获取一个活动的详情和参与人数
	 *@param int acid 活动id
	 *@param array 返回结果
	 */
	public function doit($request, &$result) {
		//活动详情数据
		$serv = &service::factory('voa_s_oa_activity');
		$view = $serv->get($request);
		if (!$view) {
			return false;
		}
		$partake = array();
		//活动参与人员信息
		$serv_partake = &service::factory('voa_s_oa_activity_partake');
		$partake = $serv_partake->list_by_conds(array("acid" => $request));
		//邀请人员
		$serv_invite = &service::factory('voa_s_oa_activity_invite');
		$invite = $serv_invite->list_by_conds(array("acid" => $request));
		$result = $view;
		//外部人员
		$exterior = $this->serv_exterior->list_by_conds(array('acid' => $request, 'deleted' => 0));
		$result['exterior'] = $exterior;
		$result['partake'] = $partake;
		$result['invite'] = $invite;
		return true;
	}

	/*
	 *数据整理
	 *@param int data 查询的结果
	 *@param array 整理后的结果
	 */
	public function format($request, &$data) {
		$data['acid'] = $request['acid'];
		$data['uname'] = $request['uname'];
		$data['title'] = htmlspecialchars($request['title']);
		$data['content'] = nl2br($request['content']);
		$data['np'] = $request['np'];
		$data['address'] = htmlspecialchars($request['address']);
		$data['start_time'] = rgmdate($request['start_time'], "Y-m-d H:i");
		$data['end_time'] = rgmdate($request['end_time'], "Y-m-d H:i");
		$data['cut_off_time'] = rgmdate($request['cut_off_time'], "Y-m-d H:i");
		$datetimes = voa_h_func::get_dhi($request['cut_off_time'] - startup_env::get('timestamp'));
		$data['times'] = $datetimes[0] . '天' . $datetimes[1] . '小时' . $datetimes[2] . '分钟';
		$data['time'] = rgmdate($request['updated'], "Y-m-d H:i");
		$data['partake'] = (!empty($request['partake'])) ? $request['partake'] : array();
		$data['exterior'] = (!empty($request['exterior'])) ? $request['exterior'] : array();
		$data['anp'] = count($data['partake']) + count($data['exterior']);
		$data['ctype'] = $this->_check_type($request['start_time'], $request['end_time']);
		$data['outsider'] = $request['outsider'];
		$data['outfield'] = $request['outfield'];
		if ($data['outsider'] == '0' && startup_env::get('wbs_uid') == '') {
			$data['not-allow-out-people'] = 1;
		} else {
			$data['not-allow-out-people'] = 0;
		}
		if (startup_env::get('wbs_uid') == $request['m_uid']) {
			$data['edit'] = 1;
		} else {
			$data['edit'] = 0;
		}
		if (($request['cut_off_time'] - startup_env::get('timestamp')) > 0) {
			$data['join'] = 1;
		} else {
			$data['join'] = 0;
		}
		// 如果有人员限制，则计算剩余人数
		if ($data['np'] != '0') {
			$data['snp'] = $data['np'] - $data['anp'];
		} else {
			// 表示无限制
			$data['snp'] = '999999999999';
		}
		//是否参与
		$serv_partake = &service::factory('voa_s_oa_activity_partake');
		$partake = $serv_partake->get_by_conds(array("m_uid" => startup_env::get('wbs_uid'), "acid" => $request['acid']));
		$apid = $partake['apid'];
		if (!empty($partake)) {
			$data['in'] = 0; //可以申请退出
		} else {
			$data['in'] = 1; //可以报名
		}
		//参与人
		$serv_getuserlist = &service::factory('voa_uda_frontend_member_getuserlist');
		$users = array();
		if (!empty($data['partake'])) {
			foreach ($data['partake'] as $value) {
				$results = array();
				$request['uid'] = $value['m_uid'];
				$serv_getuserlist->doit($request, $results);
				$users[] = array(
					'm_uid' => $value['m_uid'],
					'm_face' => $results[$value['m_uid']]['m_face'],
					'm_username' => $results[$value['m_uid']]['m_username']
				);
			}
		}
		$data['users'] = $users;
		//获取图片
		$at_ids = explode(',', $request['at_ids']);
		if (!empty($at_ids)) {
			foreach ($at_ids as $val) {
				if ($val) {
					$data['image'][] = array(
						'aid' => $val,
						'url' => voa_h_attach::attachment_url($val, 45)
					);


				}
			}
		} else {
			$data['image'] = array();
		}
		//邀请人员
		$data['users1'] = null;
		$data['dps1'] = null;
		$_dps = array();
		$_users = array();
		if (!empty($request['invite'])) {
			foreach ($request['invite'] as $values) {
				//部门
				if ($values['type'] == 1) {
					$_dps[] = $values['primary_id'];
				}
				//人员
				if ($values['type'] == 2) {
					$_users[] = $values['primary_id'];
				}
			}
			if (!empty($_dps)) {
				$data['dps1'] = implode(',', $_dps);
			}
			if (!empty($_users)) {
				$data['users1'] = implode(',', $_users);
			}
		}
		$data['m_uid'] = startup_env::get('wbs_uid');
		//获取当前用户信息 用在分享时的用户信息
//	$uid = array(
//		'uid' => startup_env::get('wbs_uid')
//	);
//	$nowuser = &service::factory('voa_uda_frontend_member_getuserlist');
//	$nowusers = null;
//	$nowuser->doit($uid, $nowusers);
//	$data['nowusers'] = $nowusers[$data['m_uid']]['m_username'];
		//是否可以再次申请退出1：申请退出 2：再次申请退出
		$serv_nopartake = &service::factory('voa_s_oa_activity_nopartake');
		$nopartake = $serv_nopartake->get_by_conds(array("apid" => "$apid"));
		if (empty($nopartake)) {
			$data['cancel'] = 1;
		} else {
			$data['cancel'] = 2;
		}
		return true;
	}

	/**
	 * 生成内部报名人二维码
	 * @param $acid 活动ID
	 * @param $npe 报名人ID
	 * @param string $file
	 * @param bool $is_download
	 */
	public function qrcode($acid, $npe, $file = '', $is_download = false) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');

		//生成二维码
		include_once(ROOT_PATH . '/framework/lib/phpqrcode.php');
		//跳转地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $sets['domain'] . "/frontend/activity/view?acid=" . urlencode($acid) . "&ac=checkin" . "&npe=" . urlencode($npe);
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		if ($file) {
			//生成文件
			imagepng($qrcode, $file);
		} else {
			//直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}

	/**
	 * 生成外部报名人二维码
	 * @param $qdata 外部人员信息
	 * @param string $file
	 * @param bool $is_download
	 */
	public function outqrcode($qdata, $file = '', $is_download = false) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');

		//生成二维码
		include_once(ROOT_PATH . '/framework/lib/phpqrcode.php');
		//跳转地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $sets['domain'] . "/frontend/activity/outqcode?acid=" . urlencode($qdata['acid']) . "&ac=check&outname=" . urlencode($qdata['outname']) . "&outphone=" . urlencode($qdata['outphone']);
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		if ($file) {
			//生成文件
			imagepng($qrcode, $file);
		} else {
			//直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}

	/**
	 * 获取活动内部报名人信息
	 * @param $acid
	 * @param $out
	 * @return bool
	 */
	public function interior($acid, &$out) {
		$re['acid'] = $acid;
		$re['type <= ?'] = 2;
		$out = $this->serv_interior->list_by_conds($re);
		if (!empty($out)) {
			foreach ($out as $k => &$v) {
				$v['created'] = rgmdate($v['created'], 'm月d日');
			}
		}
		return true;
	}

	/**
	 * 获取活动外部报名人信息
	 * @param $acid
	 * @param $out
	 * @return bool
	 */
	public function exterior($acid, &$out) {
		$re['acid'] = $acid;
		$re['deleted'] = 0;
		$out = $this->serv_exterior->list_by_conds($re);
		if (!empty($out)) {
			foreach ($out as $k => &$v) {
				$v['created'] = rgmdate($v['created'], 'm月d日');
			}
		}
		return true;
	}

	/**
	 * 判断是否已报名的外部人员信息
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function true($in, &$out) {
		$true = $this->serv_exterior->list_by_conds($in);
		// 根据外部提交的信息来查询是不是外部报名表里有这个人
		if ($true) {
			foreach ($true as $k => $v) {
				$true['outname'] = $v['outname'];
				$true['outphone'] = $v['outphone'];
			}
			if ($in['outname'] == $true['outname'] && $in['outphone'] == $true['outphone']) {
				$out = true;
			} else {
				$out = false;
			}
		} else {
			$out = false;
		}

		return true;
	}

}
