<?php
/**
 * voa_uda_frontend_secret_delete
 * 统一数据访问/秘密应用/删除操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_secret_delete extends voa_uda_frontend_secret_base {

	/**
	 * controller_request 实例
	 * @var object
	 */
	private $_request;

	public function __construct() {
		parent::__construct();
		$this->_request = controller_request::get_instance();
	}

	/**
	 * 删除指定的秘密主题及其所有相关数据
	 * @param mixed[string|array] $st_ids
	 * @param array $shard_key
	 * @return boolean
	 */
	public function secret($st_ids, $shard_key = array()) {

		$serv_secret = &service::factory('voa_s_oa_secret', $shard_key);

		try {

			$serv_secret_post = &service::factory('voa_s_oa_secret_post', $shard_key);

			// 开始删除过程
			$serv_secret->begin();

			// 删除主表记录
			$serv_secret->delete_by_ids($st_ids);

			// 删除回复表记录
			$serv_secret_post->delete_by_conditions(array('st_id' => array($st_ids)));

			// 提交删除过程
			$serv_secret->commit();

		} catch (Exception $e) {
			$serv_secret->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 按秘密回复id进行删除
	 * @param string|array $stp_ids
	 * @param array $shard_key
	 * @return boolean
	 */
	public function secret_post($stp_ids, $shard_key = array()) {

		try {

			$serv_secret_post = &service::factory('voa_s_oa_secret_post', $shard_key);
			$serv_secret_post->delete_by_ids($stp_ids);

		} catch (Exception $e) {
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}
}
