<?php
/**
 * 可查看报告人员信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_dailyreport_mem extends service {

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
			return voa_d_oa_dailyreport_mem::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取数据 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_dailyreport_mem::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_dailyreport_mem::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 dr_id 读取数据
	 * @param int $dr_id 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_dr_id($dr_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_dailyreport_mem::fetch_by_dr_id($dr_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid查询其有权限查看的报告列表
	 * @param int $uid 用户uid
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_dailyreport_mem::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_dailyreport_mem::fetch_by_search($uid, $conditions, $start, $limit, $this->__shard_key);
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
	public function fetch_by_searchusername($uid, $conditions) {
		try {
			return voa_d_oa_dailyreport_mem::fetch_by_searchusername($uid, $conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

    /**
     * 根据日报id，查询日报阅读状态
     * @pargm array $ids
     * @return  array
     * Create By liyongjian
     */

    public function  fetch_by_read($ids,$uid) {
        try {
            return voa_d_oa_dailyreport_mem::fetch_by_read($ids,$uid,$this->__shard_key);
        } catch (Exception $e) {
            logger::error($e);
            throw new service_exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 统计日报未读数
     * @pargm array $ids
     * @return  array
     * Create By liyongjian
     */
    public function  count_by_read($uid) {
        try {
            return voa_d_oa_dailyreport_mem::count_by_read($uid,$this->__shard_key);
        } catch (Exception $e) {
            logger::error($e);
            throw new service_exception($e->getMessage(), $e->getCode());
        }
    }

    /**
	 * 根据条件搜索记录总数
	 * @param int $uid
	 * @param array $conditions
	 */
	public function count_by_search($uid, $conditions) {
		try {
			return voa_d_oa_dailyreport_mem::count_by_search($uid, $conditions,$this->__shard_key);
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
			return voa_d_oa_dailyreport_mem::count_by_conditions($conditions, $this->__shard_key);
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
			return voa_d_oa_dailyreport_mem::fetch_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
	public function fetch_by_acc_conditions($conditions, $start = 0, $limit = 0) {
	    try {
	        return voa_d_oa_dailyreport_mem::fetch_by_acc_conditions($conditions, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_dailyreport_mem::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_dailyreport_mem::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
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
			return voa_d_oa_dailyreport_mem::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定的日报所有参与人员
	 * @param number|array $dr_ids
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_dr_ids($dr_ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_dailyreport_mem::delete_by_dr_ids($dr_ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}

