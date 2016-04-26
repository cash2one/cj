<?php
/**
 * voa_s_oa_common_syscache
 * 系统缓存数据表
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_common_syscache extends service {

	/** 分库/分表的信息 */
	private $__shard_key = array();

	/**
	 * __construct
	 *
	 * @param  array $shard_key
	 * @return void
	*/
	public function __construct($shard_key = array()) {

		$this->__shard_key = $shard_key;
	}

	public function list_all() {

		try {
			return voa_d_oa_common_syscache::list_all($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据缓存名称读取缓存
	 * @param string $param 缓存名
	 * @throws service_exception
	 */
	public function fetch($name) {
		try {
			return voa_d_oa_common_syscache::fetch($name, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据缓存名称读取
	 * @param array $names 缓存名数组
	 * @throws service_exception
	 */
	public function fetch_by_names($names) {
		try {
			return voa_d_oa_common_syscache::fetch_by_names($names, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 数据入库
	 * @param array $data 入库数据
	 * @param boolean $return_insert_id 是否返回自增id
	 * @param boolean $replace 是否使用 replace into
	 * @throws service_exception
	 */
	public function insert($data, $return_insert_id, $replace) {
		try {
			return voa_d_oa_common_syscache::insert($data, $return_insert_id, $replace, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 更新
	 * @param array $data 更新数据
	 * @param array|string $conditions 更新条件
	 * @throws service_exception
	 */
	public function update($data, $conditions) {
		try {
			return voa_d_oa_common_syscache::update($data, $conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除记录
	 * @param array|string $conditions 更新条件
	 * @throws service_exception
	 */
	public function delete($conditions) {
		try {
			return voa_d_oa_common_syscache::delete($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
