<?php
/**
 * 巡店基类
 * @author zhuxun37
 */

class voa_c_admincp_office_inspect_base extends voa_c_admincp_office_base {

	protected function _get_region_list($parent = null) {

		$conds = array();
		if (!empty($parent)) {
			$conds['cr_parent_id'] = explode(',', $parent);
		} else {
			$conds['cr_parent_id'] = 0;
		}

		$list = array();
		$uda = new voa_uda_frontend_common_region_list();
		$uda->set_limit(false);
		$uda->execute($conds, $list);
		return $list;
	}
}
