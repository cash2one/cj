<?php
/**
 * 审批进度表
 * $Author$
 * $Id$
 */

class voa_s_oa_askfor_proc extends service {

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

	/** 获取所有审批进度列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor_proc::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取审批列表 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 af_id 读取进度列表 */
	public function fetch_by_af_id($af_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_af_id($af_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_af_ids($af_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_af_ids($af_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据审批 id 和 uid 读取进度信息
	 * @param unknown_type $af_id
	 * @param unknown_type $uid
	 */
	public function fetch_by_af_id_uid($af_id, $uid) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_af_id_uid($af_id, $uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_af_id_uids($af_id, $uids) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_af_id_uids($af_id, $uids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 统计等待我审批的事项 */
	public function count_by_uid($uid) {
		try {
			return voa_d_oa_askfor_proc::count_by_uid($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增审批进度信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_askfor_proc::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert_multi($data) {
		try {
			return voa_d_oa_askfor_proc::insert_multi($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_askfor_proc::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
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
			return voa_d_oa_askfor_proc::delete_by_ids($ids, $unbuffered, $this->__shard_key);
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
			return voa_d_oa_askfor_proc::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据审批ID删除审批信息
	 *
	 * @param int|array $af_ids 用户 $af_id 或 $af_id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_af_ids($af_ids, $unbuffered = false) {
		try {
			return voa_d_oa_askfor_proc::delete_by_af_ids($af_ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据条件获取数据 */
	public function fetch_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor_proc::fetch_by_conditions($conditions, $start, $limit);
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
	public function count_by_conditions($uid, $status = 1) {
		$conditions = array('m_uid' => $uid, 'afp_status' => $status);
		$where = "m_uid = $uid AND ";
		if(is_array($status)) {
			$where .= "afp_status IN(".implode(',', $status).")";
		}else{
			$where .= "afp_status = $status";
		}
		try {
			return voa_d_oa_askfor_proc::count_by_conditions($where, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 我处理的列表
	 * @param int $uid
	 * @param mixed	$status	状态(int或数组)
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public function _deal($uid, $status = 1, $start = 0, $limit = 0) {
		try {
			$conditions = array('m_uid' => $uid, 'afp_status' => $status);
			$where = "m_uid = $uid AND ";
			if(is_array($status)) {
				$where .= "afp_status IN(".implode(',', $status).")";
			}else{
				$where .= "afp_status = $status";
			}

			$data = voa_d_oa_askfor_proc::fetch_by_conditions($where, $start, $limit, $this->__shard_key);
			if(!$data) return array();
			//与主表混合
			foreach ($data as $r) {
				$ids[] = $r['af_id'];
			}
			$data = voa_d_oa_askfor::fetch_by_ids($ids);
			return $data;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
