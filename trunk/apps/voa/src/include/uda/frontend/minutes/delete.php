<?php
/**
 * voa_uda_frontend_minutes_delete
 * 统一数据访问/会议记录应用/删除操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_minutes_delete extends voa_uda_frontend_minutes_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 删除指定的会议记录及其所有相关数据
	 * @param mixed[string|array] $mi_ids
	 * @param array $shard_key
	 * @return boolean
	 */
	public function minutes($mi_ids = array(), $shard_key = array()) {

		$serv_minutes = &service::factory('voa_s_oa_minutes', $shard_key);
		$serv_minutes_post = &service::factory('voa_s_oa_minutes_post', $shard_key);
		$serv_minutes_mem = &service::factory('voa_s_oa_minutes_mem', $shard_key);

		try {

			// 开始删除过程
			$serv_minutes->begin();

			// 删除主表记录
			$serv_minutes->delete_by_ids($mi_ids);

			// 删除回复表记录
			$serv_minutes_post->delete_by_conditions(array('mi_id' => array($mi_ids)));

			// 删除进程表
			$serv_minutes_mem->delete_by_conditions(array('mi_id' => array($mi_ids)));

			// 提交删除过程
			$serv_minutes->commit();

		} catch (Exception $e) {
			$serv_minutes->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}


}
