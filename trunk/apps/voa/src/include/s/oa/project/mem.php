<?php
/**
 * 项目成员表
 * $Author$
 * $Id$
 */

class voa_s_oa_project_mem extends service {

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

	/** 获取所有用户列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_project_mem::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取参会用户信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_project_mem::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_project_mem::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据会议 p_id 读取用户信息 */
	public function fetch_by_p_id($p_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_project_mem::fetch_by_p_id($p_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据用户 uid 读取参会用户信息 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_project_mem::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据会议 id 和 uid 读取参会人信息 */
	public function fetch_by_p_id_uid($p_id, $uid) {
		try {
			return voa_d_oa_project_mem::fetch_by_p_id_uid($p_id, $uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 统计我参加的并且已关闭的项目数 */
	public function count_closed($uid) {
		try {
			return voa_d_oa_project_mem::count_closed($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 统计我参加的并且已完成的项目数 */
	public function count_done($uid) {
		try {
			return voa_d_oa_project_mem::count_done($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计我参加的项目数
	 * @param int $uid
	 */
	public function count_mine($uid) {
		try {
			return voa_d_oa_project_mem::count_mine($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 读取未完成的项目
	 * @param mixed $uids
	 */
	public function count_running_by_uid($uid) {
		try {
			return voa_d_oa_project_mem::count_running_by_uid($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增参会用户信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的用户信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_project_mem::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_project_mem::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 UID 删除项目用户信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_project_mem::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 id 删除项目用户信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_project_mem::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除参加项目的人员
	 * @param int $p_id 会议id
	 * @param array $uids 用户uid数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_p_id_uid($p_id, $uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_project_mem::delete_by_p_id_uid($p_id, $uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定项目的所有参与人员
	 * @param mixed $p_ids
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete_by_p_ids($p_ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_project_mem::delete_by_p_ids($p_ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
