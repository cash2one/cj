<?php
/**
 *
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_banner_base extends voa_c_admincp_api_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	protected function _after_action($action) {

		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

}
