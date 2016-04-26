<?php
/**
 * voa_uda_frontend_project_base
 * 统一数据访问/任务应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_frontend_project_base extends voa_uda_frontend_base {
	/** 配置信息 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.project.setting', 'oa');
	}

}
