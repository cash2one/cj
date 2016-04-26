<?php

/**
 * voa_uda_frontend_event_sign
 * 应用uda
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_event_sign extends voa_uda_frontend_event_base {

	//审核表
	protected $serv;
	protected $serv_partake;

	public function __construct() {
		parent::__construct();
		$this->serv = &service::factory('voa_s_oa_event');
		$this->serv_invite = &service::factory('voa_s_oa_event_invite');
		$this->serv_partake = &service::factory('voa_s_oa_event_partake');
		$this->serv_nopartake = &service::factory('voa_s_oa_event_nopartake');
		$this->serv_outsider = &service::factory('voa_s_oa_event_outsider');
	}

	public function doit(array $request, &$result) {
		$scheme = config::get('voa.oa_http_scheme');
		switch ($request['ac']) {
			case 'join'://报名
				$m_uid = startup_env::get('wbs_uid');
				//获取人员信息
				$member = voa_h_user::get($m_uid);
				$cd_id = $member['cd_id'];
				//判断
				$res = $this->check_join($m_uid, $cd_id, $request['acid']);
				if ($res == 'true') {
					//新增报名
					$data['acid'] = $request['acid'];
					$data['remark'] = $request['message'];
					$data['m_uid'] = $m_uid;
					$data['name'] = $member['m_username'];
					$data['type'] = 1;
					$this->serv_partake->insert($data);
					$npe = urlencode(authcode($m_uid, 'zhoutao', 'ENCODE', '0'));
					$result = array('url' => '/frontend/event/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $request['acid'] . '&npe=' . $npe, 'message' => "报名成功");

					$servdata = $this->serv->get($request['acid']);
					$m_uidto = $servdata['m_uid'];
					/** 发送微信消息 */
					//发给发布人
					$msg_title = "您发布的活动有人报名啦";
					//发给报名人
					$msg_title_self = "您报名参加活动成功";
					$msg_desc = "主题：【" . $servdata['title'] . "】\n";
					$msg_desc .= "活动时间：" . rgmdate($servdata['start_time'], "m-d H:i") . " 到 " . rgmdate($servdata['end_time'], "m-d H:i") . "\n";
					$msg_url = $scheme . $request['_setting']['domain'] . '/frontend/event/view/?acid=' . $servdata['acid'] . '&pluginid=' . startup_env::get('pluginid');
					try {
						if (!empty($m_uidto)) {
							voa_h_qymsg::push_news_send_queue($request['session'], $msg_title, $msg_desc, $msg_url, array("$m_uidto"), array(), '', 0, 0, -1);//发给发布人
						}
						if (!empty($m_uid)) {
							voa_h_qymsg::push_news_send_queue($request['session'], $msg_title_self, $msg_desc, $msg_url, array("$m_uid"), array(), '', 0, 0, -1);//发给报名人
						}
					} catch (Exception $e) {

					}
				} else {
					switch ($res) {
						case '1':
							$result = array('message' => "报名人数超过限制");
							break;
						case '2':
							$result = array('message' => "被邀请的人员列表为空");
							break;
						case '3':
							$result = array('message' => "没有被邀请");
							break;
						case '4':
							$result = array('message' => '不能重复报名');
							break;
					}
				}
				break;
			case 'apply'://申请取消
				$apply = $request['message'];//申请理由
				if (empty($request['message'])) {
					$result = array('url' => '/frontend/event/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $request['acid'], 'message' => "请输入退出原因");
					break;
				}
				$partake = $this->serv_partake->get_by_conds(array('acid' => $request['acid'], 'm_uid' => $request['m_uid']));
				// 判断是否已经签到，完成签到不能取消
				if ($partake['check'] == '1') {
					$result = array('url' => '/frontend/event/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $request['acid'], 'message' => "签到后不能退出");
					break;
				}
				$apid = $partake['apid'];
				$this->serv_partake->update_by_conds(array('apid' => $apid), array('type' => 2));
				$nopartake = $this->serv_nopartake->list_by_conds(array('apid' => $apid));
				if (empty($nopartake)) {
					$res = $this->serv_nopartake->insert(array('apid' => $apid, 'apply' => $apply));
				} else {
					$res = $this->serv_nopartake->update_by_conds(array('apid' => $apid), array('apply' => $apply));
				}
				$result = array('url' => '/frontend/event/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $request['acid'], 'message' => "已发送申请取消");
				/** 发送微信消息 */
				$servdata = $this->serv->get($request['acid']);
				$m_uidto = $servdata['m_uid'];
				$msg_title = "您收到1条申请取消报名的信息";
				$msg_desc = "主题：【" . $servdata['title'] . "】\n";
				$msg_desc .= "活动时间：" . rgmdate($servdata['start_time'], "m-d H:i") . " 到 " . rgmdate($servdata['end_time'], "m-d H:i") . "\n";
				$msg_url = $scheme . $request['_setting']['domain'] . '/frontend/event/return/?apid=' . $apid . '&acid=' . $partake['acid'] . '&pluginid=' . startup_env::get('pluginid');
				try {
					if (!empty($m_uidto)) {
						voa_h_qymsg::push_news_send_queue($request['session'], $msg_title, $msg_desc, $msg_url, array("$m_uidto"), array(), '', 0, 0, -1);//发送
					}
				} catch (Exception $e) {

				}
				break;
			case 'view'://显示数据
				$apid = $request['apid'];
				$partake = $this->serv_partake->get_by_conds(array('apid' => $apid));
				$acid = $partake['acid'];
				$nopartake = $this->serv_nopartake->get_by_conds(array('apid' => $apid));
				$event = $this->serv->get_by_conds(array('acid' => $acid));
				$result['title'] = $event['title'];
				$result['acid'] = $event['acid'];
				$result['apid'] = $apid;
				$result['m_uid'] = $partake['m_uid'];
				$result['name'] = $partake['name'];
				$result['content'] = $nopartake['apply'];
				break;
			case 'reject'://驳回
				$apid = $request['apid'];
				$m_uid = $request['m_uid'];
				$message = $request['message'];
				$partake = $this->serv_partake->get_by_conds(array('apid' => $apid));
				if (empty($partake)) {
					$result = array('message' => '申请记录不存在');
					return false;
				}
				if ($partake['type'] != 2) {
					$result = array('message' => '用户没有申请退出活动');
					return false;
				}
				$result = $this->serv_partake->update_by_conds(array('apid' => $apid), array('type' => 1));
				$result = $this->serv_nopartake->update_by_conds(array('apid' => $apid), array('reject' => $message));
				/** 发送微信消息 */
				$acid = $partake['acid'];
				$servdata = $this->serv->get($acid);
				$msg_title = "您申请的取消报名已被驳回";
				$msg_desc = "主题：【" . $servdata['title'] . "】\n";
				$msg_desc .= "活动时间：" . rgmdate($servdata['start_time'], "m-d H:i") . " 到 " . rgmdate($servdata['end_time'], "m-d H:i") . "\n";
				$msg_desc .= "驳回理由：" . $message . "\n";
				$msg_url = $scheme . $request['_setting']['domain'] . '/frontend/event/view/?acid=' . $acid . '&pluginid=' . startup_env::get('pluginid');
				try {
					if (!empty($m_uid)) {
						voa_h_qymsg::push_news_send_queue($request['session'], $msg_title, $msg_desc, $msg_url, array("$m_uid"), array(), '', 0, 0, -1);//发送
					}
				} catch (Exception $e) {

				}
				$result = array('url' => '/frontend/event/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $acid, 'message' => "驳回成功");
				break;
			case 'agree'://同意
				$apid = $request['apid'];
				$partake = $this->serv_partake->get_by_conds(array('apid' => $apid));
				if (empty($partake)) {
					$result = array('message' => '申请记录不存在');
					return false;
				}
				if ($partake['type'] != 2) {
					$result = array('message' => '用户没有申请退出活动');
					return false;
				}
				$this->serv_partake->delete_by_conds(array('apid' => $apid));
				/** 发送微信消息 */
				$acid = $partake['acid'];
				$m_uid = $partake['m_uid'];
				$servdata = $this->serv->get($acid);
				$msg_title = "您的报名已取消";
				$msg_desc = "主题：【" . $servdata['title'] . "】\n";
				$msg_desc .= "活动时间：" . rgmdate($servdata['start_time'], "m-d H:i") . " 到 " . rgmdate($servdata['end_time'], "m-d H:i") . "\n";
				$msg_url = $scheme . $request['_setting']['domain'] . '/frontend/event/view/?acid=' . $acid . '&pluginid=' . startup_env::get('pluginid');
				try {
					if (!empty($m_uid)) {
						voa_h_qymsg::push_news_send_queue($request['session'], $msg_title, $msg_desc, $msg_url, array("$m_uid"), array(), '', 0, 0, -1);//发送
					}
				} catch (Exception $e) {

				}
				$result = array('url' => '/frontend/event/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $acid, 'message' => "同意成功");
				break;
		}
		return true;
	}

	/*
	 *检查报名人是否在邀请人员中并且是否有限制人数
	 *@param int m_uid 用户ID
	 *@param int cd_id 部门ID
	 *@param int acid 活动ID
	 *@return bool True：允许报名
	 */
	public function check_join($m_uid, $cd_id, $acid) {

		//判断是否已报名
		$is_sign = $this->serv_partake->count_by_conds(array('acid' => $acid, 'm_uid' => $m_uid));
		if (!empty($is_sign)) {
			return 4;
		}

		$event = $this->serv->get($acid);
		$np = $event['np'];//限制人数
		$partake = $this->serv_partake->list_by_conds(array('acid' => $acid));
		$outsider = $this->serv_outsider->list_by_conds(array('acid' => $acid));
		if (!$outsider) {
			$outsider = null;
		}
		if (!$partake) {
			$partake = null;
		}
		$invite = $this->serv_invite->list_by_conds(array('acid' => $acid));
		$num = count($partake) + count($outsider);
		if ($np != 0 && $np == $num) {
			return 1; // 达到限制人数
		}
		if (empty($invite)) {
			return 2; // 没有邀请任何内部人员
		}
		foreach ($invite as $v) {
			//部门
			if ($v['type'] == 1 && $v['primary_id'] == $cd_id) {
				return true;
			}
			// 在范围内
			if ($v['type'] == 1 && voa_h_user::in_area($m_uid, null, null, array('0' => $v['primary_id']))) {
				return true;
			}
			//人员
			if ($v['type'] == 2 && $v['primary_id'] == $m_uid) {
				return true;
			}
			//全公司
			if ($v['type'] == 1 &&
				($v['primary_id'] == -1 || $v['primary_id'] == 0)
			) {
				return true;
			}
		}
		return 3; // 没有被邀请
	}

	/**
	 * 判断外部报名人的信息申请
	 * @param $in           报名信息
	 * @param $out          是否报名成功
	 * @param $title        活动标题
	 * @param $other_feild  自定义字段数据
	 * @return bool|int
	 * @throws help_exception
	 */
	public function out_post_deal($in, &$out, &$title, &$other_feild) {
		// 查询活动限制人数
		$np = $this->serv->get_by_conds($in['acid']);
		$np = $np['np'];
		// 查询内部活动报名人数
		$internal_rule = array(
			'acid' => $in['acid'],
			'type' => 1
		);
		$internal_apply = $this->serv_partake->count_by_conds($internal_rule);
		// 查询外部活动报名人数
		$external_apply = $this->serv_outsider->count_by_conds(array('acid' => $in['acid']));
		// 计算剩余的人数，是否能报名
		$all_apply = $internal_apply + $external_apply;
		$surplue_num = (int)$np - (int)$all_apply;
		if ($np != 0) {
			if ($surplue_num < 1) {
				return $out = 2;
			}
		}

		$one = array_slice($in, 1, 4);
		// 去掉固定字段信息，留下自定义字段
		unset($in['formhash']);
		unset($in['acid']);
		unset($in['outname']);
		unset($in['outphone']);
		unset($in['remark']);
		$fields = array(
			array('acid', self::VAR_INT, null, null, true),
			array('outname', self::VAR_STR, null, null, true),
			array('outphone', self::VAR_STR, null, null, true),
			array('remark', self::VAR_STR, null, null, true),
		);
		// 手机号判断是否11位
		if (validator::is_mobile($one['outphone']) != '1') {
			return false;
		}
		$worked_data = array();
		if (!$this->extract_field($worked_data, $fields, $one)) {
			return false;
		}
		$worked_data_text = array(
			'acid' => $worked_data['acid'],
			'outphone' => $worked_data['outphone']
		);
		if ($this->serv_outsider->list_by_conds($worked_data_text)) {
			return $out = 1; //已经报名过
		}
		$title = '';
		$this->gettitle($worked_data_text['acid'], $title);
		$other_feild = $in;
		$other = serialize($in);
		$worked_data['other'] = $other;
		$this->serv_outsider->insert($worked_data);
		return true;
	}

	/**
	 * 根据acid获取标题
	 * @param $acid     活动ID
	 * @param $title    活动标题
	 */
	public function gettitle($acid, &$title) {
		$outsider = $this->serv->get_by_conds($acid);
		$title = isset($outsider['title']) ? $outsider['title'] : '';
		return true;
	}
}
