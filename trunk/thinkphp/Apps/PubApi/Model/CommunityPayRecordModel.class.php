<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/9
 * Time: 下午7:31
 */

namespace PubApi\Model;

class CommunityPayRecordModel extends AbstractModel {

	/** 支付成功 */
	const PAY_SUCCESS = 1;
	const C_PAY_SUCCESS = '支付成功';
	/** 支付失败 */
	const PAY_FAIL = 2;
	const C_PAY_FAIL = '支付失败';
	/** 待支付 */
	const PAY_WAIT_FOR = 3;
	const C_PAY_WAIT_FOR = '待支付';

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 计算人员的支付总金额
	 * @param int $m_uid 人员ID
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 * @return int
	 */
	public function sum_pay_total($m_uid, $start_time = 0, $end_time = 0) {

		if (empty($m_uid)) {
			return 0;
		}

		$sql = "SELECT SUM(`pay_money`) FROM __TABLE__";
		//搜索条件
		$where = array(
			'm_uid = ?',
		);
		//搜索值
		$where_params = array(
			$m_uid,
		);
		// 时间范围
		if (!empty($start_time)) {
			$where[] = 'pay_time > ?';
			$where_params[] = $start_time;
		}
		if (!empty($end_time)) {
			$where[] = 'pay_time < ?';
			$where_params[] = $end_time;
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}
}