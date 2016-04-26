<?php
/**
 * 投票主题表
 * $Author$
 * $Id$
 */

class voa_s_oa_vote extends service {

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

	/** 获取所有投票列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取投票主题 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_vote::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_vote::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取投票主题 */
	public function fetch_by_uids($uids, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote::fetch_by_uids($uids, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取未结束的 */
	public function fetch_unclosed_by_uid($uids, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote::fetch_unclosed_by_uid($uids, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取已结束的 */
	public function fetch_fin_by_uid_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote::fetch_fin_by_uid_updated($uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 统计进行中的投票 */
	public function count_running() {
		try {
			return voa_d_oa_vote::count_running($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 统计已结束的投票 */
	public function count_fin() {
		try {
			return voa_d_oa_vote::count_fin($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增投票信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_vote::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新投票人次 */
	public function update_voters($v_id, $gule = '+', $num = 1) {
		try {
			return voa_d_oa_vote::update_voters($v_id, $gule, $num, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据ID删除投票信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_vote::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid删除投票信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_vote::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出符合条件的投票
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return Ambigous <multitype:, void, boolean, multitype:unknown multitype: >
	 */
	public function fetch_all_by_conditions($conditions = array(), $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote::fetch_all_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算符合条件的投票总数
	 * @param array $conditions
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function count_all_by_conditions($conditions = array()) {
		try {
			return voa_d_oa_vote::count_all_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
