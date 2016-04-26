<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_s_oa_exam_tiku extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_exam_tiku();
		}
	}

	/**
	 * 验证标题基本合法性
	 * @param string $title
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_name($name) {
		$name = trim($name);
		if (!validator::is_required($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_exam::NAME_ERROR, $name);
		}

		return true;
	}

	public function list_by_ids($ids) {
		return $this->list_by_pks($ids);
	}

	public function get_by_id($id) {
		return $this->get($id);
	}

	public function list_all_tiku() {
		return $this->list_all();
	}

	public function update_count($id, $type) {
		return $this->_d_class->update_count($id, $type);
	}
}






