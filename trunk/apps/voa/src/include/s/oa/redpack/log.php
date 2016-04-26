<?php
/**
 * log.php
 * 红包/领取日志service
 * $Author$
 * $Id$
 */

class voa_s_oa_redpack_log extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 判断总额是否超出，并给出剩余额度
	 *
	 * @param number $redpack_id 指定红包ID
	 * @param number $total 红包发放预算总额（即redpack表的total字段），单位：分
	 * @param number $remainder (引用结果)可分发的剩余额度，单位：分
	 * @return boolean
	 */
	public function is_total_out($redpack_id, $total, &$remainder) {

		// 剩余可分配的金额
		$remainder = $total - $this->total_money_by_redpack_id($redpack_id);
		if ($remainder <= 0) {
			return true;
		}

		return false;
	}

	/**
	 * 计算指定红包满足条件的人员领取次数，并返回是否已领取
	 *
	 * @param number $redpack_id 待领取的红包活动ID
	 * @param number $m_uid 领取人员的uid
	 * @param string $openid 领取人员的openid
	 * @param string $ip 领取人员的IP地址
	 * @param number $total (引用结果)领取次数
	 * @return boolean true=已领取,false=未领取过
	 */
	public function is_got($redpack_id, $m_uid, $openid, $ip, &$total) {

		// 计算符合当前人身份条件的领取次数
		$total = $this->count_got_total($redpack_id, $m_uid, $openid);
		// 已领取
		if ($total > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 格式化红包信息
	 *
	 * @param array $redpack
	 * @return array
	 */
	public function format(array &$rplog) {

		$rplog['_money'] = number_format($rplog['money'] / 100, 2);
		// 时间字段
		$time_fields = array('created', 'updated', 'deleted');
		foreach ($time_fields as $_key) {
			$rplog['_' . $_key] = rgmdate($rplog[$_key], 'Y-m-d H:i');
		}

		if (voa_d_oa_redpack_log::SEND_ST_NO == $rplog['sendst']) {
			$rplog['_sendst'] = '未发送';
		} else {
			$rplog['_sendst'] = '已发送';
		}

		return $rplog;
	}

}
