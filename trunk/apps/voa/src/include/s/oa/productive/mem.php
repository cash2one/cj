<?php
/**
 * 可查看活动/产品信息人员信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_productive_mem extends service {

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
			return voa_d_oa_productive_mem::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取数据 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_productive_mem::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_productive_mem::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 pt_id 读取数据
	 * @param int $pt_id 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_pt_id($pt_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_productive_mem::fetch_by_pt_id($pt_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 pt_id, uid 读取数据
	 * @param int $pt_id 会议纪要id
	 * @param int $uid 用户uid
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_pt_id_uid($pt_id, $uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_productive_mem::fetch_by_pt_id_uid($pt_id, $uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid查询其有权限查看的活动/产品信息列表
	 * @param int $uid 用户uid
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_productive_mem::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件搜索
	 * @param int $uid
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @param array $shard_key
	 */
	public function fetch_by_search($uid, $conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_productive_mem::fetch_by_search($uid, $conditions, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_productive_mem::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的投票
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public function fetch_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_productive_mem::fetch_by_conditions($conditions, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_productive_mem::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_productive_mem::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
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
			return voa_d_oa_productive_mem::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定的日报所有参与人员
	 * @param number|array $pt_ids
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_pt_ids($pt_ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_productive_mem::delete_by_pt_ids($pt_ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 用户信息入库
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public function insert_multi($data) {
		try {
			return voa_d_oa_productive_mem::insert_multi($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过 uid 获取已接收的活动/产品信息
	 * @param int $uid
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public function list_recv_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_productive_mem::list_recv_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}

