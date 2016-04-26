<?php
/**
 * voa_c_api_showroom_get_categoryright
 * 获取一篇文章
 * $Author$
 * $Id$
 */

class voa_c_api_showroom_get_categoryright extends voa_c_api_showroom_abstract {

	public function execute() {

		$tc_id = (int)$_POST['tc_id'];  // 获取文章ID

		if (1 > $tc_id) {
			return $this->_set_errcode(voa_errcode_oa_showroom::CATEGORY_ID_ERROR);
		}
		$uda = &uda::factory('voa_uda_frontend_showroom_action_categoryedit');
		$category = array();
		$result = array();
		$uda->get_rights($tc_id,$category);
		if ($category) {
			/** 人员权限 */
			if (isset($category['contacts']) && !empty($category['contacts'])) {
				foreach ($category['contacts'] as $contact) {
					$result[] = array('id' => $contact, 'input_name' => 'contacts[]');
				}
			}
			/** 部门 权限*/
			if (isset($category['deps']) && !empty($category['deps'])) {
				foreach ($category['deps'] as $deps) {
					$result[] = array('id' => $deps, 'input_name' => 'deps[]');
				}
			}

		}

		$this->_result = empty($result) ? array() : $result;

		return true;

	}

}
