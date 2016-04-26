<?php

class voa_c_admincp_office_productive_base extends voa_c_admincp_office_base {

	protected function _get_region_list($parent = null) {
		$newConditions = array();
		if (!empty($parent)) {
			$newConditions['cr_parent_id'] = array(explode(',', $parent), 'in');
		} else {
			$newConditions['cr_parent_id'] = 0;
		}
		$db = &service::factory('voa_s_oa_common_region');
		$tmp	=	$db->fetch_by_conditions($newConditions);
		return $tmp;
	}
}
