<?php
/**
 * @Author: ppker
 * @Date:   2015-10-21 21:07:00
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 22:54:38
 */

class voa_c_cyadmin_company_base extends voa_c_cyadmin_base {

	protected $_adminer = array(); // 后台账户数据缓存mtc
	
	protected function _before_action($action) {

		// 获取缓存数据
		$this->_adminer = voa_h_cache::get_instance()->get('adminer', 'cyadmin');

		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

}
