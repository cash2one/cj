<?php
/**
 * 名片中公司信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_namecard_company extends service {

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

	/** 获取所有公司列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_namecard_company::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据公司名称读取 */
	public function fetch_by_name($name) {
		try {
			return voa_d_oa_namecard_company::fetch_by_name($name, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取公司信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_namecard_company::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_namecard_company::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据用户 uid 读取公司信息
	 * @param int $uid uid
	 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_namecard_company::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增名片公司信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_namecard_company::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_namecard_company::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新名片公司数值 */
	public function update_num($ncc_ids, $gule = '+', $num = 1) {
		try {
			return voa_d_oa_namecard_company::update_num($ncc_ids, $gule, $num, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据UID删除名片公司信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_namecard_company::delete_by_uids($uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除名片公司信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_namecard_company::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算指定条件的公司数量
	 * @param array $conditions
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conditions($conditions = array()) {
		try {
			return (int) voa_d_oa_namecard_company::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的公司列表
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return (array) voa_d_oa_namecard_company::fetch_all_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
