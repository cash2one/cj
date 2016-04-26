<?php
/**
 * 自定义字段表
 * $Author$
 * $Id$
 */

class voa_s_oa_askfor_customcols extends service {

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


	/**
	 * 根据审批流程 id 获取自定义字段
	 * @param int|array $aft_id 审批 id 或 id 数组
	 * @param int $start
	 * @param int $limit
	 */
	public function fetch_by_aft_id($aft_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askfor_customcols::fetch_by_aft_id($aft_id, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_askfor_customcols::insert_multi($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}


	/**
	 * 根据流程ID删除信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_aft_id($aft_id, $unbuffered = false) {
		try {
			return voa_d_oa_askfor_customcols::delete_by_aft_id($aft_id, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据流程ID删除信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_aft_ids($aft_ids, $unbuffered = false) {
		try {
			return voa_d_oa_askfor_customcols::delete_by_aft_id($aft_ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
