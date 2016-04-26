<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/24
 * Time: 下午1:41
 */

namespace Common\Model;

class CompanyPaysettingModel extends AbstractModel {

	const PAY = 1;
	const NOTPAY = 0;
	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据条件和时间统计付费公司
	 * @return array
	 */
	public function list_by_conds_time(){

		// 设置条件
		$where = array(
			'pay_status = ?',
			'created > ?',
			'created <= ?',
			'status < ?',
		);

		$where_params = array(
			self::PAY,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 根据条件查询付费公司
	 * @param $ep_list array 公司id
	 * @return array
	 */
	public function list_by_conds_pay($ep_list) {

		$where = array(
			'status < ?',
			'ep_id IN (?)',
			'created <= ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			$ep_list,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 根据时间段查询新增付费公司
	 * @param $date
	 * @param $page_option
	 * @return array
	 */
	public function list_new_pay($date, $page_option, $ep_list = array()) {

		// 设置条件
		$where = array(
			'pay_status = ?',
			'created > ?',
			'created <= ?',
			'status < ?',
		);

		$where_params = array(
			self::PAY,
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']) + 86400,
			$this->get_st_delete(),
		);

		if(!empty($ep_list)) {
			$where[] = 'ep_id IN (?)';
			$where_params[] = $ep_list;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('created' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);
	}

	/**
	 * 统计公司以前的付费记录
	 * @param $date
	 * @param $ep_id
	 */
	public function count_pay_record($date, $ep_id) {

		$where = array(
			'status < ?',
			'created <= ?',
			'ep_id = ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime($date['s_time']),
			$ep_id,
		);

		return $this->_m->result("SELECT count(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}
}
