<?php
/**
 * voa_s_oa_member
 * 用户表
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_member extends service {

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
			return voa_d_oa_member::fetch($value, $this->__shard_key);
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
			return voa_d_oa_member::update($data, $value, $this->__shard_key);
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
			return voa_d_oa_member::delete($value, $this->__shard_key);
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
			return voa_d_oa_member::fetch_all_field($this->__shard_key);
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
			return voa_d_oa_member::fetch_all($start, $limit, $this->__shard_key);
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
			return voa_d_oa_member::count_all($this->__shard_key);
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
			return voa_d_oa_member::insert($data, $return_insert_id, $replace, $this->__shard_key);
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
			return voa_d_oa_member::update_by_conditions($data, $conditions, $this->__shard_key);
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
			return voa_d_oa_member::delete_by_conditions($conditions, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 【S】计算符合条件的数据数
	 * @param array $conditions
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conditions($conditions) {
		try {
			return (int) voa_d_oa_member::count_by_conditions($conditions, $this->__shard_key);
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
			return (array) voa_d_oa_member::fetch_all_by_conditions($conditions, $orderby, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*********************************************************/

	public function fetch_addrbook($conds, $fields, $orderby = array(), $start = 0, $limit = 0) {

		try {
			return (array)voa_d_oa_member::fetch_addrbook($conds, $fields, $orderby, $start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据用户名读取用户信息
	 * @param string $username 用户名
	 * @throws service_exception
	 */
	public function fetch_by_username($username) {
		try {
			return voa_d_oa_member::fetch_by_username($username, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 uid 读取用户信息
	 * @param int $uid 用户 uid
	 * @throws service_exception
	 */
	public function fetch_by_uid($uid) {
		try {
			return voa_d_oa_member::fetch_by_uid($uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 openid 读取用户信息
	 * @param string $username 用户名
	 * @param boolean $force 是否强制读取
	 */
	public function fetch_by_openid($openid, $force = false) {
		try {
			return voa_d_oa_member::fetch_by_openid($openid, $force, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据手机号码获取用户信息
	 * @param string $mobile 手机号码
	 * @throws service_exception
	 */
	public function fetch_by_mobilephone($mobile) {
		try {
			return voa_d_oa_member::fetch_by_mobilephone($mobile, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}


	/**
	 * 根据微信号获取用户信息
	 * author: ppker
	 * date: 2015/07/16
	 * @param string $weixin 微信号
	 * @throws service_exception
	 */
	public function fetch_by_weixin($weixin) {
		try {
			return voa_d_oa_member::fetch_by_weixin($weixin, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}



	/**
	 * 获取有效用户(不包括待验证的)
	 * @param int $start
	 * @param int $limit
	 */
	public function fetch_valid($start = 0, $limit = 0) {
		try {
			return voa_d_oa_member::fetch_valid($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定uid的用户
	 * @param array $uids
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_ids($uids) {
		try {
			return voa_d_oa_member::fetch_all_by_ids($uids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 列出指定openid的用户
	 * @param array $openids
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_by_openids($openids) {

		try {
			return voa_d_oa_member::fetch_all_by_openids($openids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计某个部门cd_id下的成员数
	 * @param number $cd_id
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_cd_id($cd_id) {
		try {
			return voa_d_oa_member::count_by_cd_id($cd_id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 从通讯录中搜索
	 * @param string $sotext 搜索条件
	 * @throws service_exception
	 */
	public function so_addressbook($sotext) {
		try {
			return voa_d_oa_member::so_addressbook($sotext, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据email读取用户信息
	 * @param string $email 邮箱地址
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_email($email) {
		try {
			return voa_d_oa_member::fetch_by_email($email, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计某个字段的数据被登记次数，除了m_uid为$m_uid的之外
	 * @param string $field 字段名
	 * @param string $value 值
	 * @param number $m_uid
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_field_not_uid($field, $value, $m_uid = 0) {
		try {
			return voa_d_oa_member::count_by_field_not_uid($field, $value, $m_uid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过微信unionid找到指定用户信息
	 * @param string $unionid
	 * @throws service_exception
	 * @return Ambigous <multitype:, array>
	 */
	public function fetch_by_unionid($unionid) {
		try {
			return voa_d_oa_member::fetch_by_unionid($unionid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_wechatid($wechatid) {

		try {
			return voa_d_oa_member::fetch_by_wechatid($wechatid, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 通过手机号或者邮箱地址找到所有相关联的uid
	 * @param string|array $account
	 * @param string $type mobilephone|email
	 * @throws service_exception
	 * @return multitype:
	 */
	public function fetch_all_uid_by_account($account, $type) {
		try {
			return voa_d_oa_member::fetch_all_uid_by_account($account, $type, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function count_by_cdids_uids($cdids, $uids) {

		try {
			return voa_d_oa_member::count_by_cdids_uids($cdids, $uids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

	}

}
