<?php
/**
 * voa_c_api_dailyreport_get_config
 * 获取日报应用的系统配置接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_dailyreport_get_config extends voa_c_api_dailyreport_base {

	public function execute() {
		// 移除不相干的参数
		unset($this->_p_sets['perpage']);
		// 输出为整形的参数
		$numbers = array('upload_image_min_count', 'upload_image_max_count', 'upload_image', 'pluginid');
		// 转换为整形
		foreach ($numbers as $_key) {
			$this->_p_sets[$_key] = (int)$this->_p_sets[$_key];
		}
		$this->_result = $this->_p_sets;

	}

}
