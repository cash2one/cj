<?php

/**
 * voa_c_api_activity_post_new
 * 新建活动
 * $Author$
 * $Id$
 */
class voa_c_api_activity_post_new extends voa_c_api_activity_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			'acid' => array('type' => 'string_trim', 'required' => false),    //acid
			'title' => array('type' => 'string_trim', 'required' => true),    //活动主题
			'content' => array('type' => 'string_trim', 'required' => true),//活动内容
			'atids' => array('at_ids' => 'string_trim', 'required' => false),// 附件ID
			'start' => array('type' => 'string', 'required' => true),        //开始时间
			'end' => array('type' => 'string', 'required' => true),            //结束时间
			'cut' => array('type' => 'string', 'required' => true),            //截止时间
			'address' => array('type' => 'string_trim', 'required' => true),//活动地点
			'np' => array('type' => 'int', 'required' => false),            //限制人数
			'uids' => array('type' => 'string_trim', 'required' => false),    //邀请人员
			'outsider' => array('type' => 'int', 'required' => false),        //外部人员
			'outfield' => array('type' => 'array', 'required' => false)        //列表字段
		);
		// 基本验证检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 活动标题检查
		if (empty($this->_params['title'])) {
			return $this->_set_errcode(voa_errcode_api_activity::SUBJECT_NULL);
		}
		// 活动标题字数检查
		if (!validator::is_string_count_in_range($this->_params['title'], 1, 15)) {
			return $this->_set_errcode(voa_errcode_api_activity::SUBJECT_BEYOND);
		}
		// 活动内容检查
		if (empty($this->_params['content'])) {
			return $this->_set_errcode(voa_errcode_api_activity:: MESSAGE_NULL);
		}
		// 活动开始时间检查
		if (empty($this->_params['start'])) {
			return $this->_set_errcode(voa_errcode_api_activity:: START_NULL);
		}
		// 活动结束时间检查
		if (empty($this->_params['end'])) {
			return $this->_set_errcode(voa_errcode_api_activity:: END_NULL);
		}
		// 活动截止时间检查
		if (empty($this->_params['cut'])) {
			return $this->_set_errcode(voa_errcode_api_activity:: CUT_NULL);
		}
		// 时间判断
		if (!empty($this->_params['start'])) {
			$start_time = rstrtotime($this->_params['start']);
		}
		if (!empty($this->_params['end'])) {
			$end_time = rstrtotime($this->_params['end']);
		}
		if (!empty($this->_params['cut'])) {
			$cut_off_time = rstrtotime($this->_params['cut']);
		}
		$date = startup_env::get('timestamp'); //当前时间
		if ($cut_off_time > $end_time) {
			return $this->_set_errcode(voa_errcode_api_activity:: TIME_CUT_END);
		}

		if ($end_time < $start_time) {
			return $this->_set_errcode(voa_errcode_api_activity:: TIME_START_END);
		}
		// 如果是编辑
		if (!empty($this->_params['is_edit'])) {
			//活动创建时间$created
			$uda_activity = new voa_uda_frontend_activity_get();
			$uda_activity->getact($this->_params['acid'], $activity);
			$created = rgmdate($activity['created'], 'Y-m-d H:i');
			if ($cut_off_time < $created) {
				return $this->_set_errcode(voa_errcode_api_activity:: TIME_CUT_CREATED);
			}
		} else {
			// 如果是新增
			if (empty($this->_params['acid'])) {
				/*邀请人员选择*/
				if ($cut_off_time < $date) {
					return $this->_set_errcode(voa_errcode_api_activity:: TIME_CUT_DATA);
				}
				if (empty($this->_params['users']) && empty($this->_params['dp']) && $this->_params['outsider'] == 0) {
					return $this->_set_errcode(voa_errcode_api_activity:: MEM_NULL);
				}
			}
		}
		$data = $this->_get_param();
		// 入库操作
		if (!$this->_add($data, $this->_params['acid'])) {
			return false;
		}

		$this->_result = $this->_return;

		return true;
	}

	/*
	 * 获取参数
	 * @return array
	*/
	protected function _get_param() {
		$data = array();
		if (!empty($this->_params['title'])) {
			$data['title'] = $this->_params['title'];
		}
		if (!empty($this->_params['content'])) {
			$data['content'] = $this->_params['content'];
		}
		if (!empty($this->_params['start'])) {
			$data['start_time'] = rstrtotime($this->_params['start']);
		}
		if (!empty($this->_params['end'])) {
			$data['end_time'] = rstrtotime($this->_params['end']);
		}
		if (!empty($this->_params['cut'])) {
			$data['cut_off_time'] = rstrtotime($this->_params['cut']);
		}
		if (!empty($this->_params['address'])) {
			$data['address'] = $this->_params['address'];
		}
		if (!empty($this->_params['np'])) {
			$data['np'] = $this->_params['np'];
		}
		if (!empty($this->_params['users'])) {
			$data['users'] = $this->_params['users'];
		}
		if (!empty($this->_params['dp'])) {
			$data['dp'] = $this->_params['dp'];
		}
		if (!empty($this->_params['atids'])) {
			$data['at_ids'] = $this->_params['atids'];
		}
		if (!empty($this->_params['outsider'])) {
			$data['outsider'] = $this->_params['outsider'];
		}
		if (!empty($this->_params['outfield'])) {
			$data['outfield'] = $this->_params['outfield'];
		}
		$m_uid = startup_env::get('wbs_uid');
		$data['m_uid'] = $m_uid;
		$serv_getuserlist = &service::factory('voa_uda_frontend_member_getuserlist');
		$muid = array('uid' => $m_uid);
		$user = array();
		@$serv_getuserlist->doit($muid, $user);
		$data['uname'] = $user[$m_uid]['m_username'];
		return $data;
	}

	/*
	 * 入库
	 * @param array $data
	 * @param int $acid
	 * @return boolen 新增成功
	*/
	protected function _add($data, $acid = null) {
		//不邀请内部人员的情况
		if (empty($data['users']) && empty($data['dp'])) {
			if (!isset($data['outsider'])) {
				$data['outsider'] = 0;
			}
			$serv = &service::factory('voa_s_oa_activity');
			if ($acid) {//更新
				unset($data['outfield']);
				unset($data['outsider']);
				$serv->update_by_conds(array('acid' => $acid), $data);
				$res['acid'] = $acid;
				$message = "更新成功";
			} else {//插入
				if ($data['outfield'] != '') {
					$data['outfield'] = serialize($data['outfield']);
				}
				$res = $serv->insert($data);
				$message = "发布成功";
			}

			$this->_return = array('url' => '/frontend/activity/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $res['acid'], 'message' => $message);
			return true;
		}

		//有内部人员的情况
		if (!empty($data['users'])) {
			$uids = $data['users'];
			unset($data['users']);
		}
		if (!empty($data['dp'])) {
			$dp = $data['dp'];
			unset($data['dp']);
		}

		if (!isset($data['outsider'])) {
			$data['outsider'] = 0;
		}

		$serv = &service::factory('voa_s_oa_activity');
		if ($acid) {//更新
			$serv->update_by_conds(array('acid' => $acid), $data);
			$res['acid'] = $acid;
			$message = "更新成功";
		} else {//插入
			if ($data['outfield'] != '') {
				$data['outfield'] = serialize($data['outfield']);
			}
			$res = $serv->insert($data);
			$message = "发布成功";
		}

		$this->_return = array('url' => '/frontend/activity/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $res['acid'], 'message' => $message);

		$touser = array();
		$toparty = array();

		if (!empty($uids)) {
			$touser = explode(",", $uids);//数组 由于用的是UID
		}
		if (!empty($dp)) {
			$toparty = explode(",", $dp);//数组 由于用的是dps
		}
		//插入邀请人员
		if (empty($acid)) {
			$this->insertusers($touser, $toparty, $res['acid']);
		}
		/** 发送微信消息 */
		if ($acid) {
			$msg_title = "您报名的活动有更新";
			$serv_partake = &service::factory('voa_s_oa_activity_partake');
			$partake = $serv_partake->list_by_conds(array("acid" => $acid));
			$touser = array();
			$toparty = array();
			if (!empty($partake)) {
				foreach ($partake as $v) {
					$touser[] = $v['m_uid'];
				}
			}
		} else {
			$msg_title = "您收到1个活动邀请";
		}
		if (empty($touser) && empty($toparty)) {
			return true;
		}
		$scheme = config::get('voa.oa_http_scheme');
		$msg_desc = "主题：【" . $data['title'] . "】\n";
		$msg_desc .= "活动时间：" . rgmdate($data['start_time'], "m-d H:i") . " 到 " . rgmdate($data['end_time'], "m-d H:i") . "\n";
		$msg_url = $scheme . $this->_setting['domain'] . '/frontend/activity/view/?acid=' . $res['acid'] . '&pluginid=' . startup_env::get('pluginid');

		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, '', 0, 0, -1);//发消息

		return true;
	}

	/*
	 *邀请用户
	 */
	private function insertusers($touser, $toparty, $acid) {
		$serv_invite = &service::factory('voa_s_oa_activity_invite');
		$data = array();
		foreach ($touser as $val) {
			$data[] = array(
				'primary_id' => $val,
				'type' => 2,
				'acid' => $acid
			);
		}
		foreach ($toparty as $val) {
			$data[] = array(
				'primary_id' => $val,
				'type' => 1,
				'acid' => $acid
			);
		}
		$serv_invite->insert_multi($data);
		return true;
	}
}
