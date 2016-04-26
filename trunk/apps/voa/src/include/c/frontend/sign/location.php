<?php

/**
 * 地理位置签到
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_location extends voa_c_frontend_sign_base {

	public function execute() {

		if (!$this->_allow_sign) {
			// 非工作日不允许签到
			$this->_error_message('非工作日不需要进行签到');

			return true;
		}

		$location = array();
		// 外部传递的经纬度
		$g_longitude = $this->request->post('longitude');
		$g_latitude = $this->request->post('latitude');
		if (is_numeric($g_longitude) && is_numeric($g_latitude) && validator::is_in_range($g_longitude, - 180, 180) && validator::is_in_range($g_latitude, - 90, 90)) {
			$location = array(
				'longitude' => $g_longitude,
				'latitude' => $g_latitude
			);
		}

		$sign = new voa_sign_handle();
		$record = array();
		if (!$sign->sign($record, $this->_user, voa_sign_handle::TYPE_LOCATION, $location)) {
			if (empty($record['sr_type'])) {
				$error = empty($sign->error) ? '操作失败, 请重新尝试' : preg_replace('/(\d+:)/i', '', $sign->error);
			} else {
				$error = (voa_d_oa_sign_record::TYPE_ON == $record['sr_type'] ? '签到' : (voa_d_oa_sign_record::TYPE_OFF == $record['sr_type'] ? '签退' : '上报地理位置')) . '失败, 请重新进行打卡操作';
			}

			$this->_error_message($error);

			return true;
		}

		$this->_success_message((voa_d_oa_sign_record::TYPE_ON == $record['sr_type'] ? '签到' : (voa_d_oa_sign_record::TYPE_OFF == $record['sr_type'] ? '签退' : '上报地理位置')) . '成功', '/sign/');
	}
}
