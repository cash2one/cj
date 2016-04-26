<?php
/**
 * 审批数据过滤
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_template_base extends voa_uda_frontend_askfor_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 验证标题
	 * @param string $str
	 * @return boolean
	 */
	public function val_name(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, '模板名为空');
			return false;
		}

		return true;
	}


	/**
	 * 验证审批人
	 * @param string $uid
	 * @return boolean
	 */
	public function val_uid(&$uid) {
		$uid = (int)$uid;
		if (0 >= $uid) {
			$this->errmsg(102, '审核人不正确');
			return false;
		}

		return true;
	}

	/**
	 * 验证流程ID
	 * @param string $uid
	 * @return boolean
	 */
	public function val_aft_id(&$aft_id) {
		$aft_id = (int)$aft_id;
		if (0 >= $aft_id) {
			$this->errmsg(102, '数据不正确');
			return false;
		}

		return true;
	}

}
