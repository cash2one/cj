<?php
/**
 * voa_d_uc_member2enterprise
 * UC/会员与企业对应表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_uc_member2enterprise extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.member2enterprise';
	/** 主键 */
	private static $__pk = 'mep_id';
	/** 字段前缀 */
	private static $__prefix = 'mep_';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

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

	/**********************************************/

	/**
	 * 更新指定企业的指定用户的登录账号信息
	 * @param number $ep_id
	 * @param number $m_uid
	 * @param array $data
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>|Ambigous <mixed, boolean>|boolean
	 */
	public static function update_by_ep_id_m_uid($ep_id, $m_uid, $data, $shard_key = array()) {
		if (empty($data)) {
			return true;
		}
		$mep = parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('ep_id', $ep_id), db_help::field('m_uid', $m_uid)
		), $shard_key);
		if ($mep) {
			$new_data = array();
			if (isset($data['mobilephone']) && $data['mobilephone'] != $mep['mep_mobilephone']) {
				$new_data['mobilephone'] = $data['mobilephone'];
			}
			if (isset($data['email']) && $data['email'] != $mep['mep_email']) {
				$new_data['email'] = $data['email'];
			}
			if (isset($data['unionid']) && $data['unionid'] != $mep['unionid']) {
				$new_data['unionid'] = $data['unionid'];
			}
			if (!empty($new_data)) {
				return self::update($new_data, $mep['mep_id']);
			}
		} else {
			return self::insert(array(
				'ep_id' => $ep_id,
				'm_uid' => $m_uid,
				'mep_mobilephone' => isset($data['mobilephone']) ? $data['mobilephone'] : '',
				'mep_email' => isset($data['email']) ? $data['email'] : '',
				'mep_unionid' => isset($data['unionid']) ? $data['unionid'] : '',
			));
		}

		return true;
	}

	/**
	 * 找到指定企业的指定email帐号的信息
	 * @param string $email
	 * @param number $ep_id
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_email_ep_id($email, $ep_id, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('mep_email', $email), db_help::field('ep_id', $ep_id),
			db_help::field('mep_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 根据微信 unionid 找到对应绑定的用户列表信息
	 * @param string $unionid
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_by_unionid($unionid, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i", array(
			self::$__table,
			db_help::field(self::fieldname('unionid'), $unionid),
			db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
	}

}
