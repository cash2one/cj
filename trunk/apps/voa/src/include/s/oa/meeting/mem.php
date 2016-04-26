<?php
/**
 * 会议成员表
 * $Author$
 * $Id$
 */

class voa_s_oa_meeting_mem extends service {

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
			return voa_d_oa_meeting_mem::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取参会用户信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_meeting_mem::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_meeting_mem::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据会议 mt_id 读取参会用户信息 */
	public function fetch_by_mt_id($mt_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_meeting_mem::fetch_by_mt_id($mt_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据用户 uid 读取参会用户信息 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_meeting_mem::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据会议 id 和 uid 读取参会人信息 */
	public function fetch_by_mt_id_uid($mt_id, $uid) {
		try {
			return voa_d_oa_meeting_mem::fetch_by_mt_id_uid($mt_id, $uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 统计确认参加的用户数 */
	public function count_by_mt_id($mt_id, $status = array()) {
		try {
			return voa_d_oa_meeting_mem::count_by_mt_id($mt_id, $status, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计待参加的会议数
	 * @param int $uid
	 */
	public function count_by_uid($uid) {
		try {
			return voa_d_oa_meeting_mem::count_by_uid($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 读取我需要参加的会议
	 * @param int $uid 用户uid
	 * @param int $status 会议状态, 0: 所有, 1:未结束, 2:已结束
	 */
	public function count_join_by_uid($uid, $status = 0) {
		try {
			return voa_d_oa_meeting_mem::count_join_by_uid($uid, $status, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 读取我需要参加的会议
	 * @param int $uid 用户uid
	 * @param int $updated 会议信息最后更新时间
	 * @param int $start
	 * @param int $limit
	 * @param int $status 会议状态, 0: 所有, 1:未结束, 2:已结束
	 */
	public function fetch_join_by_uid_updated($uid, $updated, $start = 0, $limit = 0, $status = 0) {
		try {
			return voa_d_oa_meeting_mem::fetch_join_by_uid_updated($uid, $updated, $start, $limit, $status, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @return number
	 */
	public function count_by_conditions($conditions) {
		try {
			return voa_d_oa_meeting_mem::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的数据
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public function fetch_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_meeting_mem::fetch_by_conditions($conditions, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_meeting_mem::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_meeting_mem::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 UID 删除参会用户信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_meeting_mem::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 id 删除参会用户信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_meeting_mem::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除参加会议的人员
	 * @param int $mt_id 会议id
	 * @param array $uids 用户uid数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_mt_id_uid($mt_id, $uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_meeting_mem::delete_by_mt_id_uid($mt_id, $uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定会议的参会人员
	 * @param array $mt_id
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 */
	public function delete_by_mt_id($mt_id, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_meeting_mem::delete_by_mt_id($mt_id, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计某人在某个时间段内参会次数
	 * @param number $m_uid
	 * @param number $start_time
	 * @param number $end_time
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_m_uid($m_uid, $start_time, $end_time) {
		try {
			return voa_d_oa_meeting_mem::count_by_m_uid($m_uid, $start_time, $end_time, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
