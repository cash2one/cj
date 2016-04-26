<?php
/**
 * base.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/29  11:12
 */

class voa_c_cyadmin_api_account_base extends voa_c_cyadmin_api_base {

	protected $_uda_enterprise_add = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// UDA
		$this->_uda_enterprise_add = &uda::factory('voa_uda_cyadmin_enterprise_add');

		return true;
	}


}
