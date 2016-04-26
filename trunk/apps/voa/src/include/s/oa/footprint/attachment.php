<?php
/**
 * 附件信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_footprint_attachment extends service {
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

	/** 获取所有列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_footprint_attachment::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取数据 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_footprint_attachment::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_footprint_attachment::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 读取回复的信息
	 * @param int $uid 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_footprint_attachment::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 fp_id 读取附件信息
	 * @param mix $fp_id
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 */
	public function fetch_by_fp_id($fp_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_footprint_attachment::fetch_by_fp_id((array)$fp_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 * @return number
	 */
	public function count_by_conditions($conditions) {
		try {
			return voa_d_oa_footprint_attachment::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的记录
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public function fetch_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_footprint_attachment::fetch_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_footprint_attachment::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_footprint_attachment::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据ID删除信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_footprint_attachment::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定条件的数据
	 * @param array $conditions
	 * @param array $shard_key
	 * @param boolean $unbuffered
	 * @return Ambigous <void, boolean>
	 */
	public function delete_by_conditions($conditions, $unbuffered = false) {
		try {
			return voa_d_oa_footprint_attachment::delete_by_conditions($conditions, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}

