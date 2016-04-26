<?php

/**
 * voa_s_oa_member_department
 * 用户与部门关联表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_member_department extends service {

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
	 * 【S】根据主键值读取单条数据
	 * @author Deepseath
	 * @param int $value 主键值
	 * @throws service_exception
	 */
	public function fetch($value) {
		try {
			return voa_d_oa_member_department::fetch($value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】通过主键更新
	 * @author Deepseath
	 * @param array $data 待更新的数据数组
	 * @param array|string $value 主键值
	 * @throws service_exception
	 */
	public function update($data, $value) {
		try {
			return voa_d_oa_member_department::update($data, $value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】通过主键删除记录
	 * @author Deepseath
	 * @param array|string $value 主键值
	 * @throws service_exception
	 * @return void
	 */
	public function delete($value) {
		try {
			return voa_d_oa_member_department::delete($value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】获取数据表默认值
	 * @author Deepseath
	 * @throws service_exception
	 */
	public function fetch_all_field() {
		try {
			return voa_d_oa_member_department::fetch_all_field($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】读取所有
	 * @author Deepseath
	 * @param int $start
	 * @param int $limit
	 * @throws service_exception
	 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_member_department::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】统计所有未删除数据的总数
	 * @author Deepseath
	 * @throws service_exception
	 * @return number
	 */
	public function count_all() {
		try {
			return voa_d_oa_member_department::count_all($this->__shard_key);
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
			return voa_d_oa_member_department::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】获取指定条件的数据列表
	 * @param array $conditions
	 * @param array $orderby
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_conditions($conditions, $orderby = array(), $start = 0, $limit = 0) {
		try {
			return (array)voa_d_oa_member_department::fetch_all_by_conditions($conditions, $orderby, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】数据入库
	 * @author Deepseath
	 * @param array $data 入库数据
	 * @param boolean $return_insert_id 是否返回自增id
	 * @param boolean $replace 是否使用 replace into
	 * @throws service_exception
	 */
	public function insert($data, $return_insert_id = FALSE, $replace = FALSE) {
		try {
			return voa_d_oa_member_department::insert($data, $return_insert_id, $replace, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】通过条件更新
	 * @author Deepseath
	 * @param array $data 更新的数据
	 * @param string|array $conditions 更新条件
	 * @throws service_exception
	 * @return Ambigous <void, boolean, unknown>
	 */
	public function update_by_conditions($data, $conditions) {
		try {
			return voa_d_oa_member_department::update_by_conditions($data, $conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据条件删除记录
	 * @author Deepseath
	 * @param array|string $conditions
	 * @throws service_exception
	 * @return void
	 */
	public function delete_by_conditions($conditions) {
		try {
			return voa_d_oa_member_department::delete_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据条件找到一条记录
	 * @param array $conditions
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_conditions($conditions) {
		try {
			return voa_d_oa_member_department::fetch_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*********************************************************/

	/**
	 * 删除指定用户的部门关系数据
	 * @param number $m_uid
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_m_uid($m_uid) {
		try {
			return voa_d_oa_member_department::delete_by_m_uid($m_uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 返回指定用户所关联的部门ID数组
	 * @param number $m_uid
	 * @throws service_exception
	 * @return Ambigous <multitype:, multitype:unknown >
	 */
	public function fetch_all_by_uid($m_uid = 0) {
		try {
			return voa_d_oa_member_department::fetch_all_by_uid($m_uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 找到指定用户所关联的部门ID
	 * @param number $m_uid
	 * @param array $shard_key
	 * @return array
	 */
	public function fetch_all_field_by_uid($m_uid = 0) {
		try {
			return voa_d_oa_member_department::fetch_all_field_by_uid($m_uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * <strong style="color:blue">【D】获取用户id多条SQL UNION</strong>
	 * @param array $conditions 查询条件，
	 * @see self::parse_conditions
	 * @return array
	 */
	public function fetch_muid_multi_sql_union($conditions) {
		try {
			return voa_d_oa_member_department::fetch_muid_multi_sql_union($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过部门id统计部门人数
	 * @param array $cdids 部门id数组
	 * @param array $shard_key
	 * @return array
	 */
	public function list_count_by_cdid($cdids = array()) {

		try {
			return voa_d_oa_member_department::list_count_by_cdid($cdids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function count_by_cdid($cdids) {

		try {
			return voa_d_oa_member_department::count_by_cdid((array)$cdids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
