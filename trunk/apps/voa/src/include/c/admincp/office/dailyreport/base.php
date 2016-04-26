<?php
/**
 * voa_c_admincp_office_dailyreport_base
 * 企业后台/微办公管理/日报/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_dailyreport_base extends voa_c_admincp_office_base {


    
	public $uda_dailyreport = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->uda_dailyreport = &uda::factory('voa_uda_frontend_dailyreport_base');
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 获取指定日报的索引信息
	 * @param number $dr_id
	 * @param number $cp_pluginid
	 * @return array
	 */
	protected function _get_dailyreport($dr_id, $cp_pluginid) {
		return (array) $this->_service_single('dailyreport', $cp_pluginid, 'fetch_by_id', $dr_id);
	}
	
	/**
	 * 设置是否分tab显示
	 * @return bool
	 */
/* 	private function _set_flag(){
	    switch ($this->_module_plugin['cp_identifier']){
	        case 'askoff':
	            $flag = true;
	            break;
	        case 'reimburse':
	            $flag = true;
	            break;
	        case 'footprint':
	            $flag = true;
	            break;
	        case 'sign':
	            $flag = true;
	            break;
	        case 'dailyreport':
	            $flag = true;
	            break;
	        default :
	            $flag = false;
	    }
	    return $flag;
	} */
	
	
}
