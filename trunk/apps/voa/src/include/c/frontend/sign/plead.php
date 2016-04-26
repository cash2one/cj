<?php

/**
 * 签到申诉列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_plead extends voa_c_frontend_sign_base {

	public function execute() {
		/** 当前年/月 */
		$year = intval($this->request->get('year'));
		$year = empty($year) ? $this->y : $year;
		$month = intval($this->request->get('month'));
		$month = empty($month) ? $this->_n : $month + 1;

		/** 处理申诉 */
		if ($this->_is_post()) {
			/** 判断主题/内容 */
			$subject = trim($this->request->get('subject'));
			$message = trim($this->request->get('message'));
			if (empty($subject) || empty($message)) {
				$this->_error_message('subject_message_too_short');
			}

			/** 申诉内容入库 */
			$serv = &service::factory('voa_s_oa_sign_plead', array('pluginid' => startup_env::get('pluginid')));
			$serv->insert(array(
				'sp_year' => $year,
				'sp_month' => $month,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'sp_subject' => $subject,
				'sp_message' => $message
			));

			$this->_success_message("操作成功", "/sign/list?year={$year}&month=" . ($month - 1));
		}

		/** 起始时间和结束时间 */
		$btime = rstrtotime($year . '-' . $month . '-1 00:00:00');
		if (11 <= $month) {
			$etime = rstrtotime(($year + 1) . '-1-1 00:00:00');
		} else {
			$etime = rstrtotime($year . '-' . ($month + 1) . '-1 00:00:00');
		}

		/** 默认读取当前月份的签到情况 */
		$serv_rcd = &service::factory('voa_s_oa_sign_record', array('pluginid' => startup_env::get('pluginid')));
		$signs = $serv_rcd->fetch_by_uid_time(startup_env::get('wbs_uid'), $btime - 1, $etime);

		/** 整理签到数据 */
		$sign_detail = new voa_sign_detail();
		list($sign_day, $summary) = $sign_detail->sort_summary($signs, $btime, $etime);

		/** 按打卡类型整理 */
		foreach ($signs as &$v) {
			$v['_signtime'] = rgmdate($v['sr_signtime'], 'H:i');
		}

		unset($v);
		$signs_by_type = $sign_detail->sign_sort_by_type($signs);
		/** 上班卡 */
		$signs_on = $signs_by_type[voa_d_oa_sign_record::TYPE_ON];
		/** 下班卡 */
		$signs_off = $signs_by_type[voa_d_oa_sign_record::TYPE_OFF];

		/** 列出所有日期 */
		$ynjs = array_keys($sign_day);
		/** 申诉框默认内容 */
		$def_msg = implode(":\n", $ynjs) . ":";

		$this->view->set('ac', $this->action_name);
		$this->view->set('year', $year);
		$this->view->set('month', $month);
		$this->view->set('def_msg', $def_msg);
		$this->view->set('sign_day', $sign_day);
		$this->view->set('signs_on', $signs_on);
		$this->view->set('signs_off', $signs_off);
		$this->view->set('sign_st', $this->_sign_st);
		$this->view->set('navtitle', '考勤申诉');

		$this->_output('sign/plead');
	}
}

