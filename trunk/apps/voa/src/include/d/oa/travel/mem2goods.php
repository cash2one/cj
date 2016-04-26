<?php
/**
 * mem2goods.php
 * 用户和产品对应
 *
 * $Author$
 * $Id$
 */
class voa_d_oa_travel_mem2goods extends voa_d_abstruct {


	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.travel_mem2goods';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'mgid';

		parent::__construct(null);
	}

	/**
	 * 根据 dataid 删除数据
	 * @param array $dataid dataid 数组
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete_by_dataid($dataid) {

		try {
			// 删除
			$this->_condi('dataid IN (?)', (array)$dataid);
			return $this->_delete();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function list_by_dataid($dataid) {

		try {
			$this->_condi('dataid IN (?)', (array)$dataid);
			return $this->_find_all();
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
	public function list_by_conds($conds, $page_option = null, $orderby = array()) {

		try {
			// 条件
			$conds_r = array();
			foreach ($conds as $_k => $_v) {
				if ('uid' == $_k) {
					$conds_r['a.'.$_k] = $_v;
				} else {
					$conds_r['b.'.$_k] = $_v;
				}
			}

			$this->_parse_conds($conds_r);
			$this->_group_by('a.dataid');

			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			$this->_condi('b.status<?', voa_d_oa_goods_data::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);
			// 排序
			$this->_order_by('a.fav', 'DESC');
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by('b.'.$_f, $_dir);
			}

			return $this->_find_all('b.*, a.fav, a.uid AS mg_uid, a.username AS mg_username', 'dataid', '_find_mg_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function count_by_conds($conds) {

		try {
			// 条件
			$conds_r = array();
			foreach ($conds as $_k => $_v) {
				if ('uid' == $_k) {
					$conds_r['a.'.$_k] = $_v;
				} else {
					$conds_r['b.'.$_k] = $_v;
				}
			}

			$this->_parse_conds($conds_r);

			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			$this->_condi('b.status<?', voa_d_oa_goods_data::STATUS_DELETE);

			// 临时保存 limit
			$limit = $this->_limit;
			$this->_limit = '';
			// 获取 sql
			$sql = $this->_find_mg_sql('COUNT(DISTINCT b.dataid) AS `count`');
			// 恢复 limit
			$this->_limit = $limit;
			// 执行
			$sth = null;
			if ($this->_execute($sql, $this->_bind_params, $sth)) {
				return $sth->fetchColumn();
			}

			return 0;
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
	protected function _find_mg_sql($fields = '') {

		$fields = empty($fields) ? $this->_fields : $fields;
		if (empty($fields)) {
			$fields = "*";
		} else {
			// need fixed
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		$sql = "SELECT $fields FROM ".$this->_table." AS a"
			 . " LEFT JOIN ".$this->_table('goods_data')." AS b"
			 . " ON a.dataid=b.dataid "
			 . $this->_where()." ".$this->_g_o_l();

		return $sql;
	}

}
