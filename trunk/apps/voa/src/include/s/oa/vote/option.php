<?php
/**
 * 投票选项表
 * $Author$
 * $Id$
 */

class voa_s_oa_vote_option extends service {

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

	/** 获取所有投票选项列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote_option::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取投票选项 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_vote_option::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_vote_option::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 通过 v_id 读取投票选项列表 */
	public function fetch_by_v_id($ids, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_vote_option::fetch_by_v_id($ids, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 通过 v_id 和 vo_id 读取投票选项 */
	public function fetch_by_v_id_vo_ids($v_id, $vo_ids) {
		try {
			return voa_d_oa_vote_option::fetch_by_v_id_vo_ids($v_id, $vo_ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增投票选项信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_vote_option::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_option::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 vo_id 进行 +1 操作 */
	public function choices($ids) {
		try {
			return voa_d_oa_vote_option::choices($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 v_id 删除投票选项
	 * @param int|array $ids 主题id或数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_v_id($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_option::delete_by_v_id($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除投票选项
	 * @param int $v_id 投票id
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_v_id_vo_ids($v_id, $ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_option::delete_by_v_id_vo_ids($v_id, $ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定vo_id的投票选项
	 * @param int|array $vo_id
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @throws service_exception
	 */
	public function delete_by_id($vo_id, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_vote_option::delete_by_id($vo_id, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
