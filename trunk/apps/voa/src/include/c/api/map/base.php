<?php
/**
 * voa_c_api_map_base.php
 * $Author$
 * $Id$
 */
class voa_c_api_map_base extends voa_c_api_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}
