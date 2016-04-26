<?php
/**
 * voa_d_oa_jobtrain_category
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_d_oa_jobtrain_category extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.jobtrain_category';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	/**
	 * 根据$id获取子分类和自己
	 * @param int $id
	 * @return arr
	 */
	public function list_by_id_pid($id){
		$sql = "SELECT * FROM ".$this->_table." WHERE (`pid`='".$id."' OR id='".$id."') AND `status`<'".self::STATUS_DELETE."'";
		return $this->_getAll($sql);
	}
	/**
	 * 根据pid合计
	 * @param int $id
	 * @return int
	 */
	public function sum_by_pid($pid){
		$sum = "SELECT SUM(article_num) FROM ".$this->_table." WHERE status<'".self::STATUS_DELETE."' AND (id=$pid OR pid=$pid)";
		return $this->_getOne($sum);
	}
	

}