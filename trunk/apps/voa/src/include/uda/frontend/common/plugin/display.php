<?php
/**
 * voa_uda_frontend_common_plugin_list
 * 应用uda
 * Create By ppker
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_plugin_display extends voa_uda_frontend_common_plugin_abstract {

	protected $display;
	
	public function __construct() {
		parent::__construct();
		$this->display = &service::factory('voa_s_oa_common_plugin_display');
	}
	
	/*
	 *查询
	 *@param array request
	 *@param array result
	 */
	public function my_list_plugin($m_uid, &$data) {
		$data = $this->display->fetch_order_list($m_uid);
	}

	/**
	 * 更新数据
	 * 数据表有数据则更新，无则插入新数据
	 */
	public function do_update($conds, $up_data, &$result){
		//查询
		/*$re = $this->display->fetch_by_conditions($conds);
		if($re) $result = $this->display->update($up_data, $conds);
		else {
			$insert_data = array(
				'm_uid' => $conds['m_uid'],
				'cpd_isfav' => 0,
				'cp_pluginid' => $conds['cp_pluginid'],
				'cpd_lastusetime' => startup_env::get('timestamp')
			);
			$result = $this->display->insert($insert_data);
		}*/
		$result = $this->display->update($up_data, $conds);	
	}

	/*
	 *整理数据
	 *@param array list
	 *@return array data
	 */
	 public function listformat ($list, &$data) {
		
	 }

}
