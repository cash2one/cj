<?php

/**
 * 读取当天签到记录信息
 * $Author$
 * $Id$
 */
class voa_c_api_sign_get_view extends voa_c_api_sign_base {

	public function execute() {

		/** 起始时间和结束时间 */
		$start_date = $this->_get('start_date', '');
		$end_date = $this->_get('end_date', '');

		$btime = rstrtotime($start_date . ' 00:00:00');
		$etime = rstrtotime($end_date . ' 23:59:59');

		/** 读取当天的签到情况 */
		$serv_sr = &service::factory('voa_s_oa_sign_record', array ('pluginid' => startup_env::get('pluginid')));
		$records = $serv_sr->fetch_by_uid_time('2', $btime, $etime);

		/** 数据过滤 */
		$fmt = &uda::factory('voa_uda_frontend_sign_format');
		$fmt->sign_record_list($records);

		/** 重构数组 */
		$list = array ();
		$uplist = array ();
		foreach ($records as $_id => $data) {
			if ($data['sr_type'] == 3) {
				$uplist[$data['sr_type']] = $data;
			} else {
				$list[$_id] = $data;
			}

		}

		/** 设置返回值 */
		$this->_result = array (
			'list' => $list,
			'uplist' => $uplist
		);

		return true;
	}
}
