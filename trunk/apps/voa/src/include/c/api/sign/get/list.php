<?php

/**
 * 按月查看签到记录信息
 * $Author$
 * $Id$
 */
class voa_c_api_sign_get_list extends voa_c_api_sign_base {
	/** 年份 */
	protected $_year;
	/** 月份 */
	protected $_month;

	public function execute() {
		/** 当前年/月 */
		$this->_year = intval($this->request->get('year'));
		$this->_year = empty($this->_year) ? $this->_y : $this->_year;
		$this->_month = intval($this->request->get('month'));
		$this->_month = empty($this->_month) ? $this->_n : $this->_month;

		/** 起始时间和结束时间 */
		$btime = rstrtotime($this->_year . '-' . $this->_month . '-1 00:00:00');
		if (12 <= $this->_month) {
			$etime = rstrtotime(($this->_year + 1) . '-1-1 00:00:00');
		} else {
			$etime = rstrtotime($this->_year . '-' . ($this->_month + 1) . '-1 00:00:00');
		}

		/** 默认读取当前月份的签到情况 */
		$serv_rcd = &service::factory('voa_s_oa_sign_record', array ('pluginid' => startup_env::get('pluginid')));
		$signs = $serv_rcd->fetch_by_uid_time($this->_member['m_uid'], $btime - 1, $etime);

		$sd = new voa_sign_detail();
		list($sign_day, $summary) = $sd->sort_summary($signs, $btime, $etime);

		/** 数据转换 */
		$workdays = array ();
		foreach ($sign_day as $_k => $_v) {
			$workdays[] = array ('signDate' => $_k, 'signType' => $_v);
		}

		$summaries = array ();
		foreach ($summary as $_k => $_v) {
			$summaries[] = array ('signType' => $_k, 'signCount' => $_v);
		}

		$this->_result = array (
			'workdays' => $workdays,
			'summary' => $summaries
		);

		return true;
	}

}
