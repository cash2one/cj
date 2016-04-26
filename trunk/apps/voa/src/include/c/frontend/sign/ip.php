<?php

/**
 * ip签到
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_ip extends voa_c_frontend_sign_base {

	public function execute() {
		$sign = new voa_sign_handle();
		$record = array();
		if (!$sign->sign($record, $this->_user, voa_sign_handle::TYPE_IP)) {
			if (empty($record)) {
				$error = '操作失败, 请重新尝试';
			} else {
				$error = (voa_d_oa_sign_record::TYPE_ON == $record['sr_type'] ? '签到' : '签退') . '失败, 请重新进行打卡操作';
			}

			$this->_error_message($error);

			return true;
		}

		$this->_success_message((voa_d_oa_sign_record::TYPE_ON == $record['sr_type'] ? '签到' : '签退') . '成功', '/sign/');
	}
}
