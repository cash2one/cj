<?php
/**
 * voa_uda_frontend_common_plugin_group
 * 应用uda
 * Create By ppker
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_plugin_group extends voa_uda_frontend_common_plugin_abstract {

	protected $group;
	
	public function __construct() {
		parent::__construct();
		$this->group = &service::factory('voa_s_oa_common_plugin_group');
	}
	
	/*
	 *查询
	 *@param array request
	 *@param array result
	 */
	public function get_list() {
		return $this->group->fetch_all();
	}


}
