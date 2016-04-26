<?php
/**
 * 快递信息扩展表
 * $Author$
 * $Id$
 */

class voa_d_oa_express_mem extends voa_d_abstruct {

	//接件人
	const RECEIVE = 1;
	//收件人
	const GET = 2;
	//代领人
	const COLLECTION =3;
	//发件人
	const SEND = 4;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.express_mem';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'mid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}
	
	public function count_by_conds_left_join($conds) {
	
		try {
			// 条件
			$this->_parse_conds($conds);
	
			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			$this->_condi('a.'.$this->_prefield.'flag in(?)', array(2,3));
			$this->_condi('a.'.$this->_prefield.'uid=?', startup_env::get ( 'wbs_uid' ));
			!empty($page_option) && $this->_limit($page_option);
	
			return $this->_total('_find_ciii_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * 获取快递信息
	 * @param array $cr_ids
	 * @param int $insi_id 打分项id, 为 0 时, 为总分排行
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 */
	public function list_mem_join_express($conds, $page_option = array(), $orderby = array()) {
	
		try {
			// 条件
			$this->_parse_conds($conds);
	
			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			$this->_condi('a.'.$this->_prefield.'flag in(?)', array(2,3));
			$this->_condi('a.'.$this->_prefield.'uid=?', startup_env::get ( 'wbs_uid' ));
			!empty($page_option) && $this->_limit($page_option);
	
			// 排序
			$orderby = empty($orderby) ? array('`a`.`created`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}
	    
			return $this->_find_all('a.* ,b.flag as b_flag', null, '_find_ciii_sql');
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
				. " LEFT JOIN ".$this->_table('express')." AS b"
						. " ON a.eid=b.eid "
								. $this->_where()." ".$this->_g_o_l();

		return $sql;
	}

}
