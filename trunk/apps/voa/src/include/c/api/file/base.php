<?php
/**
 * voa_c_api_file_base.php
 * $Author$
 * $Id$
 */
class voa_c_api_file_base extends voa_c_api_base {


	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		/** 读取站点配置 */
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}
