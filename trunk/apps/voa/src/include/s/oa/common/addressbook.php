<?php
/**
 * voa_s_oa_common_addressbook
 * 通讯录表操作
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_addressbook extends service {

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
			return voa_d_oa_common_addressbook::fetch($value, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::update($data, $value, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::delete($value, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::fetch_all_field($this->__shard_key);
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
			return voa_d_oa_common_addressbook::fetch_all($start, $limit, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::count_all($this->__shard_key);
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
			return voa_d_oa_common_addressbook::insert($data, $return_insert_id, $replace, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::update_by_conditions($data, $conditions, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::delete_by_conditions($conditions, $this->__shard_key);
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
			return voa_d_oa_common_addressbook::fetch_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*********************************************************/

	/**
	 * (S) 判断手机号的个数，除cab_id外
	 * @param ring $mobilephone
	 * @param number $cab_id
	 */
	public function count_by_mobilephone_notid($mobilephone, $cab_id = 0) {
		try {
			return (int) voa_d_oa_common_addressbook::count_by_mobilephone_notid($mobilephone, $cab_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 计算符合条件的数据数
	 * @param array $conditions
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conditions($conditions, $haveMember = NULL) {
		try {
			return (int) voa_d_oa_common_addressbook::count_by_conditions($conditions, $haveMember, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取指定条件的数据列表
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @param mixed $haveMember 是否查询已绑定member表的用户。null不限，true只查找已绑定的，false只查找未绑定的
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $haveMember = NULL) {
		try {
			return (array) voa_d_oa_common_addressbook::fetch_all_by_conditions($conditions, $start, $limit, $haveMember, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据手机号码读取
	 * @param string $mobile 手机号码
	 * @throws service_exception
	 */
	public function fetch_by_mobilephone($mobile) {
		try {
			return (array)voa_d_oa_common_addressbook::fetch_by_mobilephone($mobile, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 openid 获取用户信息
	 * @param string $openid
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_openid($openid) {
		try {
			return (array)voa_d_oa_common_addressbook::fetch_by_openid($openid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计某个字段的数据被登记次数，除了cab_id为$cab_id的之外
	 * @param string $field
	 * @param string $value
	 * @param number $cab_id
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_field_notid($field, $value, $cab_id = 0) {
		try {
			return (int) voa_d_oa_common_addressbook::count_by_field_notid($field, $value, $cab_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据email读取
	 * @param string $email
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_email($email) {
		try {
			return (array)voa_d_oa_common_addressbook::fetch_by_email($email, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
