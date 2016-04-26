<?php
/**
 * voa_d_oa_common_addressbook
 * 通讯录表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_addressbook extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.common_addressbook';
	/** 主键 */
	private static $__pk = 'cab_id';
	/** 字段前缀 */
	private static $__prefix = 'cab_';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/**********************/

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
	public static function fetch($value, $shard_key = array()) {
		return parent::_fetch_first(self::$__table,
			"SELECT * FROM %t WHERE %i='%d' AND %i<'%d' LIMIT 1",
			array(self::$__table, self::$__pk, $value, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param string|number $value 主键值
	 */
	public static function update($data, $value, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}

		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, array(self::$__pk => $value), false, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键删除</strong></p>
	 * @author Deepseath
	 * @param array|number $value 主键值
	 */
	public static function delete($value, $shard_key = array()) {
		return self::delete_by_conditions(array(self::$__pk => $value), $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
	 * @author Deepseath
	 * @return array
	 */
	public static function fetch_all_field($shard_key = array()) {
		return parent::_fetch_all_field(self::$__table, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有数据</strong></p>
	 * @author Deepseath
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table,
			"SELECT * FROM %t WHERE %i<'%d' ".db_help::limit($start, $limit),
			array(self::$__table, self::fieldname('status'), self::STATUS_REMOVE), self::$__pk, $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
	 * @author Deepseath
	 * @return number
	 */
	public static function count_all($shard_key = array()) {
		return (int) parent::_result_first(self::$__table,
			"SELECT COUNT(%i) FROM %t WHERE %i<'%d'",
			array(self::$__pk, self::$__table, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】数据入库</strong></p>
	 * @author Deepseath
	 * @param array $data 入库数据数组
	 * @param boolean $return_insert_id
	 * @param boolean $replace
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_NORMAL;
		}

		if (empty($data[self::fieldname('created')])) {
			$data[self::fieldname('created')] = startup_env::get('timestamp');
		}

		if (empty($data[self::fieldname('updated')])) {
			$data[self::fieldname('updated')] = $data[self::fieldname('created')];
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param array|string $conditions 更新条件
	 */
	public static function update_by_conditions($data, $conditions, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}

		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $conditions, false, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
	 * @author Deepseath
	 * @param array $conditions 删除条件
	 * @return void
	 */
	public static function delete_by_conditions($conditions, $shard_key = array()) {
		return self::update_by_conditions(array(
			self::fieldname('status') => self::STATUS_REMOVE,
			self::fieldname('deleted') => startup_env::get('timestamp')
		), $conditions, $shard_key);
	}

	/**
	 * (d) 根据条件找到一条记录
	 * @param array $conditions
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $shard_key = array()) {
		$where = array();
		foreach ($conditions AS $k=>$v) {
			$where[] = db_help::field($k, $v);
		}

		$where = $where ? implode(' AND ', $where) : 1;
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1",
			array(self::$__table, $where, db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<')), $shard_key
		);
	}

	/**********************************************/

	/**
	 * (d) 计算手机号使用次数，除cab_id外
	 * @author Deepseath
	 * @param string $mobilephone
	 * @param number $cab_id
	 */
	public static function count_by_mobilephone_notid($mobilephone, $cab_id, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(`cab_mobilephone`) FROM %t WHERE %i AND %i AND %i", array(
				self::$__table,
				db_help::field('cab_mobilephone', $mobilephone),
				db_help::field('cab_status', self::STATUS_REMOVE, '<'),
				$cab_id ? db_help::field('cab_id', $cab_id, '<>') : 1
			), $shard_key
		);
	}

	/**
	 * 构造查询条件
	 * @param array $conditions
	 * @param null|boolean $haveMember 是否查询已绑定member表的用户,null不限,true查询绑定的，false查询未绑定的
	 * @return string
	 */
	public static function where_conditions($conditions, $haveMember = null) {
		$where = array();
		if (isset($conditions[self::$__pk])) {
			$where[] = db_help::field(self::$__pk, $conditions[self::$__pk]);
		}

		if (isset($conditions['cab_mobilephone'])) {
			$where[] = db_help::field('cab_mobilephone', '%'.addcslashes($conditions['cab_mobilephone'], '%_').'%', 'like');
		}

		if (isset($conditions['cab_active'])) {
			$where[] = db_help::field('cab_active', $conditions['cab_active']);
		}

		if (isset($conditions['cd_id'])) {
			$where[] = db_help::field('cd_id',$conditions['cd_id']);
		}

		if (isset($conditions['cj_id'])) {
			$where[] = db_help::field('cj_id', $conditions['cj_id']);
		}

		if (isset($conditions['cab_realname'])) {
			$where[] = db_help::field('cab_realname', '%'.addcslashes($conditions['cab_realname'], '%_').'%', 'like');
		}

		if (!empty($conditions['m_uid'])) {
			$where[] = db_help::field('m_uid', $conditions['m_uid']);
		} else {
			/** 是否查询已绑定的用户 */
			if ($haveMember !== null) {
				$where[] = db_help::field('m_uid', 0, ($haveMember ? '>' : '='));
			}
		}

		$where[] = db_help::field('cab_status', self::STATUS_REMOVE, '<');
		return $where ? implode(' AND ',$where) : 1;
	}

	/**
	 * 根据条件计算数据数
	 * @param array $conditions
	 * @param null|boolean $haveMember 是否查询已绑定
	 * @return number
	 */
	public static function count_by_conditions($conditions, $haveMember = null, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`cab_id`) FROM %t WHERE %i", array(
			self::$__table, self::where_conditions($conditions, $haveMember)
		), $shard_key);
	}

	/**
	 * 根据条件列出数据
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @param null|boolean $haveMember 是否查询已绑定
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $haveMember = null, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY `cab_id` DESC %i", array(
			self::$__table,
			self::where_conditions($conditions, $haveMember),
			($start === true || $limit === true) ? db_help::limit(0, 1000000) : db_help::limit($start, $limit)
		), self::$__pk, $shard_key);
	}

	/** 根据手机号码登录 */
	public static function fetch_by_mobilephone($phone, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `cab_mobilephone`=%s AND `cab_status`<'%d'", array(
				self::$__table, $phone, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 根据 openid 获取用户信息
	 * @param string $openid
	 * @throws service_exception
	 * @return array
	 */
	public static function fetch_by_openid($openid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE m_openid=%s AND cab_status<'%d'", array(
				self::$__table, $openid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 统计某个字段的数据被登记次数，除了cab_id外
	 * 如，统计email被登记次数
	 * @param string $field 字段名
	 * @param string $value 数据
	 * @param number $cab_id 除此cab_id外
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_by_field_notid($field, $value, $cab_id = 0, $shard_key = array()) {
		$cab_id = (int)$cab_id;
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`cab_id`) FROM %t WHERE %i AND %i AND %i", array(
				self::$__table,
				db_help::field($field, $value),
				db_help::field('cab_status', self::STATUS_REMOVE, '<'),
				$cab_id ? db_help::field('cab_id', $cab_id, '<>') : 1
		), $shard_key
		);
	}

	/** 按email查询 */
	public static function fetch_by_email($email, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `cab_email`=%s AND `cab_status`<'%d'", array(
				self::$__table, $email, self::STATUS_REMOVE
			), $shard_key
		);
	}

}
