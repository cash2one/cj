<?php
/**
 * 考试应用基本控制器
 * Create By wogu
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_exam_base extends voa_c_admincp_office_base {


	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}
}
