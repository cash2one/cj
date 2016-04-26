<?php
/**
 * 审批相关的删除操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_delete extends voa_uda_frontend_askfor_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 审批数据入库
	 * @param array $askfor 审批主题信息
	 * @param array $post 审批详情信息
	 * @param array $mem 审批人信息
	 * @param array $cculist 抄送人信息
	 * @return boolean
	 */
	public function askfor_delete($af_ids) {

		$serv_afat = &service::factory('voa_s_oa_askfor_attachment', array('pluginid' => startup_env::get('pluginid')));
		/** 数据入库 */
		$servao = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$servpc = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servao->begin();

			$conds = array('af_id' => $af_ids);
			$servao->delete_by_ids($af_ids);
			$servpc->delete_by_af_ids($af_ids);
			$serv_afat->delete_by_conditions($conds);

			$servao->commit();
		} catch (Exception $e) {
			$servao->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(152, 'askfor_delete_failed');
			return false;
		}

		return true;
	}
}
