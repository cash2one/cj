<?php
/**
 * 培训/学习
 * $Author$
 * $Id$
 */

class voa_s_oa_jobtrain_study extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_jobtrain_study();
		}
	}
	/**
	 * 获取列表
	 * @return array
	 */
	public function list_study_by_conds($conds, $pager, $cata) {
		return $this->_d_class->list_study_by_conds($conds, $pager, $cata);
	}

}