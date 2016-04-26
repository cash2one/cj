<?php
/**
 * 名片信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_namecard extends service {

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

	/** 获取所有名片信息列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_namecard::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取名片信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_namecard::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_namecard::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 和 ncf_id 获取名片信息
	 * @param int $uid 用户 uid
	 * @param int|array $ncf_ids 名片群组id
	 * @param int $start
	 * @param int $limit
	 */
	public function fetch_mine_by_ncf_id($uid, $ncf_ids, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_namecard::fetch_mine_by_ncf_id($uid, $ncf_ids, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据用户 uid 读取我的名片信息 */
	public function fetch_mine_by_uid($uid, $sotext = '', $start = 0, $limit = 0) {
		try {
			return voa_d_oa_namecard::fetch_mine_by_uid($uid, $sotext, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 统计名片数
	 * @param int $uid
	 */
	public function count_by_uid($uid) {
		try {
			return voa_d_oa_namecard::count_by_uid($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增名片信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insernc_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_namecard::insert($data, $return_insernc_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_namecard::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据UID删除名片信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_namecard::delete_by_uids($uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除名片信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_namecard::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算指定条件的名片数量
	 * @param array $conditions
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conditions($conditions = array()) {
		try {
			return (int) voa_d_oa_namecard::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的名片列表
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return (array) voa_d_oa_namecard::fetch_all_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取用户的名片夹信息
	 * @param int $uid 用户uid
	 * @param array $conditions 条件
	 * @param number $start 开始
	 * @param number $limit 结束
	 * @param array $shard_key 分表
	 */
	public function list_by_conditions($uid, $conditions, $start = 0, $limit = 0) {
		try {
			return (array)voa_d_oa_namecard::list_by_conditions($uid, $conditions, $start, $limit);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
