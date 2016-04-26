<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/26 0026
 * Time: 14:02
 */
class voa_c_api_campaign_post_editcontent extends voa_c_api_campaign_base {

	// 不强制登录，允许外部访问
	protected function _before_action($action) {

		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		$step = intval($this->request->get('step'));
		$url = '';
		$result = array();
		$error = '';
		// url前缀
		$url_prefix = '/admincp/office/' . $this->_api_module . '/edit/pluginid/' . startup_env::get('pluginid');
		try {
			switch ($step) {
				// 活动推广主数据
				case 1 :
					try {
						$fields = array('subject' => array('type' => 'string_trim', 'required' => true), // 活动主题
'typeid' => array('type' => 'int', 'required' => true), // 附件ID
'cover' => array('type' => 'int', 'required' => true), // 封面
'begintime' => array('type' => 'string_trim', 'required' => true), 'overtime' => array('type' => 'string_trim', 'required' => true), 'address' => array('type' => 'string_trim', 'required' => true)) // 活动地址
;

						// 基本验证检查
						if (!$this->_check_params($fields)) {
							return false;
						}

						// 活动主题不能为空
						if (empty($this->_params['subject'])) {
							return $this->_set_errcode(voa_errcode_api_campaign::SUBJECT_NULL);
						}

						// 活动类型不能为空
						if (empty($this->_params['typeid'])) {
							return $this->_set_errcode(voa_errcode_api_campaign::TYPE_NULL);
						}

						// 开始时间不能为空
						if (empty($this->_params['begintime'])) {
							return $this->_set_errcode(voa_errcode_api_campaign::START_NULL);
						}

						// 结束时间不能为空
						if (empty($this->_params['overtime'])) {
							return $this->_set_errcode(voa_errcode_api_campaign::END_NULL);
						}

						// 活动地点不能为空
						if (empty($this->_params['address'])) {
							$this->_set_errcode(voa_errcode_api_campaign::ADDRESS_NULL);
							return false;
						}

						// 结束时间不得大于开始时间
						if ($this->_params['overtime'] < $this->_params['begintime']) {
							return $this->_set_errcode(voa_errcode_api_campaign::TIME_CUT_END);
						}

						// 结束时间不得小于当前时间
						if (rstrtotime($this->_params['overtime']) + 86400 < time()) {
							return $this->_set_errcode(voa_errcode_api_campaign::TIME_CUT_DATA);
						}

						// 内容不能为空
						if (!isset($this->_params['content'])) {
							$this->_set_errcode(voa_errcode_api_campaign::CONTENT_NULL);
							return false;
						}

						$uda = &uda::factory('voa_uda_frontend_campaign_campaign');
						$act = array();

						// 当前发布活动的作者
						$_POST['uid'] = 0;
						$_POST['username'] = 0;

						$rs = $uda->save($_POST, $act, $error);

						if ($rs) {
							$url = $url_prefix . '/acid/' . $act['id'] . '/?step=2&act=back&endtime=' . $act['overtime'];
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

						$url = $url_prefix . '/acid/' . $this->_params['cid'] . '/?step=3&act=back&endtime=' . $this->_params['endtime'];
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

						if (!$uda->edit_customcols($this->_params, $result, $this->session)) {
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
						$camp_data['push_flag'] = $this->request->get('push_flag');
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

		$this->_result = array('url' => $url);

		return true;
	}

	protected function _checkcols() {

		if (isset($this->_params['cols'])) {
			foreach($this->_params['cols'] as $v) {
				if ($v['name'] == '') {
					$this->_set_errcode(voa_errcode_api_campaign::COLS_NOT_NULL);
					return false;
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

		/* 如果选择全天 则为23：59：59 */
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
		if ($this->_params['endtime'] + 86400 < $this->_params['etime']) {
			return $this->_set_errcode(voa_errcode_api_campaign::ETIME_CUT_BITM);
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
	private function _to_queues($act,array $user, $session) {

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

		if ($act['is_push'] == 0) {
			$dp = '';
			$viewurl = '';
			$msg_title = '您收到一条活动信息';
			$msg_desc = '主题：' . rhtmlspecialchars($act['subject']);

			if (!empty($act['cover'])) {
				$msg_picurl = voa_h_attach::attachment_url($act['cover'], 0);
			}

			$this->get_view_url($viewurl, $act['id']);
			$msg_url = $viewurl;
		} elseif ($act['is_push'] == 1) {
			if ($act['push_flag'] == 0) {
				$msg_title = '您收到一条活动信息';
			} else {
				$msg_title = '您收到一条活动有更新';
			}

			$dp = '';
			$viewurl = '';
			$msg_desc = '主题：' . rhtmlspecialchars($act['subject']);

			if (!empty($act['cover'])) {
				$msg_picurl = voa_h_attach::attachment_url($act['cover'], 0);
			}

			$this->get_view_url($viewurl, $act['id']);
			$msg_url = $viewurl;
		}

		// 发消息
		voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $touser, $dp, $msg_picurl);
	}

}