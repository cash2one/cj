<?php
/**
 * 项目表
 * $Author$
 * $Id$
 */

class voa_s_oa_project extends service {

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

	/** 计算所有总数 */
	public function count_all() {
		try {
			return voa_d_oa_project::count_all($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取所有列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_project::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取项目列表 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_project::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_project::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取已经完成项目的列表 */
	public function fetch_done_by_uids_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_project::fetch_done_by_uids_updated($uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid计算已经完成的项目数
	 * @param array $uids
	 * @param array $updated
	 * @throws service_exception
	 * @return number
	 */
	public function count_done_by_uids_updated($uids, $updated) {
		try {
			return voa_d_oa_project::count_done_by_uids_updated($uids, $updated, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取已关闭的项目列表 */
	public function fetch_closed_by_uids_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_project::fetch_closed_by_uids_updated($uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid计算已关闭的项目数
	 * @param array $uids
	 * @param number $updated
	 * @throws service_exception
	 * @return number
	 */
	public function count_closed_by_uids_updated($uids, $updated) {
		try {
			return voa_d_oa_project::count_closed_by_uids_updated($uids, $updated, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取我参加的项目列表 */
	public function fetch_my_by_uids_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_project::fetch_my_by_uids_updated((array)$uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid计算我参加的项目数
	 * @param array $uids
	 * @param number $updated
	 * @throws service_exception
	 * @return number
	 */
	public function count_my_by_uids_updated($uids, $updated) {
		try {
			return voa_d_oa_project::count_my_by_uids_updated((array)$uids, $updated, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid计算我参与的正在进行的项目数
	 * @param array $uids
	 * @param number $updated
	 * @throws service_exception
	 * @return number
	 */
	public function count_myactive_by_uids_updated($uids, $updated) {
		try {
			return voa_d_oa_project::count_myactive_by_uids_updated((array)$uids, $updated, $this->__shard_key);
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
			return voa_d_oa_project::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_project::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
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
			return voa_d_oa_project::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid删除信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_project::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算指定条件的项目数量
	 * @param array $conditions
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conditions($conditions = array()) {
		try {
			return (int) voa_d_oa_project::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的项目列表
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return (array) voa_d_oa_project::fetch_all_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
