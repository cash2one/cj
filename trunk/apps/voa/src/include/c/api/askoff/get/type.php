<?php
/**
 * voa_c_api_askoff_get_type
 * 请假分类
 * $Author$
 * $Id$
 */

class voa_c_api_askoff_get_type extends voa_c_api_askoff_base {

	public function execute() {

		

		$types = voa_h_cache::get_instance()->get('plugin.askoff.setting', 'oa');
		
		$temp = array();
		foreach ($types['types'] as $k => $r)
		{
			$temp[] = array('id' => $k, 'value' => $r);
		}
		
		// 输出结果
		$this->_result = $temp;

		return true;
	}

}
