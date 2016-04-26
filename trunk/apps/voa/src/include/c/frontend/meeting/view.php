<?php
/**
 * 会议信息查看
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_view extends voa_c_frontend_meeting_base {

	public function execute() {
		/** 读取会议信息 */
		$mt_id = rintval($this->request->get('mt_id'));
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$meeting = $serv_mt->fetch_by_id($mt_id);
		/** 会议信息不存在 */
		if (empty($meeting)) {
			$this->_error_message('meeting_not_exist');
		}

		//读会议地点
		$d = new voa_d_oa_meeting_room();
		$room = $d->fetch_by_id($meeting['mr_id']);
		$meeting['mt_address'] = $room['mr_address'];

		$meeting['mt_subject'] = rhtmlspecialchars($meeting['mt_subject']);
		$meeting['mt_message'] = rhtmlspecialchars($meeting['mt_message']);
		$meeting['mt_message'] = bbcode::instance()->bbcode2html($meeting['mt_message']);
		$meeting['date'] = rgmdate($meeting['mt_begintime'], 'Y-m-d');
		$meeting['week'] = rgmdate($meeting['mt_begintime'], 'w');
		$map = array('日', '一', '二', '三', '四', '五', '六');
		$meeting['week'] = $map[$meeting['week']];

		//会议时长(*小时*分钟)
		$minutes = intval(($meeting['mt_endtime'] - $meeting['mt_begintime']) / 60);
		if($minutes < 1) {
			$meeting['length'] = '未开启';
		}else if($minutes >= 60) {
			$meeting['length'] = intval($minutes / 60) . '小时';
			if($minutes % 60 > 0) $meeting['length'] .= ($minutes % 60) . '分钟';
		}else{
			$meeting['length'] .= $minutes . '分钟';
		}

		$meeting['time'] = rgmdate($meeting['mt_begintime'], 'H:i');

		/** 读取会议参会人列表 */
		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		$mmlist = $serv_mm->fetch_by_mt_id($mt_id);
		/** 判断是否被邀请(即是否有权限查看) */
		$is_permit = false;
		/** 以 uid 为键值的参会人员列表 */
		$mms = array();
		$sponsor_id = 0;
		$my_mm = array();
		foreach ($mmlist as $v) {
			$v['mm_reason'] = rhtmlspecialchars($v['mm_reason']);
			$mms[$v['m_uid']] = $v;
			if ($v['m_uid'] == startup_env::get('wbs_uid')) {
				$my_mm = $v;
				$is_permit = true;
			}

			if ($v['m_uid'] == $meeting['m_uid']) {
				$sponsor_id = $v['mm_id'];
			}
		}
		$tmp = $mmlist[$sponsor_id];
		unset($mmlist[$sponsor_id]);
		array_unshift($mmlist, $tmp);

		if (!$is_permit) {
			$this->_error_message('no_privilege');
		}

		/** 确认参加的用户列表 */
		$confirm_list = array();
		$absence_list = array();
		$confirm_users = array();
		$unconfirm_users = array();
		foreach ($mms as $v) {
			$v['mm_reason'] = bbcode::instance()->bbcode2html($v['mm_reason']);
			if (voa_d_oa_meeting_mem::STATUS_CONFIRM == $v['mm_status']) {
				$confirm_list[$v['m_uid']] = $v;
				$confirm_users[$v['m_uid']] = $v['m_username'];
			} else if (voa_d_oa_meeting_mem::STATUS_ABSENCE == $v['mm_status']) {
				$absence_list[$v['m_uid']] = $v;
			} else {
				$unconfirm_users[$v['m_uid']] = $v['m_username'];
			}
			//已签到用户
			if($v['mm_confirm']) {
				$sign_users[$v['m_uid']] = $v['m_username'];
			}
		}

		/** 统计确认参加/缺席人数 */
		$meeting['mt_agreenum'] = count($confirm_list);
		$meeting['mt_refusenum'] = count($absence_list);

		/** 时间转换 */
		$meeting['_ymd'] = rgmdate($meeting['mt_begintime'], 'Y/m/d');
		$meeting['_begin_hm'] = rgmdate($meeting['mt_begintime'], 'H:i');
		$meeting['_w'] = rgmdate($meeting['mt_begintime'], 'w');
		$meeting['_end_hm'] = rgmdate($meeting['mt_endtime'], 'H:i');

		/** 判断会议状态 */
		$meeting_closed = false;
		if ($meeting['mt_endtime'] < time() || voa_d_oa_meeting::STATUS_CANCEL == $meeting['mt_status']) {
			$meeting_closed = true;	//已结束或取消
			$this->view->set('meeting_closed', $meeting_closed);
		}
		if ($meeting['mt_begintime'] < time() && $meeting['mt_endtime'] > time()) {
			$meeting_ing = true;	//进行中
			$this->view->set('meeting_ing', $meeting_ing);
		}

		// 会议状态
		if (voa_d_oa_meeting::STATUS_CANCEL == $meeting['mt_status']) {
			$meeting_status_string = '已被 '.$meeting['m_username'].' 取消';
		} else {
			if ($meeting['mt_endtime'] < startup_env::get('timestamp')) {
				$meeting_status_string = '已经结束';
			} elseif ($meeting['mt_begintime'] > startup_env::get('timestamp')) {
				$meeting_status_string = '尚未开始';
			} elseif ($meeting['mt_begintime'] < startup_env::get('timestamp')
					&& $meeting['mt_endtime'] > startup_env::get('timestamp')) {
				$meeting_status_string = '正在进行中';
			}
		}
		$this->view->set('meeting_status_string', $meeting_status_string);


		/** 用户头像信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids(array_keys($mms));
		voa_h_user::push($users);

		$this->view->set('room', $this->_rooms[$meeting['mr_id']]);
		$this->view->set('m', $meeting);
		$this->view->set('mms', $mms);


		$this->view->set('mmlist', $mmlist);
		$this->view->set('my_mm', $my_mm);

		$this->view->set('total', count($mmlist));
		$this->view->set('confirm_users', $confirm_users);
		$this->view->set('sign_users', $sign_users);

		$this->view->set('ct_sign_users', count($sign_users));
		$this->view->set('ct_confirm_users', count($confirm_users));

		//如果已结束,未确认的拼到拒绝数组里
		if($meeting_closed) {
			foreach ($absence_list as $k => $a)
			{
				$unconfirm_users[$k] = $a['m_username'];
			}
			$this->view->set('unconfirm_users', $unconfirm_users);
			$this->view->set('ct_unconfirm_users', count($unconfirm_users));
		}else{
			$this->view->set('unconfirm_users', $unconfirm_users);
			$this->view->set('absence_list', $absence_list);
			$this->view->set('ct_unconfirm_users', count($unconfirm_users));
			$this->view->set('ct_absence_list', count($absence_list));
		}

		$this->view->set('refer', get_referer());
		$this->view->set('mt_id', $mt_id);

		//是否发起人
		$is_main = $meeting['m_uid'] == $this->_user['m_uid'];
		$this->view->set('is_main', $is_main);


		$this->_output('meeting/view');
	}
}
