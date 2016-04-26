<?php
/**
 * 插件表
 * $Author$
 * $Id$
 */

class voa_s_oa_common_plugin extends service {

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

	/** 获取所有插件列表 */
	public function fetch_all($start = 0, $limit = 0, $force = FALSE) {
		try {
			return voa_d_oa_common_plugin::fetch_all($start, $limit, $force, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 根据插件 id 读取插件信息 */
	public function fetch_by_ids($ids) {
		$ids = rintval($ids);
		try {
			return voa_d_oa_common_plugin::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据唯一标识读取插件记录
	 * @param string $identifier 唯一标识字串
	 */
	public function fetch_by_identifier($identifier) {
		try {
			return voa_d_oa_common_plugin::fetch_by_identifier($identifier, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 验证唯一标识是否存在
	 * @param string|array 唯一标识
	 */
	public function identifier_exists($identifier){
		try {
			return voa_d_oa_common_plugin::fetch_all($identifier, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 新增插件
	 *
	 * @param array $data 插件数据数组, 下标为字段名, 值为对应的插件信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_common_plugin::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_common_plugin::update($data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据UID删除插件
	 *
	 * @param int|array $ids 插件UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_common_plugin::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 找到所有 启用/未启用 的插件应用
	 * @param number $available
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_all_by_available($available = 1) {
		try {
			return voa_d_oa_common_plugin::fetch_all_by_available($available, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 读取指定id的插件信息
	 * @param number $cp_pluginid
	 * @param boolean $force 是否强制读取已删除了的数据
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_cp_pluginid($cp_pluginid, $force = FALSE) {
		try {
			return voa_d_oa_common_plugin::fetch_by_cp_pluginid($cp_pluginid, $force, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 读取指定cma_id的插件信息
	 * @param number $cma_id
	 * @param string $force 是否强制读取已删除了的数据
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_cma_id($cma_id, $force = FALSE) {
		try {
			return voa_d_oa_common_plugin::fetch_by_cma_id($cma_id, $force, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 找到指定的模块分组的第一个插件信息
	 * @param number $cmg_id
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_first_by_cmg_id($cmg_id) {
		try {
			return voa_d_oa_common_plugin::fetch_first_by_cmg_id($cmg_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 找到指定应用型代理id对应的插件信息
	 * @param number $cp_agentid
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_cp_agentid($cp_agentid = 0) {
		try {
			return voa_d_oa_common_plugin::fetch_by_cp_agentid($cp_agentid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 清理非应用自身ID的agentid
	 * @param number $pluginid
	 * @param number $agentid
	 * @throws service_exception
	 */
	public function clear_agentid($pluginid, $agentid) {
		try {
			return voa_d_oa_common_plugin::clear_agentid($pluginid, $agentid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算已安装应用个数
	 * @throws service_exception
	 */
	public function installed_count() {
		try {
			return voa_d_oa_common_plugin::installed_count($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
