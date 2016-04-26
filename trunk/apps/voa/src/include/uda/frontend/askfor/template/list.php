<?php
/**
 * 审批相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_template_list extends voa_uda_frontend_askfor_template_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 取得审批列表
	 * @return array
	 */
	public function template_list(&$templates) {

		/** 取得数据 */
		$servt = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));

		$templates = $servt->fetch_all_for_is_use();

		return true;
	}
}
