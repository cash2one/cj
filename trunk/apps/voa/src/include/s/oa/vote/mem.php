<?php
/**
 * 参与投票用户记录表
 * $Author$
 * $Id$
 */

class voa_s_oa_vote_mem extends service {

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

	/** 获取所有参与投票用户列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote_mem::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取参与投票用户 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_vote_mem::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_vote_mem::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 通过 v_id 读取参与投票用户列表 */
	public function fetch_by_v_id($ids, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote_mem::fetch_by_v_id($ids, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 v_id 和 uid 读取投票记录 */
	public function fetch_by_v_id_uid($v_id, $uid) {
		try {
			return voa_d_oa_vote_mem::fetch_by_v_id_uid($v_id, $uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_vote_mem::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_mem::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据投票 id 和 uid 删除参与投票用户
	 * @param int $v_id 投票id
	 * @param int|array $uids 用户 uid
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_v_id_uids($v_id, $uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_mem::delete_by_v_id_uids($v_id, $uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 v_id 删除参与投票用户
	 * @param int|array $ids 主题id或数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_v_id($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_mem::delete_by_v_id($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除参与投票用户
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_mem::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算参与指定投票的投票总数
	 * @param number $v_id
	 * @throws service_exception
	 * @return Ambigous <void, boolean>
	 */
	public function count_by_v_id($v_id) {
		try {
			return voa_d_oa_vote_mem::count_by_v_id($v_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定投票选项的记录
	 * @param int|array $vo_id
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_vo_id($vo_id, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_mem::delete_by_vo_id($vo_id, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
