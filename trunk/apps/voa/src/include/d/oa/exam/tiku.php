<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_d_oa_exam_tiku extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.exam_tiku';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	public function update_count($id, $type) {
		$map = array('dan_num', 'tian_num', 'pan_num', 'duo_num');
		$field = $map[$type];
		unset($map[$type]);
		$s_ti = new voa_d_oa_exam_ti();
		$count=$s_ti->count_by_conds(array('tiku_id'=>$id, 'type'=>$type));
		
		$other=implode("+", $map);

		$sql = 'UPDATE '.$this->_table." SET {$field}={$count}, num={$other}+{$count} WHERE id={$id}";

		$sth = null;
		return $this->_execute($sql, $this->_bind_params, $sth);
	}
}

