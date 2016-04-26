<?php
/**
 * voa_uda_frontend_vnote_delete
 * 统一数据访问/备忘应用/数据删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_vnote_delete extends voa_uda_frontend_vnote_base {

	public function __construct() {
		parent::__construct();
	}

	public function delete_vnote() {
		/** 备忘ID */
		$vn_id = rintval($this->_request->get('vn_id'));

		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$serv_m = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		$serv_p = &service::factory('voa_s_oa_vnote_post', array('pluginid' => startup_env::get('pluginid')));

		/** 读取记录 */
		$vnote = $serv_m->fetch_by_vn_id_uid($vn_id, startup_env::get('wbs_uid'));
		if (empty($vnote)) {
			$this->errmsg(101, 'no_privilege');
			return false;
		}

		try {
			$serv->begin();

			/** 删除备忘 */
			$serv->delete_by_ids(array($vn_id));

			/** 删除备忘详情 */
			$serv_p->delete_by_vn_ids(array($vn_id));

			/** 删除分享人 */
			$serv_m->delete_by_vn_ids(array($vn_id));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}
}
