<?php

/**
 * voa_c_api_campaign_post_addcontent
 * User: Muzhitao
 * Date: 2015/8/26 0026
 * Time: 14:02
 */
class voa_c_api_campaign_post_addcontent extends voa_c_api_campaign_base {

	// 不强制登录，允许外部访问
	protected function _before_action($action) {

		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		$url = ''; // 返回url
		$error = ''; // 错误返回
		$step = intval($this->request->get('step')); // 当前步骤ID

		// url前缀
		$url_prefix = '/admincp/office/' . $this->_api_module . '/add/pluginid/' . startup_env::get('pluginid');

		$result = array();
		try {
			switch ($step) {
				// 活动推广主数据
				case 1 :
					try {
						if (!$this->__check_step()) {
							return false;
						}

						$uda = &uda::factory('voa_uda_frontend_campaign_campaign');
						$act = array();

						// 当前发布活动的作者
						$_POST['uid'] = 0;
						$_POST['username'] = 0;

						// 默认当前状态为编辑
						$_POST['is_push'] = 0;

						$rs = $uda->save($_POST, $act, $error);
						if ($rs) {
							$url = $url_prefix . '/acid/' . $act['id'] . '/?step=2&endtime=' . $this->_params['overtime'];
						}

						// 判断当前动作是否是返回编辑状态
						if (!empty($_POST['action'])) {
							$url = $url . '&act=back';
						}
					} catch (help_exception $h) {
						$this->_errcode = $h->getCode();
						$this->_errmsg = $h->getMessage();
					} catch (Exception $e) {
						logger::error($e);
						return $this->_api_system_message($e);
					}
				break;

				// 活动推广接单详情
				case 2 :
					try {
						// 检查参数是否为空
						if (!$this->__check_params()) {
							return false;
						}

						$uda = &uda::factory('voa_uda_frontend_campaign_orders');
						$uda->add_orders($this->_params, $result);

						// 返回需要的URL地址
						$url = $url_prefix . '/acid/' . $this->_params['cid'] . '/?step=3&endtime=' . $this->_params['endtime'];
					} catch (help_exception $h) {
						$this->_errcode = $h->getCode();
						$this->_errmsg = $h->getMessage();
					} catch (Exception $e) {
						logger::error($e);
						return $this->_api_system_message($e);
					}
				break;

				// 活动推广报名信息
				case 3 :
					if (!$this->_checkcols()) {
						return false;
					}
					try {

						$uda = &uda::factory('voa_uda_frontend_campaign_customcols');

						/* 增加自定义字段操作 如果错误 返回错误代码 */
						if (!$uda->add_customcols($this->_params)) {
							$this->_errcode = $uda->errcode;
							$this->_errmsg = $uda->errmsg;
							$this->_result = array();
							return false;
						}

						// 删除cookie记录的预览状态
						$this->session->remove('cid');

						// 返回需要跳转的URL地址
						$url = "/admincp/office/" . $this->_api_module . "/list/pluginid/" . startup_env::get('pluginid') . "/";

						// 查询该条活动的通知对象
						$right = new voa_d_oa_campaign_right();
						$users = $right->list_by_conds(array('actid' => $this->_params['cid']));

						// 获取活动主题信息
						$d = &service::factory('voa_d_oa_campaign_campaign');
						$camp_data = $d->get($this->_params['cid']);

						// 发送微信消息
						$this->_to_queues($camp_data, $users, $this->session);
					} catch (help_exception $h) {
						$this->_errcode = $h->getCode();
						$this->_errmsg = $h->getMessage();
					} catch (Exception $e) {
						logger::error($e);
						return $this->_api_system_message($e);
					}
				break;
			}
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);

			return $this->_api_system_message($e);
		}


		// 返回数据
		$this->_result = array('url' => $url);

		return true;
	}

	protected function _checkcols() {

		if (isset($this->_params['cols'])) {
			foreach($this->_params['cols'] as $v) {
				if ($v['name'] == '') {
					return $this->_set_errcode(voa_errcode_api_campaign::COLS_NOT_NULL);
				}
			}
		}
		return true;
	}

	/**
	 * 接单详情中数据验证
	 */
	private function __check_params() {

		/* 开始时间不能为空 */
		if (empty($this->_params['stime'])) {
			return $this->_set_errcode(voa_errcode_api_campaign::OTIME_NULL);
		}

		/* 结束时间不能为空 */
		if (empty($this->_params['etime'])) {
			return $this->_set_errcode(voa_errcode_api_campaign::OTIME_NULL);
		}

		/* 如果选择全天 则为00:00:00 */
		if (!$this->_params['o_stime'] || $this->_params['o_stime'] == '全天') {
			$this->_params['o_stime'] = '00:00:00';
		}

		/* 如果选择全天 则为23：59：59 */
		if (!$this->_params['o_etime'] || $this->_params['o_etime'] == '全天') {
			$this->_params['o_etime'] = '23:59:59';
		}

		// 将日期时间 转化为时间戳
		$this->_params['stime'] = rstrtotime($this->_params['stime'] . ' ' . $this->_params['o_stime']);
		$this->_params['etime'] = rstrtotime($this->_params['etime'] . ' ' . $this->_params['o_etime']);

		/* 结束时间不得小于当前时间 */
		if ($this->_params['etime'] < time()) {
			return $this->_set_errcode(voa_errcode_api_campaign::TIME_CUT_DATA);
		}

		/* 开始时间不得大于结束时间 */
		if ($this->_params['stime'] > $this->_params['etime']) {
			return $this->_set_errcode(voa_errcode_api_campaign::TIME_CUT_END);
		}

		/* 抢单开始时间不得大于活动截至时间 */
		if ($this->_params['endtime'] < $this->_params['stime']) {
			return $this->_set_errcode(voa_errcode_api_campaign::OTIME_CUT_BITME);
		}

		/* 抢单结束时间不得大于活动截至时间 */
		if ($this->_params['endtime'] < $this->_params['etime']) {
			return $this->_set_errcode(voa_errcode_api_campaign::ETIME_CUT_BITM);
		}

		return true;
	}

	/**
	 * 验证活动主题信息
	 *
	 * @return bool|void
	 */
	private function __check_step() {


		$fields = array(
			'subject' => array('type' => 'string_trim', 'required' => true), // 活动主题
			'typeid' => array('type' => 'int', 'required' => true), // 附件ID
			'cover' => array('type' => 'int', 'required' => true), // 封面
			'begintime' => array('type' => 'string_trim', 'required' => true), // 开始时间
			'overtime' => array('type' => 'string_trim', 'required' => true), // 结束时间
			'address' => array('type' => 'string_trim', 'required' => true), // 活动地址
		);


		// 基本验证检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 活动主题不能为空
		if (empty($this->_params['subject'])) {
			$this->_set_errcode(voa_errcode_api_campaign::SUBJECT_NULL);
			return false;
		}

		// 活动类型不能为空
		if (empty($this->_params['typeid'])) {
			$this->_set_errcode(voa_errcode_api_campaign::TYPE_NULL);
			return false;
		}

		// 开始时间不能为空
		if (empty($this->_params['begintime'])) {
			$this->_set_errcode(voa_errcode_api_campaign::START_NULL);
			return false;
		}

		// 结束时间不能为空
		if (empty($this->_params['overtime'])) {
			$this->_set_errcode(voa_errcode_api_campaign::END_NULL);
			return false;
		}

		// 活动地点不能为空
		if (empty($this->_params['address'])) {
			$this->_set_errcode(voa_errcode_api_campaign::ADDRESS_NULL);
			return false;
		}

		/* 如果选择全天 则为00:00:00 */
		if (!$this->_params['btime'] || $this->_params['btime'] == '全天') {
			$this->_params['btime'] = '00:00:00';
		}

		/* 如果选择全天 则为23：59：59 */
		if (!$this->_params['time'] || $this->_params['time'] == '全天') {
			$this->_params['time'] = '23:59:59';
		}

		$this->_params['begintime'] = rstrtotime($this->_params['begintime'] . $this->_params['btime']);
		$this->_params['overtime'] = rstrtotime($this->_params['overtime'] . $this->_params['time']);

		// 结束时间不得大于开始时间
		if ($this->_params['overtime'] <= $this->_params['begintime']) {
			$this->_set_errcode(voa_errcode_api_campaign::TIME_CUT_END);
			return false;
		}

		// 结束时间不得小于当前时间
		if ($this->_params['overtime'] < time()) {
			$this->_set_errcode(voa_errcode_api_campaign::TIME_CUT_DATA);
			return false;
		}

		// 内容不能为空
		if (!isset($this->_params['content'])) {
			$this->_set_errcode(voa_errcode_api_campaign::CONTENT_NULL);
			return false;
		}

		return true;
	}

	/**
	 * 发送图文微信信息
	 *
	 * @param $act
	 * @param array $user
	 * @param $session
	 */
	private function _to_queues($act, array $user, $session) {
		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);

		foreach ($user as $v) {
			if ($v['m_uid'] == 0) {
				$touser = '@all';
			} else {
				$touser[] = $v['m_uid'];
			}
		}

		$dp = '';
		$viewurl = '';
		$msg_title = '您收到一条活动信息';
		$msg_desc = '主题：' . rhtmlspecialchars($act['subject']);
		if (!empty($act['cover'])) {
			$msg_picurl = voa_h_attach::attachment_url($act['cover'], 0);
		}
		$this->get_view_url($viewurl, $act['id']);
		$msg_url = $viewurl;

		// 发消息
		voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $touser, $dp, $msg_picurl);
	}

}