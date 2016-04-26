<?php
/**
 * 名片相关的删除操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_namecard_delete extends voa_uda_frontend_namecard_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 删除指定的名片及其相关所有数据
	 * @param int $nc_id
	 * @param array $shard_key
	 * @return boolean
	 */
	public function namecard_delete($nc_id, $shard_key = array()) {
		$serv_nc = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$namecard = $serv_nc->fetch_by_id($nc_id);

		/** 判断权限 */
		if (empty($namecard) || startup_env::get('wbs_uid') != $namecard['m_uid']) {
			$this->errmsg(100, 'namecard_is_not_exists');
			return false;
		}

		$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv_c = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$serv_j = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		try {
			/** 开始删除过程 */
			$serv_nc->begin();

			/** 删除当前名片 */
			$serv_nc->delete_by_ids(array($nc_id));

			/** 统计数 -1 */
			$ncf_id = (int)$namecard['ncf_id'];
			$ncc_id = (int)$namecard['ncc_id'];
			$ncj_id = (int)$namecard['ncj_id'];
			0 < $ncf_id && $serv_f->update_num($ncf_id, '-');
			0 < $ncc_id && $serv_c->update_num($ncc_id, '-');
			0 < $ncj_id && $serv_j->update_num($ncj_id, '-');

			/** 提交删除过程 */
			$serv_nc->commit();
		} catch (Exception $e) {
			$serv_nc->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		/** 删除搜索数据 */
		$serv_so = &service::factory('voa_s_oa_namecard_search', array('pluginid' => startup_env::get('pluginid')));
		$serv_so->delete_by_conditions(array('m_uid' => $namecard['m_uid'], 'nc_id' => $namecard['nc_id']));

		return true;
	}
}
