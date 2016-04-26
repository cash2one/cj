<?php
/**
 * 微信墙表
 * $Author$
 * $Id$
 */

class voa_s_oa_wxwall extends service {

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

	/** 获取所有微信墙列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 值, 读取微信墙列表 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_wxwall::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据微信墙管理员登录名获取微信墙信息
	 * @param string $admin
	 * @return array
	 */
	public function fetch_by_admin($admin){
		try {
			return voa_d_oa_wxwall::fetch_by_admin($admin, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_wxwall::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 uid 读取微信墙列表 */
	public function fetch_by_uids($uids, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall::fetch_by_uids($uids, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取审核中的 */
	public function fetch_mine_apply($uid, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall::fetch_mine_apply($uid, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取运行中的 */
	public function fetch_running($start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall::fetch_running($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据最后更新时间读取 */
	public function fetch_fin_by_updated($updated, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall::fetch_fin_by_updated($updated, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_wxwall::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据ID删除
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_wxwall::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据uid删除
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_uids($uids, $unbuffered = false) {
		try {
			return voa_d_oa_wxwall::delete_by_uids($uids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过二维码场景id获取微信墙信息
	 * @param int $sceneid
	 */
	public function fetch_by_sceneid($sceneid) {
		try {
			return voa_d_oa_wxwall::fetch_by_sceneid($sceneid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 找到所有关闭或者结束了的微信墙ww_id
	 */
	public function fetch_all_close() {
		try {
			return voa_d_oa_wxwall::fetch_all_close($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取运行中的数目 */
	public function count_running() {
		try {
			return voa_d_oa_wxwall::count_running($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取已结束/关闭的数目 */
	public function count_fin() {
		try {
			return voa_d_oa_wxwall::count_fin($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出符合条件的微信墙
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return Ambigous <multitype:, void, boolean, multitype:unknown multitype: >
	 */
	public function fetch_all_by_conditions($conditions = array(), $start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall::fetch_all_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算符合条件的微信墙总数
	 * @param array $conditions
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function count_all_by_conditions($conditions = array()) {
		try {
			return voa_d_oa_wxwall::count_all_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
