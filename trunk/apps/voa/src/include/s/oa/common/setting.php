<?php
/**
 * voa_s_oa_common_setting
 * 系统配置数据表
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_common_setting extends service {

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

	/**
	 * 根据 $key 读取配置信息
	 * @param string $key 配置名称
	 * @throws service_exception
	 */
	public function fetch($key) {
		try {
			return voa_d_oa_common_setting::fetch($key, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据键值数组读取配置信息
	 * @param array $keys
	 * @throws service_exception
	 */
	public function fetch_by_keys($keys) {
		try {
			return voa_d_oa_common_setting::fetch_by_keys($keys, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 读取所有
	 * @param int $start
	 * @param int $limit
	 * @throws service_exception
	 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_common_setting::fetch_all($start, $limit, $this->__shard_key);
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
			return voa_d_oa_common_setting::insert($data, $return_insert_id, $replace, $this->__shard_key);
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
			return voa_d_oa_common_setting::update($data, $conditions, $this->__shard_key);
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
			return voa_d_oa_common_setting::delete($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 以变量名为键名输出所有变量信息
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_setting() {
		try {
			return voa_d_oa_common_setting::fetch_all_setting($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 更新多个变量的值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @throws service_exception
	 * @return boolean
	 */
	public function update_setting($data) {
		try {
			return voa_d_oa_common_setting::update_setting($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
