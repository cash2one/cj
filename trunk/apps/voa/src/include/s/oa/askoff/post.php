<?php
/**
 * 请假回复信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_askoff_post extends service {

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
			return voa_d_oa_askoff_post::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据主键值读取数据 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_askoff_post::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_askoff_post::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过 ao_id 获取对应的详情信息
	 * @param int $ao_id 请假
	 * @throws service_exception
	 * @return Ambigous <void, boolean>
	 */
	public function fetch_first_by_ao_id($ao_id) {
		try {
			return voa_d_oa_askoff_post::fetch_first_by_ao_id($ao_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 ao_id 读取回复的信息
	 * @param int $ao_id 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_ao_id($ao_id, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askoff_post::fetch_by_ao_id($ao_id, $start, $limit, $this->__shard_key);
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
			return voa_d_oa_askoff_post::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定条件的数据
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public function fetch_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_askoff_post::fetch_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_askoff_post::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_askoff_post::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据ID删除信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_askoff_post::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除指定条件的数据
	 * @param array $conditions
	 * @param boolean $unbuffered
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_conditions($conditions, $unbuffered = false) {
		try {
			return voa_d_oa_askoff_post::delete_by_conditions($conditions, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}

