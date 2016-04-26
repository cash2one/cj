<?php
/**
 * department.php
 * 部门表操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_department extends service {

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
			return voa_d_oa_common_department::fetch($value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
    /**
     * 根据用户id获取最大的部门查看权限
     * @param uid $uid
     * @return array
     */
    public function fetch_purview($uid) {
        try {
            return voa_d_oa_common_department::fetch_purview($uid, $this->__shard_key);
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
			return voa_d_oa_common_department::update($data, $value, $this->__shard_key);
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
			return voa_d_oa_common_department::delete($value, $this->__shard_key);
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
			return voa_d_oa_common_department::fetch_all_field($this->__shard_key);
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
			return voa_d_oa_common_department::fetch_all($start, $limit, $this->__shard_key);
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
			return voa_d_oa_common_department::count_all($this->__shard_key);
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
			return voa_d_oa_common_department::insert($data, $return_insert_id, $replace, $this->__shard_key);
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
			return voa_d_oa_common_department::update_by_conditions($data, $conditions, $this->__shard_key);
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
			return voa_d_oa_common_department::delete_by_conditions($conditions, $this->__shard_key);
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
            return voa_d_oa_common_department::count_by_conditions($conditions, $this->__shard_key);
        } catch ( Exception $e ) {
            logger::error($e);
            throw new service_exception($e->getMessage(), $e->getCode());
        }
    }

	/*********************************************************/

	/**
	 * (S) 统计同名部门的个数，除cd_id外
	 * @param string $name
	 * @param number $cd_id
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_name_notid($name, $cd_id){
		try {
			return voa_d_oa_common_department::count_by_name_notid($name, $cd_id, $this->__shard_key);
		} catch ( Exception $e ) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】读取所有指定主键值的数据
	 * @param array $value
	 * @param string $orderby
	 * @param string $sort
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_key($value, $orderby = 'cd_displayorder', $sort = 'ASC', $start = 0, $limit = 0){
		try {
			return voa_d_oa_common_department::fetch_all_by_key($value, $orderby, $sort, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 指定部门 cd_id 增加单位 unit_value 成员计数
	 * <p style="color:red">不推荐直接使用该方法，请调用voa_uda_frontend_department_update->update_usernum()方法</p>
	 * @param number $cd_id
	 * @param number $unit_value 增加的数值，默认为：1
	 * @throws service_exception
	 * @return boolean
	 */
	public function increase_usernum_by_cd_id($cd_id, $unit_value = 1) {
		try {
			return voa_d_oa_common_department::increase_usernum_by_cd_id($cd_id, $unit_value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 指定部门 cd_id 减少单位 unit_value 成员计数
	 * <p style="color:red">不推荐直接使用该方法，请调用voa_uda_frontend_department_update->update_usernum()方法</p>
	 * @param number $cd_id
	 * @param number $unit_value 减少的数值，默认为：1
	 * @throws service_exception
	 * @return boolean
	 */
	public function decrease_usernum_by_cd_id($cd_id, $unit_value = 1) {
		try {
			return voa_d_oa_common_department::decrease_usernum_by_cd_id($cd_id, $unit_value, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据部门名称找到部门信息
	 * @param string $cd_name
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_cd_name($cd_name) {
		try {
			return voa_d_oa_common_department::fetch_by_cd_name($cd_name, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据企业微信的部门id 获取本地的部门信息
	 * @param string $cd_qywxid
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_qywxid($cd_qywxid) {
		try {
			return voa_d_oa_common_department::fetch_by_qywxid($cd_qywxid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据部门名称和上级id来读取部门信息
	 * @param string $cd_name 部门名称
	 * @param int $cd_upid 该部门的上级id
	 * @throws service_exception
	 */
	public function fetch_by_cd_name_upid($cd_name, $cd_upid) {
		try {
			return voa_d_oa_common_department::fetch_by_cd_name_upid($cd_name, $cd_upid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 读取父级ID为指定数据的部门数据
	 * @param number $cd_upid
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_upid($cd_upid) {
		try {
			return voa_d_oa_common_department::fetch_all_by_upid($cd_upid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_all_by_cd_ids($cd_ids, $shard_key = array()) {

		try {
			return voa_d_oa_common_department::fetch_all_by_cd_ids((array)$cd_ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
