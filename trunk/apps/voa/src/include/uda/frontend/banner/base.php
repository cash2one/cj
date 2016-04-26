<?php

/**
 * voa_uda_frontend_event_base
 * 统一数据访问/社群banner/基本控制
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_banner_base extends voa_uda_frontend_base {

	/** 应用信息 */
	protected $_plugin = array();

	public function __construct() {
		parent::__construct();

		$this->_plugin = voa_h_cache::get_instance()->get('plugin', 'oa');
	}

}
