<?php

/**
 * voa_uda_frontend_activity_add
 * 统一数据访问/活动报名/添加活动
 * Created by zhoutao.
 * Created Time: 2015/5/14  0:20
 */
class voa_uda_frontend_activity_add extends voa_uda_frontend_activity_base {

	public function __construct() {
		parent::__construct();
		$this->__service = new voa_s_oa_activity();
		$this->__service_invite = new voa_s_oa_activity_invite();
	}

	private $__request = array();

	/**
	 * 入库操作
	 * @param $in
	 * @param $out
	 * @param object $session
	 * @return bool
	 */
	public function addact($in, &$out, $session) {
		// 提交的值进行过滤
		$data = array();
		if (!$this->getact($in, $data)) {
			return false;
		}

		// 入activity库
		$data = $this->__service->insert($data);

		// 入invite库
		if (isset($in['all_company']) && $in['all_company'] == 1) {
			$dp[] = -1;
			$users[] = -1;
		} else {
			if (!empty($in['users'])) {
				$users = $in['users'];
			} else {
				$users = '';
			}
			if (!empty($in['dp'])) {
				$dp = $in['dp'];
			} else {
				$dp = '';
			}
		}
		if (!empty($data['acid'])) {
			$this->insertusers($users, $dp, $data['acid']);

			$settings = voa_h_cache::get_instance()->get('setting', 'oa');
			// 发送微信消息
			$msg_title = "您收到1个活动邀请";
			$scheme = config::get('voa.oa_http_scheme');
			$msg_desc = "主题：【" . $data['title'] . "】\n";
			$msg_desc .= "活动时间：" . rgmdate($data['start_time'], "m-d H:i") . " 到 " . rgmdate($data['end_time'], "m-d H:i") . "\n";
			$msg_url = $scheme . $settings['domain'] . '/frontend/activity/view/?acid=' . $data['acid'] . '&pluginid=' . startup_env::get('pluginid');

			// 发消息
			voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $users, $dp, '', 0, 0, -1);
		}

		return true;
	}

	/**
	 * 邀请人员
	 * @param $users    人员
	 * @param $dp       部门
	 * @param $acid     活动ID
	 * @return bool
	 */
	private function insertusers($users, $dp, $acid) {
		$serv_invite = &service::factory('voa_s_oa_activity_invite');
		$data = array();
		if (!empty($users)) {
			foreach ($users as $val) {
				$data[] = array(
					'primary_id' => $val,
					'type' => 2,
					'acid' => $acid
				);
			}
		}
		if (!empty($dp)) {
			foreach ($dp as $val) {
				$data[] = array(
					'primary_id' => $val,
					'type' => 1,
					'acid' => $acid
				);
			}
		}

		$serv_invite->insert_multi($data);
		return true;
	}

	/**
	 * 处理提交的数据
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function getact($in, &$out) {
		//获取数据
		if (!empty($in)) {
			$data['title'] = $in['title'];
			if (!isset($in['content']) || $in['content'] == '') {
				$data['content'] = '';
			} else {
				$data['content'] = $in['content'];
			}
			$data['address'] = $in['address'];
			$data['np'] = $in['np'];
			$data['m_uid'] = $in['m_uid'];
			$data['uname'] = voa_h_user::get($in['m_uid']);
			$data['uname'] = $data['uname']['m_username'];
			// 处理时间
			$std = $in['start_time']['data'];
			$stt = $in['start_time']['time'];
			$etd = $in['end_time']['data'];
			$ett = $in['end_time']['time'];
			$cod = $in['cut_off_time']['data'];
			$cot = $in['cut_off_time']['time'];
			$in['start_time'] = $std . ' ' . $stt;
			$in['end_time'] = $etd . ' ' . $ett;
			$in['cut_off_time'] = $cod . ' ' . $cot;
			$data['start_time'] = rstrtotime($in['start_time']);
			$data['end_time'] = rstrtotime($in['end_time']);
			$data['cut_off_time'] = rstrtotime($in['cut_off_time']);

			if (isset($in['outsider']) && $in['outsider'] == '1') {
				$data['outsider'] = $in['outsider'];
				$data['outfield'] = serialize($in['outfield']);
			} else {
				$data['outsider'] = 0;
				$data['outfield'] = serialize($in['outfield']);
			}
		} else {
			return false;
		}

		$fields = array(
			'title' => array('title', parent::VAR_STR, null, null, false),
			'content' => array('content', parent::VAR_STR, null, null, false),
			'address' => array('address', parent::VAR_STR, null, null, false),
			'np' => array('np', parent::VAR_INT, null, null, false),
			'm_uid' => array('m_uid', parent::VAR_INT, null, null, false),
			'uname' => array('uname', parent::VAR_STR, null, null, false),
			'start_time' => array('start_time', parent::VAR_INT, null, null, false),
			'end_time' => array('end_time', parent::VAR_INT, null, null, false),
			'cut_off_time' => array('cut_off_time', parent::VAR_INT, null, null, false),
			'outsider' => array('outsider', parent::VAR_INT, null, null, false),
			'outfield' => array('outfield', parent::VAR_STR, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}
		$out = $this->__request;
		if ($out['start_time'] >= $out['end_time']) {
			$this->errmsg(10001, '开始时间不能大于结束时间');
			return false;
		}
		if ($out['cut_off_time'] < startup_env::get('timestamp')) {
			$this->errmsg(10002, '截止时间不能早于当前时间');
			return false;
		}
		if ($out['cut_off_time'] > $out['end_time']) {
			$this->errmsg(10003, '报名截止时间不能大于活动结束时间');
			return false;
		}
		if (!validator::is_string_count_in_range($out['title'], 1, 15)) {
			$this->errmsg('10004', '标题字数最高15字，最低1个字');
			return false;
		}
		if (!empty($out['np']) && $out['np'] <= 0) {
			$this->errmsg('10006', '限制人数不能为负和零');
			return false;
		}
		if (empty($out['content'])) {
			$this->errmsg('10007', '内容不能为空');
			return false;
		}
		return true;
	}

	/**
	 * 后台活动编辑更新
	 * @param $in
	 * @param $out
	 * @param object session
	 * @return bool
	 */
	public function updataact($in, &$out, $session) {
		$acid = $in['ac'];

		// 处理时间
		$std = $in['start_time']['data'];
		$stt = $in['start_time']['time'];
		$etd = $in['end_time']['data'];
		$ett = $in['end_time']['time'];
		$cod = $in['cut_off_time']['data'];
		$cot = $in['cut_off_time']['time'];
		$in['start_time'] = $std . ' ' . $stt;
		$in['end_time'] = $etd . ' ' . $ett;
		$in['cut_off_time'] = $cod . ' ' . $cot;
		$in['start_time'] = rstrtotime($in['start_time']);
		$in['end_time'] = rstrtotime($in['end_time']);
		$in['cut_off_time'] = rstrtotime($in['cut_off_time']);

		$fields = array(
			'title' => array('title', parent::VAR_STR, null, null, false),
			'content' => array('content', parent::VAR_STR, null, null, false),
			'address' => array('address', parent::VAR_STR, null, null, false),
			'start_time' => array('start_time', parent::VAR_INT, null, null, false),
			'end_time' => array('end_time', parent::VAR_INT, null, null, false),
			'cut_off_time' => array('cut_off_time', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}
		// 判断字段规则
		if ($in['start_time'] > $in['end_time']) {
			$this->errmsg(10001, '开始时间不能大于结束时间');
			return false;
		}
		if ($in['cut_off_time'] < startup_env::get('timestamp')) {
			$this->errmsg(10002, '截止时间不能早于当前时间');
			return false;
		}
		if ($in['cut_off_time'] > $in['end_time']) {
			$this->errmsg(10003, '报名截止时间不能大于活动结束时间');
			return false;
		}
		if (!validator::is_string_count_in_range($in['title'], 1, 15)) {
			$this->errmsg('10004', '标题字数最高15字，最低1个字');
			return false;
		}

		$data = array(
			'title' => $in['title'],
			'content' => $in['content'],
			'address' => $in['address'],
			'start_time' => $in['start_time'],
			'end_time' => $in['end_time'],
			'cut_off_time' => $in['cut_off_time'],
		);
		$out = $this->__service->update_by_conds($acid, $data);

		$invite = $this->__service_invite->list_by_conds(array('acid' => $in['ac']));

		if (!empty($invite)) {
			$users = array();
			$dp = array();
			foreach ($invite as $k => $v) {
				if ($v['type'] == 2) {
					$users[] = $v['primary_id'];
				} else {
					$dp[] = $v['primary_id'];
				}
			}
			if (empty($users)) {
				$users = '';
			}
			if (empty($dp)) {
				$dp = '';
			}
			// 发送微信消息
			$settings = voa_h_cache::get_instance()->get('setting', 'oa');
			$msg_title = "您报名的活动有更新";
			$scheme = config::get('voa.oa_http_scheme');
			$msg_desc = "主题：【" . $data['title'] . "】\n";
			$msg_desc .= "活动时间：" . rgmdate($data['start_time'], "m-d H:i") . " 到 " . rgmdate($data['end_time'], "m-d H:i") . "\n";
			$msg_url = $scheme . $settings['domain'] . '/frontend/activity/view/?acid=' . $acid . '&pluginid=' . startup_env::get('pluginid');
			// 发消息

			voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $users, $dp, '', 0, 0, -1);
		}

		return true;
	}


}
