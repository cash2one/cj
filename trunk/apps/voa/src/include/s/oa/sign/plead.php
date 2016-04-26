<?php
/**
 * 签到申诉表
 * $Author$
 * $Id$
 */

class voa_s_oa_sign_plead extends service {

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

	/** 获取所有签到申诉列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_sign_plead::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取签到申诉信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_sign_plead::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_sign_plead::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据用户 uid 读取签到申诉信息 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_sign_plead::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增签到信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_sign_plead::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_sign_plead::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据UID删除签到信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_sign_plead::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除签到信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_sign_plead::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出符合条件的申诉记录
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return Ambigous <multitype:, void, boolean, multitype:unknown multitype: >
	 */
	public function fetch_all_by_conditions($conditions = array(), $start = 0, $limit = 0) {
		try {
			return voa_d_oa_sign_plead::fetch_all_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算符合条件的申诉总数
	 * @param array $conditions
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function count_all_by_conditions($conditions = array()) {
		try {
			return voa_d_oa_sign_plead::count_all_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
