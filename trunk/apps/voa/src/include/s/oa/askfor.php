<?php
/**
 * 审批表
 * $Author$
 * $Id$
 */

class voa_s_oa_askfor extends service {

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

	/** 计算所有审批总数 */
	public function count_all() {
		try {
			return voa_d_oa_askfor::count_all($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取所有审批列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 返回符合条件的审批列表数据
	 * @param array $condition
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function fetch_all_by_condition($condition, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_all_by_condition($condition, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取审批列表 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_askfor::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_askfor::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_mine($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_mine($uid, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_askfor::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取已经完成审批的列表 */
	public function fetch_done_by_uids_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_done_by_uids_updated($uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取等待我批复的申请列表 */
	public function fetch_deal_by_uids_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_deal_by_uids_updated($uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取我申请的审批列表 */
	public function fetch_my_by_uids_updated($uids, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_my_by_uids_updated($uids, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取我发起的审批中的审批列表 */
	public function fetch_my_askforing($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_my_askforing($uid,  $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取我发起的已审批的审批列表 */
	public function fetch_my_askfored($uid,  $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_my_askfored($uid,  $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取我发起的被驳回和撤销的审批列表 */
	public function fetch_my_refuse_cancel($uid,  $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_my_refuse_cancel($uid,  $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取等待我批复的审批列表 */
	public function fetch_my_approving($uid,  $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_my_approving($uid,  $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取我已批复的审批列表 */
	public function fetch_my_approved($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor::fetch_my_approved($uid,  $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件计算总数
	 * @param int $uid
	 * @param mixed	$status	状态(int或数组)
	 * @return number
	 */
	public function count_my_by_conditions($uid, $status = 1) {
		$conditions = array('m_uid' => $uid, 'af_status' => $status);
		$where = "m_uid = $uid AND ";
		if(is_array($status)) {
			$where .= "af_status IN(".implode(',', $status).")";
		}else{
			$where .= "af_status = $status";
		}
		try {
			return voa_d_oa_askfor::count_my_by_conditions($where, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增审批信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_askfor::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_askfor::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据ID删除审批信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_askfor::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid删除审批信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_askfor::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计指定条件的审批数量
	 * @param array $condition
	 * @throws service_exception
	 * @return number
	 */
	public function count_all_by_condition($condition) {
		try {
			return (int) voa_d_oa_askfor::count_all_by_condition($condition, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计指定用户发起的审批数
	 * @param int $uid
	 */
	public function count_mine($uid) {
		try {
			return (int) voa_d_oa_askfor::count_mine($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
