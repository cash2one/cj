<?php
/**
 * 审批相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_template_get extends voa_uda_frontend_askfor_template_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 取得审批数据
	 * @param array $askfor 审批主题信息
	 * @param array $post 审批详情信息
	 * @param array $mem 审批人信息
	 * @param array $cculist 抄送人信息
	 * @return boolean
	 */
	public function template_get(&$template) {

		$aft_id = (int)$this->_request->get('aft_id');
		/** 取得数据 */
		$servt = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
		$servc = &service::factory('voa_s_oa_askfor_customcols', array('pluginid' => startup_env::get('pluginid')));
		$template = $servt->fetch_by_id($aft_id);
		if (empty($template)) {
			throw new Exception('数据错误');
		}
		$cols = $servc->fetch_by_aft_id($aft_id);
		if (!empty($cols)) {
			$template['cols'] = $cols;
		}

		return true;
	}
}
