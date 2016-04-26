<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_s_oa_exam_paperdetail extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_exam_paperdetail();
		}
	}

	public function validator_ids($ids) {
		if(!is_array($ids))
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_DETAIL_TI_ID_ERROR, $ids);

		foreach ($ids as $v) {
			if(!is_numeric($v)) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_exam::PAPER_DETAIL_TI_ID_ERROR, $ids);
			}
		}
		return true;
	}

	public function delete_by_paperid($paperid) {
		return $this->_d_class->delete_by_paperid($paperid);
	}

	public function list_by_paperid($paperid) {
		return $this->_d_class->list_by_paperid($paperid);
	}

	public function real_delete_details($ids) {
		return $this->_d_class->real_delete_details($ids);
	}

}
