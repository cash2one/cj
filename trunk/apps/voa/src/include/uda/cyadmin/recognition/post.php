<?php
/**
 * voa_uda_cyadmin_recognition_post
 * uda/总站/识别/结果发送到企业站接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_cyadmin_recognition_post extends voa_uda_cyadmin_base {

	/**
	 * 将名片识别结果提交给对应的企业OA站
	 * @param array $namecard recognition_namecard指定的记录
	 * @param array $recognition_data 识别数据
	 * @return boolean
	 */
	public function namecard_post_to_oasite($namecard, $recognition_data) {

		// 为数据添加域名信息
		$uda_enterprise_profile = &uda::factory('voa_uda_cyadmin_enterprise_profile');
		$tmp = array($namecard);
		$uda_enterprise_profile->data_append_domain($tmp);
		$namecard = $tmp[0];

		// 提交识别结果给企业站的数据字段
		// @see /apps/voa/src/include/server/recognition.php
		$args = array(
				'realname' => isset($recognition_data['realname']) ? $recognition_data['realname'] : '',
				'job' => isset($recognition_data['job']) ? $recognition_data['job'] : '',
				'mobilephone' => isset($recognition_data['mobilephone']) ? $recognition_data['mobilephone'] : '',
				'telephone' => isset($recognition_data['telephone']) ? $recognition_data['telephone'] : '',
				'email' => isset($recognition_data['email']) ? $recognition_data['email'] : '',
				'company' => isset($recognition_data['company']) ? $recognition_data['company'] : '',
				'address' => isset($recognition_data['address']) ? $recognition_data['address'] : '',
				'postcode' => isset($recognition_data['postcode']) ? $recognition_data['postcode'] : '',
				'qq' => '',
				'remark' => isset($recognition_data['other']) ? $recognition_data['other'] : '',
				'nc_id' => $namecard['nc_id'],
		);

		$oa_result = array();
		if ($this->qyoa_api($namecard['_domain'], 'recognition', 'namecard', $args, $oa_result)) {
			// 失败
			return false;
		} else {
			// 成功
			$this->errmsg(0, '');
			return true;
		}
	}

	/**
	 * 将票据识别数据提交给对应的企业OA站
	 * @param array $namecard
	 * @param array $recognition_data 识别的数据信息
	 * @return boolean
	 */
	public function bill_post_to_oasite($bill, $recognition_data) {

		// 为数据添加域名信息
		$uda_enterprise_profile = &uda::factory('voa_uda_cyadmin_enterprise_profile');
		$tmp = array($bill);
		$uda_enterprise_profile->data_append_domain($tmp);
		$bill = $tmp[0];

		// 票据金额
		$rbb_expend = 0;
		if (isset($recognition_data['amount'])) {
			// 将提交过来的“元”转换为“分”
			$rbb_expend = $recognition_data['amount'];
			$rbb_expend = number_format($rbb_expend, 2) * 100;
		}

		// 票据时间
		$rbb_time = 0;
		if (isset($recognition_data['date_year']) && isset($recognition_data['date_month']) && isset($recognition_data['date_day'])) {
			list($y, $m, $d) = explode('-', rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
			if ($recognition_data['date_year']) {
				$y = $recognition_data['date_year'];
			}
			if ($recognition_data['date_month']) {
				$m = $recognition_data['date_month'];
			}
			if ($recognition_data['date_day']) {
				$d = $recognition_data['date_day'];
			}
			$rbb_time = rstrtotime("{$y}-{$m}-{$d}");
		}

		// 提交识别结果给企业站的数据字段
		// @see /apps/voa/src/include/server/recognition.php

		$args = array(
				'rbb_expend' => $rbb_expend,
				'rbb_time' => $rbb_time,
				'bill_id' => $bill['bill_id'],
		);
		$oa_result = array();
		if ($this->qyoa_api($bill['_domain'], 'recognition', 'bill', $args, $oa_result)) {
			// 失败
			return false;
		} else {
			// 成功
			$this->errmsg(0, '');
			return true;
		}
	}

}
