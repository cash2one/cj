<?php
/**
 * 允许进行投票的用户表
 * $Author$
 * $Id$
 */

class voa_s_oa_vote_permit_user extends service {

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

	/** 获取所有列表(基本没啥意义) */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote_permit_user::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取允许投票的用户信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_vote_permit_user::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_vote_permit_user::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 v_id 读取允许投票的用户 */
	public function fetch_by_v_id($v_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote_permit_user::fetch_by_v_id($v_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 v_id m_uid 查询权限记录  */
	public function fetch_by_v_id_uid($v_id, $m_uid) {
		try {
			return voa_d_oa_vote_permit_user::fetch_by_v_id_uid($v_id, $m_uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增允许查看日志/记录信息的用户
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的用户信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_vote_permit_user::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_permit_user::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据UID删除允许投票的用户
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_permit_user::delete_by_uids($uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除允许投票的用户
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_permit_user::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据v_id删除允许投票的用户
	 *
	 * @param int|array $tids 主题 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_v_id($tids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_permit_user::delete_by_v_id($tids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function delete_by_v_id_uid($v_id, $uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_permit_user::delete_by_v_id_uid($v_id, $uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
