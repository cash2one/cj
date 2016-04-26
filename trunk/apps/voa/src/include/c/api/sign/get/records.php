<?php

/**
 * 读取签到记录信息
 * $Author$
 * $Id$
 */
class voa_c_api_sign_get_records extends voa_c_api_sign_base {

	public function execute() {

		/** 起始时间和结束时间 */
		$start_date = $this->_get('start_date', '');
		$end_date = $this->_get('end_date', '');

		$btime = rstrtotime($start_date . ' 00:00:00');
		$etime = rstrtotime($end_date . ' 23:59:59');

		/** 只能取一个月范围内的(86400 * 31) */
		if ($btime - $etime > 2678400) {
			$this->_set_errcode(voa_errcode_api_sign::DATE_RANGE_ERROR);

			return true;
		}

		/** 默认读取当天的签到情况 */
		$serv_sr = &service::factory('voa_s_oa_sign_record', array ('pluginid' => startup_env::get('pluginid')));
		$records = $serv_sr->fetch_by_uid_time($this->_member['m_uid'], $btime, $etime);

		/** 上班卡/下班卡 */
		$rcds = array ();
		foreach ($records as $_r) {
			$rcds[] = array (
				'id' => $_r['sr_id'],
				'signtime' => $_r['sr_signtime'],
				'ip' => $_r['sr_ip'],
				'type' => $_r['sr_type'],
				'longitude' => $_r['sr_longitude'],
				'latitude' => $_r['sr_latitude'],
				'address' => $_r['sr_address']
			);
		}

		/** 设置返回值 */
		$this->_result = array (
			'start_date' => $start_date,
			'end_date' => $end_date,
			'records' => $rcds
		);

		return true;
	}
}
