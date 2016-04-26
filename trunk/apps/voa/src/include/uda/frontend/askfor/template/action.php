<?php
/**
 * 删除、启用、禁用审批流程
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_template_action extends voa_uda_frontend_askfor_template_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 删除、启用、禁用审批流程
	 * @param array $askfor 审批主题信息
	 * @param array $post 审批详情信息
	 * @param array $mem 审批人信息
	 * @param array $cculist 抄送人信息
	 * @return boolean
	 */
	public function template_action($ids, $action = 'delete') {

		/** 取得数据 */
		$servt = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
		$servc = &service::factory('voa_s_oa_askfor_customcols', array('pluginid' => startup_env::get('pluginid')));

		if ($action == 'is_use' || $action == 'unuse') { //删除
			if ($action == 'is_use'){ //启用
				$data = array('is_use' => 1);
			} else {                   //禁用
				$data = array('is_use' => 0);
			}
			$servt->update($ids, $data);
		} else {
			try{
				$servt->begin();
				$servt->delete($ids);
				$servc->delete_by_aft_ids($ids);
				$servt->commit();
			} catch (Exception $e) {
				$servt->rollback();
			}

		}

		return true;
	}
}
