<?php
/**
 * 模块分组表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_module_group extends service {

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
			return voa_d_oa_common_module_group::fetch($value, $this->__shard_key);
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
			return voa_d_oa_common_module_group::update($data, $value, $this->__shard_key);
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
			return voa_d_oa_common_module_group::delete($value, $this->__shard_key);
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
	public function fetch_all_field(){
		try {
			return voa_d_oa_common_module_group::fetch_all_field($this->__shard_key);
		} catch ( Exception $e ) {
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
	public function fetch_all($start = 0, $limit = 0, $force = FALSE) {
		try {
			return voa_d_oa_common_module_group::fetch_all($start, $limit, $force, $this->__shard_key);
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
	public function count_all(){
		try {
			return voa_d_oa_common_module_group::count_all($this->__shard_key);
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
			return voa_d_oa_common_module_group::insert($data, $return_insert_id, $replace, $this->__shard_key);
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
			return voa_d_oa_common_module_group::update_by_conditions($data, $conditions, $this->__shard_key);
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
			return voa_d_oa_common_module_group::delete_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*********************************************************/

	/**
	 * 找到指定模块分组名的信息
	 * @param string $ccm_dir
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_cmg_dir($ccm_dir) {
		try {
			return voa_d_oa_common_module_group::fetch_by_cmg_dir($ccm_dir, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
