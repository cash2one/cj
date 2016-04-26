<?php
/**
 * 培训/权限
 * $Author$
 * $Id$
 */

class voa_s_oa_jobtrain_right extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_jobtrain_right();
		}
	}
	/**
	 * 物理删除权限记录
	 * @param array $conds
	 */
	public function delete_real_records_by_conds($conds) {
		return $this->_d_class->delete_real_records_by_conds($conds);
	}

	/**
	 * 获取预览列表
	 * @param int $aid
	 * @return array
	 */
	public function list_right_users($aid) {
		return $this->_d_class->list_by_conds(array('aid' => $aid));
	}

	/**
	 * 取得一篇内容的权限（剔除所有人可见权限）
	 * @param int $cid
	 * @return array
	 */
	public function list_rights_for_single($cid) {
		return $this->_d_class->list_rights_for_single($cid);
	}
	
}