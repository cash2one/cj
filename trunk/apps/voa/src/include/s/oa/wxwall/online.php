<?php
/**
 * 微信墙在线用户操作类
 * $Author$
 * $Id$
 */

class voa_s_oa_wxwall_online extends service {

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

	/** 获取所有在线列表 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_wxwall_online::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据 id 读取在线信息 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_wxwall_online::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_wxwall_online::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}


	/**
	 * 添加在线信息
	 * @param array $data 字段数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insernc_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_wxwall_online::insert($data, $return_insernc_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_online::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id删除在线信息
	 * @param int|array $ids wwo_id
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_online::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据用户的openid和微信墙ww_id找到在线信息
	 * @param unknown $m_openid
	 * @param unknown $wwo_id
	 */
	public function fetch_by_openid_id($m_openid, $ww_id = false) {
		try {
			return voa_d_oa_wxwall_online::fetch_by_openid_id($m_openid, $ww_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 找到所有在线的微信墙id
	 */
	public function fetch_all_by_online() {
		try {
			$list = array();
			$data = voa_d_oa_wxwall_online::fetch_all_by_online($this->__shard_key);
			foreach ($data as $id => $v) {
				$list[$id] = $id;
			}

			return $list;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定微信墙ww_ids的在线信息
	 * @param unknown $ww_ids
	 */
	public function delete_by_ww_id($ww_ids, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_wxwall_online::delete_by_ww_id($ww_ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定的openid的在线信息
	 */
	public function delete_by_openid_wwid($m_openid, $ww_id) {
		try {
			return voa_d_oa_wxwall_online::delete_by_openid_wwid($m_openid, $ww_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
