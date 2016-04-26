<?php
/**
 * voa_c_api_meeting_get_list
 * 查看会议
 * $Author$
 * $Id$
 */
class voa_c_api_meeting_get_view extends voa_c_api_meeting_base {

	public function execute() {

		/*请求参数*/
		$fields = array(
			/*会议ID*/
			'id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		/** 会议信息 */
		$mt_id = $this->_params['id'];
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$meeting = $serv_mt->fetch_by_id($mt_id);
		
		/** 会议信息不存在 */
		if (empty($meeting)) {
			//$this->_error_message('meeting_not_exist');
			return $this->_set_errcode(voa_errcode_api_dailreport::VIEW_NOT_EXISTS, $mt_id);
		}

		$meeting['mt_subject'] = rhtmlspecialchars($meeting['mt_subject']);
		$meeting['mt_message'] = rhtmlspecialchars($meeting['mt_message']);
		$meeting['mt_message'] = bbcode::instance()->bbcode2html($meeting['mt_message']);

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
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_meeting::NO_PRIVILEGE);
		}

		/** 确认参加的用户列表 */
		$confirm_list = array();
		$absence_list = array();
		$confirm_users = array();
		$unconfirm_users = array();
		foreach ($mms as $v) {
			$v['mm_reason'] = bbcode::instance()->bbcode2html($v['mm_reason']);
			if (voa_d_oa_meeting_mem::STATUS_CONFIRM == $v['mm_status']) {
				$confirm_list[$v['mm_id']] = $v;
				$confirm_users[] = $v['m_username'];
			} else if (voa_d_oa_meeting_mem::STATUS_ABSENCE == $v['mm_status']) {
				$absence_list[$v['mm_id']] = $v;
			} else {
				$unconfirm_users[$v['mm_id']] = $v['m_username'];
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
		if ($meeting['mt_endtime'] < startup_env::get('timestamp') || voa_d_oa_meeting::STATUS_CANCEL == $meeting['mt_status']) {
			$meeting_closed = true;
		}

		/** 用户头像信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids(array_keys($mms));
		voa_h_user::push($users);

		/* 邀请人员json数组 */
		$mmlist_josn = array();
		foreach ($mmlist as $key => $value) {
			$mmlist_josn[$value['m_uid']]['uid'] = $value['m_uid'];
			$mmlist_josn[$value['m_uid']]['username'] = $value['m_username'];
			$mmlist_josn[$value['m_uid']]['status'] = $value['mm_status'];
			$mmlist_josn[$value['m_uid']]['avatar'] = voa_h_user::avatar($value['m_uid'], isset($users[$value['m_uid']]) ? $users[$value['m_uid']] : array());
		}
		/** 重组返回json数组 */
		$this->_result = array(
			'id' => $mt_id,
			'meeting' => array(
				'uid' => $meeting['m_uid'],// 创建者uid
				'username' => $meeting['m_username'],// 创建者名字
				'subject' => $meeting['mt_subject'],// 会议主题 
				'message' => $meeting['mt_message'],// 会议内容
				'begintime' => $meeting['mt_begintime'],// 会议开始时间
				'endtime' => $meeting['mt_endtime'],// 会议结束时间
				'mrname' => $this->_rooms[$meeting['mr_id']]['mr_name'],// 会议室
				'invitenum' => $meeting['mt_invitenum'],// 邀请人数
				'agreenum' => $meeting['mt_agreenum'],// 确定参与人数
				'refusenum' => $meeting['mt_refusenum'],// 拒绝参与人数
				'status' => $meeting_closed,// 会议状态 
			),
			'userlist' => $mmlist_josn,
		);

		return true;
	}

}
