<?php
/**
 * 微社群用户动态表
 * $Author$
 * $Id$
 */

class voa_s_oa_common_dynamic extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_common_dynamic();
		}
	}

	/**
	 * 根据主键获取信息
	 * @param $id
	 * @return Ambigous
	 * @throws service_exception
	 */
	public function del_by_tid($tid) {

		$result = $this->_d_class->list_by_conds(array('obj_id' => $tid, 'cp_identifier' => 'community'));
		if (!empty($result)) {
			$ids = array_column($result, 'id');
			$this->_d_class->delete_by_conds(array('id' => $ids));
		}

		return true;
	}
}

