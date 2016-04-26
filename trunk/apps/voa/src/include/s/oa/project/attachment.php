<?php
/**
 * voa_s_oa_project_attachment
 * 应用.任务/附件表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_project_attachment extends service {

	/** 分库/分表的信息 */
	private $__shard_key = array();

	/**
	 * __construct
	 * @param  array $shard_key
	 * @return void
	*/
	public function __construct($shard_key = array()) {
		$this->__shard_key = $shard_key;
	}


	/**
	 * 【S】获取表字段默认数据
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_field() {
		try {
			return voa_d_oa_project_attachment::fetch_all_field($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据主键值获取单条数据
	 * @param number $value
	 * @throws service_exception
	 * @return array
	 */
	public function fetch($value) {
		try {
			return voa_d_oa_project_attachment::fetch($value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据主键ID值获取单条数据（不推荐使用此方法，请以fetch来替代）
	 * @param number $id
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_project_attachment::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【D】根据一组主键值来获取多条数据
	 * @param number|array $ids
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_project_attachment::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】读取所有数据
	 * @param int $start
	 * @param int $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_project_attachment::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】列出指定条件的数据
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_conditions($conditions, $start = 0, $limit = 0) {
		try {
			return voa_d_oa_project_attachment::fetch_by_conditions($conditions, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】数据入库
	 * @param array $data 入库数据
	 * @param boolean $return_insert_id 是否返回自增id
	 * @param boolean $replace 是否使用 replace into
	 * @throws service_exception
	 */
	public function insert($data, $return_insert_id = FALSE, $replace = FALSE, $silent = FALSE) {
		try {
			return voa_d_oa_project_attachment::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】通过主键删除记录
	 * @param array|number $value
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete($value, $unbuffered = FALSE, $low_priority = FALSE) {
		try {
			return voa_d_oa_project_attachment::delete($value, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据主键删除（不推荐此方法，请使用delete方法替代）
	 * @param number|array $ids
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_ids($ids, $unbuffered = FALSE, $low_priority = FALSE) {
		try {
			return voa_d_oa_project_attachment::delete_by_ids($ids, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据条件删除
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function delete_by_conditions($conditions, $unbuffered = FALSE, $low_priority = FALSE) {
		try {
			return voa_d_oa_project_attachment::delete_by_conditions($conditions, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】通过主键更新
	 * @param array $data
	 * @param number|array $value
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function update($data, $value, $unbuffered = FALSE, $low_priority = FALSE) {
		try {
			return voa_d_oa_project_attachment::update($data, $value, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】通过条件更新
	 * @param array $data
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 * @return Ambigous <Ambigous, void, boolean>
	 */
	public function update_by_conditions($data, $conditions, $unbuffered = FALSE, $low_priority = FALSE) {
		try {
			return voa_d_oa_project_attachment::update_by_conditions($data, $conditions, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】统计所有未删除数据的总数
	 * @throws service_exception
	 * @return number
	 */
	public function count_all(){
		try {
			return voa_d_oa_project_attachment::count_all($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】根据条件计算所有未删除的记录总数
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conditions($conditions){
		try {
			return voa_d_oa_project_attachment::count_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** ************************ */

	/**
	 * 找到指定任务的所有相关文件附件
	 * @param number $p_id
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_all_by_p_id($p_id) {
		try {
			return voa_d_oa_project_attachment::fetch_all_by_p_id($p_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
