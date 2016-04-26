<?php
/**
 * detail.php
 * 订单
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_travel_order extends voa_d_abstruct {
	static $PAY_NOT = 1; // 未支付
	static $PAY_ING = 2; // 支付中
	static $PAY_SECCESS = 3; // 支付完成
	static $PAY_SEND = 4; // 已发货
	static $PAY_SIGN = 9; // 已签收
	static $PAY_CANCEL = 20; // 已取消
	static $PAY_INVALID = 30; // 已失效
	static $PAY_FAIL = 40; // 支付失败

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.order';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'orderid';

		parent::__construct(null);
	}




	/**
	 * 订单总数
	 * @param unknown $conds
	 * @throws service_exception
	 * @return Ambigous <Ambigous, boolean>
	 */
	public function count_by_conds_left_join($conds) {

		try {
			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			if (!empty($conds['sale_name'])) {
				$this->_condi('b.salename like ?', "%{$conds['sale_name']}%");
				unset($conds['sale_name']);
			}

			if (!empty($conds['customer_name'])) {
				$this->_condi('a.customer_name like ?', "%{$conds['customer_name']}%");
				unset($conds['customer_name']);
			}

			if (!empty($conds['created>?'])) {//开始时间
				$this->_condi('a.created > ?', $conds['created>?']);
				unset($conds['created>?']);
			}

			if (!empty($conds['created<?'])) {//结束时间
				$this->_condi('a.created < ?', $conds['created<?']);
				unset($conds['created<?']);
			}

			// 条件
			$this->_parse_conds($conds);


			!empty($page_option) && $this->_limit($page_option);

			return $this->_total('_find_ciii_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取订单信息
	 * @param array $cr_ids
	 * @param int $insi_id 打分项id, 为 0 时, 为总分排行
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 */
	public function list_mem_join_order($conds, $page_option = array(), $orderby = array()) {

		try {
			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			if (!empty($conds['sale_name'])) {
				$this->_condi('b.salename like ?', "%{$conds['sale_name']}%");
				unset($conds['sale_name']);
			}

			if (!empty($conds['customer_name'])) {
				$this->_condi('a.customer_name like ?', "%{$conds['customer_name']}%");
				unset($conds['customer_name']);
			}

			if (!empty($conds['created>?'])) {//开始时间
				$this->_condi('a.created > ?', $conds['created>?']);
				unset($conds['created>?']);
			}

			if (!empty($conds['created<?'])) {//结束时间
				$this->_condi('a.created < ?', $conds['created<?']);
				unset($conds['created<?']);
			}

			$this->_parse_conds($conds);

			!empty($page_option) && $this->_limit($page_option);

			// 排序
			$orderby = empty($orderby) ? array('`a`.`created`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all('a.* ,b.salename', null, '_find_ciii_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取查询的sql
	 * @param string $fields
	 * @return string
	 */
	protected function _find_ciii_sql($fields = '') {

		$fields = empty($fields) ? $this->_fields : $fields;
		if (empty($fields)) {
			$fields = "*";
		} else {
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		$sql = "SELECT $fields FROM ".$this->_table." AS a"
				. " LEFT JOIN ".$this->_table('order_goods')." AS b"
						. " ON a.orderid=b.order_id "
								. $this->_where()." ".$this->_g_o_l();

		return $sql;
	}

}
