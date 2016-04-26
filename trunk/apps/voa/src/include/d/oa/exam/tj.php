<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_d_oa_exam_tj extends voa_d_abstruct {

	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.exam_tj';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	public function delete_by_paper_id($paperid) {
		return $this->_delete_real_by_conds(array('paper_id' => $paperid));
	}

	public function list_stats_by_conds($condition, $start=0, $limit=0) {
		$limit=$limit?"LIMIT $start, $limit":'';
		$sql = "SELECT *, my_begin_time+my_time*60 as my_end_time FROM ".$this->_table." WHERE ".self::parse_conditions($condition)." AND `status`<'".self::STATUS_REMOVE."' ORDER BY my_end_time DESC $limit";

		$list = $this->_getAll($sql);

		$total = "SELECT COUNT(*) FROM ".$this->_table." WHERE ".self::parse_conditions($condition)." AND `status`<'".self::STATUS_REMOVE."'";
		$total = $this->_getOne($total);

		return array('list' => $list, 'total' => $total);
	}
	public static function parse_conditions($conditions = array()) {
		$wheres = array();
		
		foreach ($conditions as $field => $v) {
			
			$f_v = $v;
			$gule = '=';
			if (is_array($v)) {
				$f_v = $v[0];
				$gule = empty($v[1]) ? '=' : $v[1];
			}

			$wheres[] = db_help::field($field, $f_v, $gule);
		}


		return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}
}
