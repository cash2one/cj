<?php
/**
 * 购物车
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_d_oa_travel_ordergoods extends voa_d_abstruct {

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.order_goods';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'ogid';

		parent::__construct(null);
	}

	/**
	 * 设置查询条件
	 * @param array $conds 查询条件
	 */
	private function __set_conds($conds) {

		// 用户名称
		if (!empty($conds['username'])) {
			$this->_condi('a.salename like ?', "%{$conds['username']}%");
		}

		// 条件
		if (!empty($conds['saleuid'])) {
			$this->_condi('a.saleuid IN (?)', $conds['saleuid']);
		}

		// 部门
		if (!empty($conds['cd_id'])) {
			$this->_condi('a.cd_id=?', $conds['cd_id']);
		}

		// 支付时间
		if (!empty($conds['start_date'])) {
			$this->_condi('b.pay_time>?', rstrtotime($conds['start_date']));
		}

		if (!empty($conds['end_date'])) {
			$this->_condi('b.pay_time<?', rstrtotime($conds['end_date']));
		}

		// 支付完成
		$this->_condi("b.order_status IN (?)", array(
			voa_d_oa_travel_order::$PAY_SECCESS,
			voa_d_oa_travel_order::$PAY_SEND,
			voa_d_oa_travel_order::$PAY_SIGN
		));

		// 只查询未删除的
		$this->_condi('b.'.$this->_prefield.'status<?', self::STATUS_DELETE);

	}

	/**
	 * 统计销售的订单总数
	 * @param array $conds 查询条件
	 * @throws service_exception
	 * @return number
	 */
	public function count_saleorder($conds) {

		try {
			// 条件
			$this->__set_conds($conds);
			$sql = "SELECT COUNT(*) FROM (".$this->_find_turnover_sql('DISTINCT a.order_id').") AS c";

			return (int)$this->_total($sql);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计已支付的商品
	 * @param array $conds 查询条件
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function count_payed($conds) {

		try {
			// 条件
			$this->__set_conds($conds);

			return $this->_total('_find_turnover_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取数据数组
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 */
	public function list_payed($conds, $page_option = null, $orderby = array()) {

		try {
			// 条件
			$this->__set_conds($conds);

			if (!empty($conds['orderby'])) {
				$this->_order_by("b.pay_time", 'DESC');
			}

			!empty($page_option) && $this->_limit($page_option);

			return $this->_find_all('a.*', null, '_find_turnover_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取销售业绩与提成
	 * @param array $conds 查询条件
	 * @throws service_exception
	 * @return number
	 */
	public function get_turnover($conds) {

		try {
			// 条件
			$this->__set_conds($conds);
			$this->_group_by('a.saleuid');
			return $this->_find_row('a.saleuid, a.cd_id, a.salename, SUM(a.price * a.num) AS price, SUM(a.profit) as profit', '_find_turnover_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件计算数量
	 * @param array $conds
	 * @throws service_exception
	 * @return number
	 */
	public function count_turnover($conds) {

		try {
			// 条件
			$this->__set_conds($conds);
			// group by
			$this->_group_by('a.saleuid');
			$sql = "SELECT COUNT(*) FROM (".$this->_find_turnover_sql('COUNT(*)').") AS c";

			return (int)$this->_total($sql);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取数据数组
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 */
	public function list_turnover($conds, $page_option = null, $orderby = array()) {

		try {
			// 条件
			$this->__set_conds($conds);
			// group by
			$this->_group_by('a.saleuid');

			if (!empty($conds['orderby'])) {
				$this->_order_by("a.".$conds['orderby'], 'DESC');
			}

			!empty($page_option) && $this->_limit($page_option);

			return $this->_find_all('a.saleuid, a.cd_id, a.salename, SUM(a.price * a.num) AS price, SUM(a.profit) as profit', null, '_find_turnover_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}


	/**
	 * 根据条件读取数据数组
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 */
	public function list_order_goods($conds, $page_option = null, $orderby = array()) {

		try {
			// 条件
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			if (!empty($conds['sale_name'])) {
				$this->_condi('salename like ?', "%{$conds['sale_name']}%");
				unset($conds['sale_name']);
			}
			$this->_parse_conds($conds);
			// 排序
			$orderby = empty($orderby) ? array('`created`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			!empty($page_option) && $this->_limit($page_option);

			return $this->_find_all('DISTINCT order_id', null, '_find_ordergoods_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取数据数组
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 */
	public function count_order_goods($conds, $page_option = null, $orderby = array()) {

		try {
			// 条件
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			if (!empty($conds['sale_name'])) {
				$this->_condi('salename like ?', "%{$conds['sale_name']}%");
				unset($conds['sale_name']);
			}
			$this->_parse_conds($conds);

			// 排序
			$orderby = empty($orderby) ? array('`created`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			!empty($page_option) && $this->_limit($page_option);

			return $this->_find_one('count(DISTINCT order_id)', null, '_find_ordergoods_sql');
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
	protected function _find_ordergoods_sql($fields = '') {

		$fields = empty($fields) ? $this->_fields : $fields;
		if (empty($fields)) {
			$fields = "*";
		} else {
			// need fixed
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		$sql = "SELECT $fields FROM ".$this->_table
								. $this->_where()." ".$this->_g_o_l();

		return $sql;
	}



	/**
	 * 获取查询的sql
	 * @param string $fields
	 * @return string
	 */
	protected function _find_turnover_sql($fields = '') {

		$fields = empty($fields) ? $this->_fields : $fields;
		if (empty($fields)) {
			$fields = "*";
		} else {
			// need fixed
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		$sql = "SELECT $fields FROM ".$this->_table." AS a"
			 . " LEFT JOIN ".$this->_table('order')." AS b"
			 . " ON a.order_id=b.orderid "
			 . $this->_where()." ".$this->_g_o_l();

		return $sql;
	}



}
