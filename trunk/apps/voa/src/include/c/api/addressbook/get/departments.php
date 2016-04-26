<?php
/**
 * voa_c_admincp_manage_department_list
 * 企业后台/企业管理/部门管理/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_addressbook_get_departments extends voa_c_api_addressbook_base {

	public function execute() {

		$department_list = array();
		$uda_get = &uda::factory('voa_uda_frontend_department_get');
		$uda_get->list_all($department_list, 'all');
		$lists = array();
		/** 上/下级关系 */
		$p2c = array();
		foreach ($department_list as $key => $item) {//$_department_field_maps
			$row = $this->_department_format($item);
			$p2c[$item['cd_upid']][] = $item['cd_id'];
			$lists[] = $row;
		}

		$this->_result = array('total' => count($lists), 'p2c' => (object)$p2c, 'lists' => $lists);
	}

}
