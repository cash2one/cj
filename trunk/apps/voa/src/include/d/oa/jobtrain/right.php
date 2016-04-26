<?php
/**
 * voa_d_oa_jobtrain_right
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_d_oa_jobtrain_right extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.jobtrain_right';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	/**
	 * 物理删除权限记录
	 * @param array $conds
	 */
	public  function delete_real_records_by_conds($conds) {
		return $this->_delete_real_by_conds($conds);
	}

	/**
	 * 取得一篇内容的权限（剔除所有人可见权限）
	 * @param int $cid
	 * @return array
	 */
	public  function list_rights_for_single($cid) {
		$rights = $this->list_by_conds(array('cid' => $cid));
		//将is_all=1的选项剔除
		if (!empty($rights)) {
			foreach ($rights as $k => $right) {
				if ($right['is_all'] == 1){
					unset($rights[$k]);
				}
			}
		}

		return $rights;
	}

}