<?php
/**
 * voa_d_oa_member
 * 用户表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_member extends dao_mysql {

	/** 表名 */
	public static $__table = 'oa.member';
	/** 主键 */
	private static $__pk = 'm_uid';
	/** 字段前缀 */
	private static $__prefix = 'm_';
	/** 所有字段名 */
	private static $__fields = array(
		'm_uid', 'm_openid', 'm_mobilephone', 'm_email', 'm_unionid', 'm_active', 'm_username',
		'm_index', 'm_password', 'm_number', 'm_admincp', 'cug_groupid', 'cd_id', 'cj_id', 'm_gender',
		'm_face', 'm_salt', 'm_qywxstatus', 'm_status', 'm_created', 'm_updated', 'm_deleted', 'm_weixin'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 待验证 */
	//const STATUS_VERIFY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/**********************/

	/** 性别常量：性别未登记 */
	const GENDER_UNKNOWN = 0;
	/** 性别常量：男 */
	const GENDER_MALE = 1;
	/** 性别常量：女 */
	const GENDER_FEMALE = 2;

	/** 在职状态：在职 */
	const ACTIVE_YES = 1;
	/** 在职状态：离职 */
	const ACTIVE_NO = 0;

    /** 微信状态：未关注 */
    const WX_STATUS_UNFOLLOW = 4;
    /** 微信状态：已关注 */
    const WX_STATUS_FOLLOWED = 1;
    /** 微信状态：已冻结 */
    const WX_STATUS_FREEZE = 2;

    /*用户来源：扫码关注*/
    const QRCODE_RESOURCE = 1;
    /*用户来源：系统*/
    const SYSTEM_RESOURCE = 2;
    /*用户来源：其它*/
    const OTHER_RESOURCE = 3;

	/**********************/

	/**
	 * <p><strong style="color:blue">【D】获取带前缀的字段名</strong></p>
	 * @author Deepseath
	 * @param string $field 无前缀的字段名
	 * @return string 带前缀的字段名
	 */
	public static function fieldname($field) {
		return self::$__prefix.$field;
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键值获取单条数据</strong></p>
	 * @author Deepseath
	 * @param int $value 主键值
	 */
	public static function fetch($value) {
		return parent::_fetch_first(self::$__table,
				"SELECT * FROM %t WHERE %i='%d' AND %i<'%d' LIMIT 1",
				array(self::$__table, self::$__pk, $value, self::fieldname('status'), self::STATUS_REMOVE)
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param string|number $value 主键值
	 */
	public static function update($data, $value) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}
		if (empty($data[self::fieldname('updated')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}
		return parent::_update(self::$__table, $data, array(self::$__pk => $value));
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键删除</strong></p>
	 * @author Deepseath
	 * @param array|number $value 主键值
	 */
	public static function delete($value) {
		return self::delete_by_conditions(array(self::$__pk => $value));
	}

	/**
	 * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
	 * @author Deepseath
	 * @return array
	 */
	public static function fetch_all_field() {
		return parent::_fetch_all_field(self::$__table);
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有数据</strong></p>
	 * @author Deepseath
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function fetch_all($start = 0, $limit = 0) {
		return (array)parent::_fetch_all(self::$__table,
				"SELECT * FROM %t WHERE %i<'%d' ".db_help::limit($start, $limit),
				array(self::$__table, self::fieldname('status'), self::STATUS_REMOVE), self::$__pk
		);
	}

	/**
	 * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
	 * @author Deepseath
	 * @return number
	 */
	public static function count_all() {
		return (int) parent::_result_first(self::$__table,
				"SELECT COUNT(%i) FROM %t WHERE %i<'%d'",
				array(self::$__pk, self::$__table, self::fieldname('status'), self::STATUS_REMOVE)
		);
	}

	/**
	 * <p><strong style="color:blue">【D】数据入库</strong></p>
	 * @author Deepseath
	 * @param array $data 入库数据数组
	 * @param boolean $return_insert_id
	 * @param boolean $replace
	 */
	public static function insert($data, $return_insert_id = false, $replace = false) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_NORMAL;
		}
		if (empty($data[self::fieldname('created')])) {
			$data[self::fieldname('created')] = startup_env::get('timestamp');
		}
		if (empty($data[self::fieldname('updated')])) {
			$data[self::fieldname('updated')] = $data[self::fieldname('created')];
		}
		return parent::_insert(self::$__table, $data, $return_insert_id, $replace);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param array|string $conditions 更新条件
	 */
	public static function update_by_conditions($data, $conditions) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}
		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		$conditions['m_status'] = array(
			//self::STATUS_NORMAL, self::STATUS_UPDATE, self::STATUS_VERIFY
			self::STATUS_NORMAL, self::STATUS_UPDATE
		);
		return parent::_update(self::$__table, $data, $conditions);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
	 * @author Deepseath
	 * @param array $conditions 删除条件
	 * @return void
	 */
	public static function delete_by_conditions($conditions) {
		return self::update_by_conditions(array(
				self::fieldname('status') => self::STATUS_REMOVE,
				self::fieldname('deleted') => startup_env::get('timestamp')
		), $conditions);
	}

	/**
	 * <strong style="color:blue">【D】根据查询条件拼凑 SQL 条件语句</strong>
	 * @param array $conditions
	 * <pre>$conditions = array(
	 * 		'field1' => '查询条件', // 运算符为 =
	 * 		'field2' => array('查询条件', '查询运算符'),
	 * 		'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 * )</pre>
	 * @return string
	 */
	public static function parse_conditions($conditions = array()) {
		if (!isset($conditions[self::fieldname('status')])) {
			// 忽略已删除数据
			$conditions[self::fieldname('status')] = array(self::STATUS_REMOVE, '<');
		}
		$wheres = array();
		// 遍历条件
		foreach ($conditions as $field => $v) {

			if (!in_array($field, self::$__fields)) {
				// 非当前表字段则忽略
				continue;
			}

			$f_v = $v;
			$gule = '=';

			if (is_array($v)) {
				// 如果条件为数组
				$f_v = $v[0];
				$gule = empty($v[1]) ? '=' : $v[1];
			}

			$wheres[] = db_help::field($field, $f_v, $gule);
		}

		return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}

	/**
	 * <strong style="color:blue">【D】根据条件计算数据数</strong>
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i", array(
			self::$__pk, self::$__table, self::parse_conditions($conditions)
		), $shard_key);
	}

	/**
	 * <strong style="color:blue">【D】根据条件列出数据</strong>
	 * @param array $conditions 查询条件，
	 * @see self::parse_conditions
	 * @param array $orderby 排序方式
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $orderby, $start = 0, $limit = 0, $shard_key = array()) {
		if (empty($orderby)) {
			$orderby = array(self::$__pk => 'DESC');
		}
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i %i %i", array(
			self::$__table, self::parse_conditions($conditions), db_help::orders($orderby), db_help::limit($start, $limit)
		), self::$__pk, $shard_key);
	}

	/**********************************************/

	public static function fetch_addrbook($conds, $fields, $orderby = array(), $start = 0, $limit = 0, $shard_key = array()) {

		if (empty($orderby)) {
			$orderby = array('m_displayorder' => 'DESC');
		}

		return (array)parent::_fetch_all(self::$__table, "SELECT %i FROM %t WHERE %i %i %i", array(
			$fields, self::$__table, self::parse_conditions($conds), db_help::orders($orderby), db_help::limit($start, $limit)
		), self::$__pk, $shard_key);
	}

	/**
	 * 根据用户名读取用户
	 * @param string $username 用户名
	 */
	public static function fetch_by_username($username, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
				self::$__table, db_help::field('m_username', $username), db_help::field('m_status', self::STATUS_REMOVE, '<')
		));
	}

	/**
	 * 根据 uid 读取用户信息
	 * @param int $uid uid
	 */
	public static function fetch_by_uid($uid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND m_status<%d", array(
				self::$__table, $uid, self::STATUS_REMOVE
			)
		);
	}

	/**
	 * 根据 openid 读取用户信息
	 * @param string $openid
	 * @param boolean $force 是否强制读取
	 */
	public static function fetch_by_openid($openid, $force = false, $shard_key = array()) {
		$wheres = array('m_openid=%s');
		$params = array($openid);
		if (false == $force) {
			$wheres[] = 'm_status<%d';
			$params[] = self::STATUS_REMOVE;
		}

		$wherestr = implode(' AND ', $wheres);
		array_unshift($params, self::$__table);

		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE {$wherestr}", $params
		);
	}

	/**
	 * 根据手机号码获取用户信息
	 * @param string $mobile 手机号码
	 * @throws service_exception
	 */
	public static function fetch_by_mobilephone($mobile, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `m_mobilephone`=%s AND m_status<%d ORDER BY m_uid ASC", array(
				self::$__table, $mobile, self::STATUS_REMOVE
			)
		);
	}



	/**
	 * 根据微信号获取用户信息
	 * author: ppker
	 * date: 2015/07/16
	 * @param string $weixin 微信号
	 * @throws service_exception
	 */
	public static function fetch_by_weixin($weixin, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `m_weixin`=%s AND m_status<%d ORDER BY m_uid ASC", array(
				self::$__table, $weixin, self::STATUS_REMOVE
			)
		);
	}


	/**
	 * 获取有效用户(不包括待验证的)
	 * @param int $start
	 * @param int $limit
	 */
	public static function fetch_valid($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_status` IN (%n) ORDER BY `m_uid` DESC ".db_help::limit($start, $limit), array(
				self::$__table, array(self::STATUS_NORMAL, self::STATUS_UPDATE)
			), self::$__pk
		);
	}

	/**
	 * 列出所有指定uid的用户
	 * @param array $ids
	 * @return array
	 */
	public static function fetch_all_by_ids($ids, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid` IN (%n) AND `m_status`<'%d'
			ORDER BY `m_uid` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk
		);
	}

	/**
	 * 列出所有指定 openid 的用户
	 * @param array $openids
	 * @return array
	 */
	public static function fetch_all_by_openids($openids, $shard_key = array()) {

		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_openid` IN (%n) AND `m_status`<'%d'", array(
				self::$__table, $openids, self::STATUS_REMOVE
			), self::$__pk
		);
	}

	/**
	 * 统计某个部门id的成员数
	 * @param number $cd_id
	 * @return number
	 */
	public static function count_by_cd_id($cd_id, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`m_uid`) FROM %t WHERE %i AND %i", array(
			self::$__table, db_help::field('cd_id', $cd_id), db_help::field('m_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 从通讯录中搜索
	 * @param string $sotext 搜索条件
	 * @param array $shard_key 分库分表参数
	 */
	public static function so_addressbook($sotext, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t AS a
			LEFT JOIN %t AS b ON a.cab_id=b.cab_id
			WHERE a.m_username LIKE %s AND m_status<%d ORDER BY %s DESC", array(
				self::$__table, voa_d_oa_common_addressbook::$__table, "%{$sotext}%",
				self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据邮箱地址获取用户信息
	 * @param string $email 邮箱地址
	 * @throws service_exception
	 */
	public static function fetch_by_email($email, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND m_status<%d LIMIT 1", array(
			self::$__table, db_help::field('m_email', $email), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 统计某个字段的数据被登记次数，除了m_uid为$m_uid的之外
	 * @param string $field 字段名
	 * @param string $value 值
	 * @param number $m_uid
	 * @param unknown $shard_key
	 * @return number
	 */
	public static function count_by_field_not_uid($field, $value, $m_uid, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`m_uid`) FROM %t
			WHERE %i AND %i AND m_status<%d", array(
			self::$__table, db_help::field($field, $value),
			$m_uid > 0 ? db_help::field('m_uid', $m_uid, '<>') : 1, self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 通过微信unionid找到指定用户信息
	 * @param string $unionid
	 * @param unknown $shard_key
	 * @return array
	 */
	public static function fetch_by_unionid($unionid, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('m_unionid', $unionid), db_help::field('m_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 通过微信unionid找到指定用户信息
	 * @param string $wechatid
	 * @param unknown $shard_key
	 * @return array
	 */
	public static function fetch_by_wechatid($wechatid, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('m_wechatid', $wechatid), db_help::field('m_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}


	/**
	 * 找到与给定的手机号或者邮箱地址相关联的所有uid
	 * @param string|array $account
	 * @param string $type mobilephone|email
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_uid_by_account($account, $type, $shard_key = array()) {

		$field = $type == 'email' ? 'm_email' : 'm_mobilephone';
		$conds = array();
		$conds[$field] = array($account, is_array($account) ? 'in' : '=');
		$conds['m_status'] = array(self::STATUS_REMOVE, '<');

		return array_keys(self::fetch_all_by_conditions($conds, array()));
	}

	public static function count_by_cdids_uids($cdids, $uids, $shard_key = array()) {

		if (empty($cdids) && empty($uids)) {
			return 0;
		}

		$where = array();
		if (!empty($cdids)) {
			$where[] = db_help::field('cd_id', $cdids);
		}

		if (!empty($uids)) {
			$where[] = db_help::field('m_uid', $uids);
		}

		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`m_uid`) FROM %t
			WHERE (%i) AND m_status<%d", array(
			self::$__table, implode(' OR ', $where), self::STATUS_REMOVE
		), $shard_key);
	}
}
