<?php
/**
 * 培训应用基本控制器
 * Create By wowxavi
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_note_base extends voa_c_admincp_office_base
{

	//protected $_p_sets = array();

	protected function _before_action($action)
	{
		if (!parent::_before_action($action)) {
			return false;
		}
		//$this->_p_sets = voa_h_cache::get_instance()->get('plugin.note.setting', 'oa');
		return true;
	}

	protected function _after_action($action)
	{
		parent::_after_action($action);
		return true;
	}
}