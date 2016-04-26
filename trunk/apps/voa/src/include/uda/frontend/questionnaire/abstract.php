<?php
/**
 * $Author$
 * $Id$
 */

class voa_uda_frontend_questionnaire_abstract extends voa_uda_frontend_base {

	// 全局配置信息
	protected $_sets = array();
	// 插件配置
	protected $_p_sets = array();

	public function __construct() {

		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.questionnaire.setting', 'oa');
	}

}
