<?php
/**
 * 日志/记录详细信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_thread_post extends voa_d_abstruct {

	/** 不是主题 */
	const FIRST_NO = 0;
	/** 是主题 */
	const FIRST_YES = 1;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.thread_post';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'pid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}
	
	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
	    return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND drp_status<%d", array(
	        self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
	    ), $shard_key);
	}
	
	/**
	 * 根据查询条件拼凑 sql 条件
	 * @param array $conditions 查询条件
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 */
	public static function parse_conditions($conditions = array()) {
	    $wheres = array();
	    /** 遍历条件 */
	    foreach ($conditions as $field => $v) {
	        /** 非当前表字段 */
	        if (!in_array($field, self::$__fields)) {
	            continue;
	        }
	
	        $f_v = $v;
	        $gule = '=';
	        /** 如果条件为数组, 则 */
	        if (is_array($v)) {
	            $f_v = $v[0];
	            $gule = empty($v[1]) ? '=' : $v[1];
	        }
	
	        $wheres[] = db_help::field($field, $f_v, $gule);
	    }
	
	    return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}

}
