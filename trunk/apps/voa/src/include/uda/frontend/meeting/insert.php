<?php
/**
 * 会议相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_meeting_insert extends voa_uda_frontend_meeting_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 新增会议操作
	 * @param array $meeting 会议详情
	 * @param array $user_list 参会人员列表
	 * @return boolean
	 */
	public function meeting_new(&$meeting, &$user_list) {
		/** 开始结束时间 */
		$begintime = (string)$this->_request->get('begin_hm');
		if (!$this->val_begin_hm($begintime)) {
			return false;
		}

		$endtime = (string)$this->_request->get('end_hm');
		if (!$this->val_end_hm($endtime)) {
			return false;
		}

		/** 判断时间是否正确, 结束时间不能小于开始时间, 会议开始时间不能提前超过 1 个月并且不能小于当前时间 */
		$now = startup_env::get('timestamp');
		if ($endtime < $begintime || $begintime < $now || $begintime - $now > 86400 * 30) {
			$this->errmsg(100, '会议时间错误');
			return false;
		}

		/** 检查会议室 id 是否正确 */
		$mr_id = (string)$this->_request->get('mr_id');
		if (!$this->val_mr_id($mr_id)) {
			$this->errmsg(100, '请选择会议室');
			return false;
		}

		list($roomid, $is_used) = $this->_room_is_used(
				$mr_id, $begintime, $endtime
		);
		if ($is_used) {
			$this->errmsg(100, '会议室已被占用');
			return false;
		}


		/** 标题和内容 */
		$subject = (string)$this->_request->get('subject');
		if (!$this->val_subject($subject)) {
			return false;
		}

		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		/** 参会人 */
		$uidstr = (string)$this->_request->get('join_uids');
		$join_uids = array();
		if (!$this->val_join_uids($uidstr, $join_uids)) {
			return false;
		}
		$join_uids[startup_env::get('wbs_uid')] = startup_env::get('wbs_uid');
		/** 读取用户列表 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user_list = $servm->fetch_all_by_ids($join_uids);

		/** 入库 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 会议信息入库 */
			$meeting = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'mr_id' => $is_used ? 0 : $roomid,
				'mt_subject' => $subject,
				'mt_message' => $message,
				'mt_invitenum' => count($user_list),
				'mt_begintime' => $begintime,
				'mt_endtime' => $endtime,
				'mt_created' => startup_env::get('timestamp')
			);
			$mt_id = $serv_mt->insert($meeting, true);
			$meeting['mt_id'] = $mt_id;

			/** 参会人用户信息入库 */
			foreach ($user_list as $u) {
				$serv_mm->insert(array(
					'mt_id' => $mt_id,
					'm_uid' => $u['m_uid'],
					'm_username' => $u['m_username'],
					'mm_status' => $u['m_uid'] == startup_env::get('wbs_uid') ? voa_d_oa_meeting_mem::STATUS_CONFIRM : voa_d_oa_meeting_mem::STATUS_NORMAL
				));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			$this->_error_message('会议发起失败');
		}

		return true;
	}

	/**
	 * 判断房间是否可用
	 * @param int $roomid 房间ID
	 * @param int $btime 开始时间戳
	 * @param int $etime 结束时间
	 * @param int $mt_id 当前会议id
	 */
	protected function _room_is_used($roomid, $btime, $etime, $mt_id = 0) {

		$is_used = false;
		/** 会议室不存在 */
		if (empty($this->_rooms[$roomid])) {
			return array(0, false);
		}

		/** 根据 mr_id 读取会议记录 */
		$serv_mt = &service::factory('voa_s_oa_meeting', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_mt->fetch_by_mr_id($roomid, voa_d_oa_meeting::MEETING_NEW);
		/** 判断会议室是否可用 */
		foreach ($list as $mt) {
			// 当前记录
			if ($mt_id == $mt['mt_id']) {
				continue;
			}

			// 如果开始时间处在会议中
			if ($btime > $mt['mt_begintime'] && $btime < $mt['mt_endtime']) {
				$is_used = true;
				break;
			}

			// 如果结束时间处在会议中
			if ($etime > $mt['mt_begintime'] && $etime < $mt['mt_endtime']) {
				$is_used = true;
				break;
			}

			// 如果当前记录包含了 $mt 记录
			if ($btime <= $mt['mt_begintime'] && $etime >= $mt['mt_endtime']) {
				$is_used = true;
				break;
			}
		}

		return array($roomid, $is_used);
	}

	//显示ajax信息
	public function errmsg($state, $info = '')
	{
		echo json_encode(array('state' => 0, 'info' => $info));
		exit;
	}
}
