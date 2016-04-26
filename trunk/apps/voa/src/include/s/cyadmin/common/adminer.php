<?php
/**
 * voa_s_cyadmin_common_adminer
 * 管理员数据表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_cyadmin_common_adminer extends service {

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
			return voa_d_cyadmin_common_adminer::fetch($value, $this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::update($data, $value, $this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::delete($value, $this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::fetch_all_field($this->__shard_key);
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
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_cyadmin_common_adminer::fetch_all($start, $limit, $this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::count_all($this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::insert($data, $return_insert_id, $replace, $this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::update_by_conditions($data, $conditions, $this->__shard_key);
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
			return voa_d_cyadmin_common_adminer::delete_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*********************************************************/

	/**
	 * (S) 根据管理员用户名读取
	 * @param string $username
	 * @throws service_exception
	 */
	public function fetch_by_username($username) {
		try {
			return voa_d_cyadmin_common_adminer::fetch_by_username($username, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * (S) 统计同名管理员的个数，除ca_id外
	 * @param string $username
	 * @param number $ca_id
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_username_notid($username, $ca_id){
		try {
			return voa_d_cyadmin_common_adminer::count_by_username_notid($username, $ca_id, $this->__shard_key);
		} catch ( Exception $e ) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
