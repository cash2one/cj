<?php
/**
 * 获取插件分组列表
 * $Author$
 * $Id$
 */

class voa_c_api_plugin_get_group extends voa_c_api_plugin_base {

	public function execute() {

		$list = voa_h_cache::get_instance()->get('plugin_group', 'oa');
		$data = array();
		foreach ($list as $_v) {
			$data[] = array(
				'groupid' => $_v['cpg_id'],
				'groupname' => $_v['cpg_name'],
				'ordernum' => $_v['cpg_ordernum']
			);
		}

		$this->_result = $data;
		return true;
	}

}
