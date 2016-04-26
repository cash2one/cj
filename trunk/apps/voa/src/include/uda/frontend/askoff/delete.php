<?php
/**
 * 请假相关的删除操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askoff_delete extends voa_uda_frontend_askoff_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 删除指定的请假请求极其相关所有数据
	 * @param mixed $ao_ids
	 * @param array $shard_key
	 * @return boolean
	 */
	public function askoff_delete($ao_ids = array(), $shard_key = array()) {

		$serv_ao = &service::factory('voa_s_oa_askoff', $shard_key);
		$serv_aopt = &service::factory('voa_s_oa_askoff_post', $shard_key);
		$serv_aopc = &service::factory('voa_s_oa_askoff_proc', $shard_key);
		try {
			// 开始删除过程
			$serv_ao->begin();

			// 删除主表记录
			$serv_ao->delete_by_ids($ao_ids);

			// 删除回复表记录
			$serv_aopt->delete_by_conditions(array('ao_id' => array($ao_ids)));

			// 删除进程表
			$serv_aopc->delete_by_conditions(array('ao_id' => array($ao_ids)));

			// 提交删除过程
			$serv_ao->commit();
		} catch (Exception $e) {
			$serv_ao->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}
}
