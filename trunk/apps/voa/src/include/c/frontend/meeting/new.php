<?php
/**
 * 新的会议
 * $Author$
 * $Id$
 */
class voa_c_frontend_meeting_new extends voa_c_frontend_meeting_base {

	public function execute() {
		if ($_GET['step'] == 2) {
			$this->_step2();
			exit();
		}
		if ($_GET['step'] == 'check') {
			$this->check();
			exit();
		}
		if ($_GET['step'] == 3) {
			$this->_step3();
			exit();
		}
		if ($_GET['step'] == 'submit') {
			$this->_submit();
			exit();
		}

		// 地址列表
		$adds = array();
		// by zhuxun, 开始时间, 临时解决方案
		$start_i = -1;
		$end_i = -1;
		// end.
		foreach ($this->_rooms as $r) {
			if (!in_array($r['mr_address'], $adds)) {
				$adds[$r['mr_address']] = $r['mr_address'];
			}

			// by zhuxun, 计算起始分钟数
			list($h, $i, $s) = explode(':', $r['mr_timestart']);
			$tmp_i = $h * 60 + $i;
			$start_i = -1 == $start_i ? $tmp_i : min($start_i, $tmp_i);

			list($h, $i, $s) = explode(':', $r['mr_timeend']);
			$tmp_i = $h * 60 + $i;
			$end_i = -1 == $end_i ? $tmp_i : max($end_i, $tmp_i);
			// end.
		}
		$this->view->set('adds', $adds);

		// 日期列表
		$today = rstrtotime('today');
		for ($i = 0; $i < 15; $i ++) {
			$d = rgmdate($today + 86400 * $i, 'Y-m-d');
			$dates[$d] = $d;
		}
		$this->view->set('dates', $dates);

		// 当前分钟数(为了过滤开始时间列表)
		$this->view->set('current', rgmdate(time(), 'H') * 60 + rgmdate(time(), 'i'));

		// 时间列表(半小时为一段)
		for ($i = 0; $i < 48; $i ++) {
			$k = rgmdate($today + 1800 * $i, 'H') * 60 + rgmdate($today + 1800 * $i, 'i');
			// by zhuxun.
			if ($k < $start_i || $k > $end_i) continue;
			//if ($k < 540)
				//continue;
			// end.
			$t = rgmdate($today + 1800 * $i, 'H:i');
			$times[$t] = rgmdate($today + 1800 * $i, 'H:i');
		}
		$this->view->set('times', $times);
		$this->view->set('current', (rgmdate(time(), 'H') + 1) . ':00'); // 默认选中时间段

		// 时长列表
		for ($i = 0; $i < 14; $i ++) {
			$j = ($i * 0.5 + 0.5);
			$lengths["$j"] = $j . '小时';
		}
		$this->view->set('lengths', $lengths);

		$this->_output('meeting/new1');
	}

	/**
	 * 获取获取时间的会议室
	 */
	public function check() {
		// 计算开始时间与结束时间,转换为一天中的分钟数
		list($hour, $minute) = explode(':', $_POST['time']);
		$start = $hour * 60 + $minute;
		$end = $start + $_POST['length'] * 60;

		// 开始时间不能早于当前时间
		$starttime = rstrtotime($_POST['date'] . ' ' . $_POST['time']);
		if ($starttime < time()) {
			$this->ajax(0, '会议时间不能早于当前时间');
		}

		// 会议室按地址过滤,楼层分组并按层排序
		$rooms = array();
		$meeting = new voa_d_oa_meeting();
		foreach ($this->_rooms as $r) {
			if ($r['mr_address'] != $_POST['addr']) {
				continue;
			}
			// 计算会议室开始,结束时间,转换为分钟数
			list($hour, $minute) = explode(':', $r['mr_timestart']);
			$r['start'] = $hour * 60 + $minute;
			list($hour, $minute) = explode(':', $r['mr_timeend']);
			$r['end'] = $hour * 60 + $minute;

			// 过滤不符合时间段的会议室
			if ($start < $r['start'] || $end > $r['end']) {
				continue;
			}

			// 过滤已有会议的会议室
			$starttime = rstrtotime($_POST['date'] . ' ' . $_POST['time'] . ':00');
			$endtime = $starttime + $_POST['length'] * 3600;
			$where = "mr_id = {$r['mr_id']} AND mt_status < 3 AND (($starttime >= mt_begintime AND  $starttime < mt_endtime) OR ($endtime > mt_begintime AND  $endtime <= mt_endtime))";
			$list = $meeting->fetch_all_by_conditions2($where);
			if ($list) {
				continue;
			}
			$rooms[$r['mr_floor']][] = $r;
		}
		if (!$rooms) {
			$this->ajax(0, '当前时间段无空闲会议室');
		}
		$this->ajax(1);
	}

	/**
	 * 选会议室
	 */
	private function _step2() {
		if (!$_POST) {
			header('Location: /frontend/meeting/new');
			exit();
		}
		// 计算开始时间与结束时间,转换为一天中的分钟数
		list($hour, $minute) = explode(':', $_POST['time']);
		$start = $hour * 60 + $minute;
		$end = $start + $_POST['length'] * 60;

		// 开始时间不能早于当前时间
		$starttime = rstrtotime($_POST['date'] . ' ' . $_POST['time']);
		if ($starttime < time()) {
			$this->_error_message('会议开始时间不能早于当前时间');
		}

		// 会议室按地址过滤,楼层分组并按层排序
		$rooms = array();
		$meeting = new voa_d_oa_meeting();
		foreach ($this->_rooms as $r) {
			if ($r['mr_address'] != $_POST['addr']) {
				continue;
			}
			// 计算会议室开始,结束时间,转换为分钟数
			list($hour, $minute) = explode(':', $r['mr_timestart']);
			$r['start'] = $hour * 60 + $minute;
			list($hour, $minute) = explode(':', $r['mr_timeend']);
			$r['end'] = $hour * 60 + $minute;

			// 过滤不符合时间段的会议室
			if ($start < $r['start'] || $end > $r['end']) {
				continue;
			}

			// 过滤已有会议的会议室
			$starttime = rstrtotime($_POST['date'] . ' ' . $_POST['time'] . ':00');
			$endtime = $starttime + $_POST['length'] * 3600;
			$where = "mr_id = {$r['mr_id']} AND mt_status < 3 AND (($starttime >= mt_begintime AND  $starttime < mt_endtime) OR ($endtime > mt_begintime AND  $endtime <= mt_endtime))";
			$list = $meeting->fetch_all_by_conditions2($where);
			if ($list) {
				continue;
			}
			$rooms[$r['mr_floor']][] = $r;
		}
		ksort($rooms);
		$this->view->set('post', json_encode($_POST));
		$this->view->set('rooms', $rooms);
		$this->_output('meeting/new2');
	}

	/**
	 * 填主题,选人
	 */
	private function _step3() {
		// 会议室信息
		$mr_id = intval($_GET['id']);
		$room = $this->_rooms[$mr_id];
		if (!$room) {
			$this->_error_message('会议室信息错误');
		}
		$this->view->set('room', $room);

		// 取草稿信息
		$data = array();
		$this->_get_draft($data);
		// $data['ccusers'] = array();
		$this->view->set('ccusers', $data['ccusers']);

		// 会议信息
		$this->view->set('user', $this->_user['m_username']);
		$week = rgmdate(rstrtotime($_GET['date']), 'w');
		$map = array(
			'日',
			'一',
			'二',
			'三',
			'四',
			'五',
			'六'
		);
		$this->view->set('week', $map[$week]);

		$cc_users = array();
		if ($data['ccusers'] && is_array($data['ccusers'])) {
/*
			foreach ($data['ccusers'] as $_uid => $_username) {
				$cc_users[] = array(
					'm_uid' => $_uid,
					'm_username' => $_username,
					'm_face' => $this->avatar($_uid)
				);
				// 超过5人则忽略
				if (isset($cc_users[4])) {
					break;
				}
			}
*/
		}
		$this->view->set('cc_users', $cc_users);

		$this->view->set('get', json_encode($_GET));
		$this->_output('meeting/new3');
	}

	/**
	 * 提交
	 * @return boolean
	 */
	public function _submit() {
		$begin = rstrtotime($_POST['date'] . ' ' . $_POST['time']);
		$end = $begin + $_POST['length'] * 3600;
		$this->request->set_params(array(
			'mr_id' => intval($_POST['id']),
			'join_uids' => $_POST['join_uids'],
			'message' => $this->request->get('subject'),
			'begin_hm' => $begin,
			'end_hm' => $end
		));
		$uda = &uda::factory('voa_uda_frontend_meeting_insert');
		// 会议信息
		$meeting = array();
		// 用户列表
		$user_list = array();
		if (!$uda->meeting_new($meeting, $user_list)) {
			$this->_error_message($uda->error);
			return false;
		}

		// 更新草稿信息
		$this->_update_draft(array_keys($user_list));

		// 把消息推入队列
		if ($_POST['send'] == 'on') {
			$this->_to_queue($meeting, $user_list);
		}

		// 输出结果
		echo json_encode(array(
			'state' => 1,
			'info' => $meeting['mt_id']
		));
	}

	/**
	 * 把消息推入队列
	 * @param array $meeting 会议信息
	 * @param array $user_list 需要发送的用户
	 * @return boolean
	 */
	protected function _to_queue($meeting, $user_list) {
		// 整理需要接收消息的用户
		$users = array();
		foreach ($user_list as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$users[$u['m_uid']] = $u['m_openid'];
		}

		// 如果没有需要发送的用户
		if (empty($users)) {
			return true;
		}

		// 整理输出
		$uda_fmt = &uda::factory('voa_uda_frontend_meeting_format');
		if (!$uda_fmt->meeting($meeting)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		// 发送微信消息
		$viewurl = '';
		$this->get_view_url($viewurl, $meeting['mt_id']);
		$content = "会议邀请\n"
				. "来自：" . $meeting['m_username'] . "\n"
				. "会议室：" . $this->_rooms[$meeting['mr_id']]['mr_name'] . "\n"
				. "会议主题：" . $meeting['mt_subject'] . "\n"
				. "会议时间：" . $meeting['_begintime'] . "\n"
				. " <a href='" . $viewurl . "'>点击查看详情</a>";

		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int) $this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		// 写入 cookie, 刷新页面时发送
		$this->set_queue_session(array(
			$data['mq_id']
		));
	}

}
