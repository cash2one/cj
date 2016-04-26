<?php

/**
 * voa_c_admincp_office_interface_base
 * 测试应用/后台/基类
 * Created by gaosong
 */
class voa_c_admincp_office_interface_base extends voa_c_admincp_office_base {

	protected function _before_action($action) {

		if (! parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	//初始化参数过滤掉为空的值
	protected function _init_params($get, &$params) {
		foreach ($params as $key => &$v) {
			if (!empty($get[$key]) &&
					trim($get[$key]) != '') {
						$v = $get[$key];
					}
		}
	}

}
