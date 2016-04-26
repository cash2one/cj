<?php
/**
 * voa_c_api_goods_abstract
 * 商品基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_workorder_abstract extends voa_c_api_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.workorder.setting', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);

		return true;
	}

}
