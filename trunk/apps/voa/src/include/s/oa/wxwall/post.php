<?php
/**
 * 微信墙回复信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_wxwall_post extends service {

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

	/** 获取所有主题/记录详细信息列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall_post::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取主题/记录详细信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_wxwall_post::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_wxwall_post::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 通过 ww_id 读取微信墙回复信息列表 */
	public function fetch_by_ww_id($ids, $status = array(), $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall_post::fetch_by_ww_id($ids, $status, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过www_id和状态计算微信墙回复信息数
	 * @param array $ids
	 * @param array $status
	 */
	public function count_by_ww_id($ids, $status) {
		try {
			return voa_d_oa_wxwall_post::count_by_ww_id($ids, $status, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据ww_id读取last_wwp_id之后发布的微信墙消息
	 * @param number $ww_id
	 * @param number $last_wwp_id
	 * @param number $start
	 * @param number $limit
	 */
	public function fetch_all_by_ww_id_wwp_id($ww_id, $last_wwp_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall_post::fetch_all_by_ww_id_wwp_id($ww_id, $last_wwp_id, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 ww_id 和更新时间读取记录 */
	public function fetch_by_ww_id_updated($id, $updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall_post::fetch_by_ww_id_updated($id, $updated, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据用户 uid 读取微信墙回复信息 */
	public function fetch_by_uid($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall_post::fetch_by_uid($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计回复数
	 * @param int $ww_id 微信墙id
	 * @param int $uid 用户uid
	 * @param array $status 状态
	 */
	public function count_by_ww_id_uid($ww_id, $uid, $status = array()) {
		try {
			return voa_d_oa_wxwall_post::count_by_ww_id_uid($ww_id, $uid, $status, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增微信墙回复信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_wxwall_post::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_post::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据UID删除微信墙回复信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_post::delete_by_uids($uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据微信墙 id 和 uid 删除微信墙回复信息
	 * @param int $ww_id 微信墙id
	 * @param int|array $uids 用户 uid
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_ww_id_uids($ww_id, $uids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_post::delete_by_ww_id_uids($ww_id, $uids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 ww_id 删除微信墙回复信息
	 * @param int|array $ids 主题id或数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public function delete_by_ww_id($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_post::delete_by_ww_id($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除主题/记录详细信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_post::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
