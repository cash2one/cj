<?php
/**
 * voa_d_oa_inspect_score
 * 巡店打分信息表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_inspect_score extends voa_d_abstruct {
	// 待评
	const STATE_DOING = 1;
	// 已评
	const STATE_DONE = 2;

	// 日期
	const TYPE_DATE = 1;
	// 周
	const TYPE_WEEK = 2;
	// 月
	const TYPE_MONTH = 3;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.inspect_score';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'isr_id';
		// 字段前缀
		$this->_prefield = 'isr_';

		parent::__construct();
	}

	/**
	 * 获取排行列表信息
	 * @param array $cr_ids
	 * @param int $insi_id 打分项id, 为 0 时, 为总分排行
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 */
	public function list_rank_join_mem($conds, $page_option = array(), $orderby = array()) {

		try {
			// 条件
			$this->_parse_conds($conds);

			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			$orderby = empty($orderby) ? array('`a`.`isr_score`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all('a.*', null, '_find_ciii_sql');
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
			// need fixed
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		$sql = "SELECT $fields FROM ".$this->_table." AS a"
			 . " LEFT JOIN ".$this->_table('inspect_mem')." AS b"
			 . " ON a.ins_id=b.ins_id "
			 . $this->_where()." ".$this->_g_o_l();

		return $sql;
	}
}
