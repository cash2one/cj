<?php
/**
 * voa_c_admincp_office_questionnaire_base
 * 企业后台/微办公管理/问卷/基本控制器
 * Create By
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_questionnaire_base extends voa_c_admincp_office_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		//FIXME ！！！涉及指定应用更新问题
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.questionnaire.setting', 'oa');

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}
}
