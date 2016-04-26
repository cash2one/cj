<?php

/**
 * 签到列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_list extends voa_c_frontend_sign_base {
	protected $_year;
	protected $_month;

	public function execute() {
		/** 当前年/月 */
		$this->_year = intval($this->request->get('year'));
		$this->_year = empty($this->_year) ? $this->_y : $this->_year;
		$this->_month = intval($this->request->get('month'));
		$this->_month = empty($this->_month) ? $this->_n : $this->_month + 1;

		/**
		 * refresh: 刷新签到信息
		 * list: 列表页
		 */
		$acs = array('refresh', 'list');
		$ac = $this->request->get('ac');
		$ac = in_array($ac, $acs) ? $ac : 'list';

		/** 执行对于操作 */
		$func = '_' . $ac;
		if (method_exists($this, $func)) {
			call_user_func(array($this, $func));

			return true;
		}

		$this->view->set('year_sel', $this->_year_sel);
		$this->view->set('month_sel', $this->_month_sel);
		$this->view->set('year', $this->_year);
		$this->view->set('month', $this->_month);
		$this->view->set('styles', $this->_styles);
		$this->view->set('sign_st', $this->_sign_st);
		$this->view->set('navtitle', '考勤列表');

		$this->_output('sign/' . $ac);
	}

	/** 签到列表 */
	protected function _refresh() {
		/** 起始时间和结束时间 */
		$btime = rstrtotime($this->_year . '-' . $this->_month . '-1 00:00:00');
		if (11 <= $this->_month) {
			$etime = rstrtotime(($this->_year + 1) . '-1-1 00:00:00');
		} else {
			$etime = rstrtotime($this->_year . '-' . ($this->_month + 1) . '-1 00:00:00');
		}

		/** 默认读取当前月份的签到情况 */
		$serv_rcd = &service::factory('voa_s_oa_sign_record', array('pluginid' => startup_env::get('pluginid')));
		$signs = $serv_rcd->fetch_by_uid_time(startup_env::get('wbs_uid'), $btime - 1, $etime);

		$sd = new voa_sign_detail();
		list($sign_day, $summary) = $sd->sort_summary($signs, $btime, $etime);
		$this->_json_message(array(
			'workdays' => $sign_day,
			'summary' => $summary
		));
	}
}

