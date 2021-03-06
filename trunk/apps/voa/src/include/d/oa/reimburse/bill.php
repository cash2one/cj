<?php
/**
 * 清单信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_reimburse_bill extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.reimburse_bill';
	/** 主键 */
	private static $__pk = 'rbb_id';
	/** 所有字段名 */
	private static $__fields = array(
		'rbb_id', 'm_uid', 'm_username', 'rbb_type', 'rbb_time', 'rbb_expend', 'rbb_reason', 'rbbat_id',
		'rbb_status', 'rbb_created', 'rbb_updated', 'rbb_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已使用 */
	const STATUS_USED = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;
	/** 订单状态 */
	const BILL_ALL = 0;
	const BILL_NORMAL = 1;
	const BILL_USED = 2;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `rbb_status`<'%d'
			ORDER BY `rbb_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `rbb_id`='%d' AND `rbb_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `rbb_id` IN (%n) AND `rbb_status`<'%d'
			ORDER BY `rbb_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 uid 读取回复的信息
	 * @param int $uid 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @param int $status
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $status = 0, $shard_key = array()) {
		$wheres = array("m_uid=".$uid);
		if (self::BILL_NORMAL == $status) {
			$wheres[] = "rbb_status=".self::BILL_NORMAL;
		} elseif (self::BILL_USED == $status) {
			$wheres[] = "rbb_status=".self::BILL_USED;
		} else {
			$wheres[] = "rbb_status<".self::STATUS_REMOVE;
		}

		$wherestr = implode(' AND ', $wheres);

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `rbb_id` DESC", array(
				self::$__table, $wherestr
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据查询条件拼凑 sql 条件
	 * @param array $conditions 查询条件
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 */
	public static function parse_conditions($conditions = array()) {
		$wheres = array();
		/** 遍历条件 */
		foreach ($conditions as $field => $v) {
			/** 非当前表字段 */
			if (!in_array($field, self::$__fields)) {
				continue;
			}

			$f_v = $v;
			$gule = '=';
			/** 如果条件为数组, 则 */
			if (is_array($v)) {
				$f_v = $v[0];
				$gule = empty($v[1]) ? '=' : $v[1];
			}

			$wheres[] = db_help::field($field, $f_v, $gule);
		}

		return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND rbb_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的记录
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND rbb_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['rbb_status'])) {
			$data['rbb_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['rbb_created'])) {
			$data['rbb_created'] = startup_env::get('timestamp');
		}

		if (empty($data['rbb_updated'])) {
			$data['rbb_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['rbb_updated'])) {
			$data['rbb_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'rbb_status' => self::STATUS_REMOVE,
			'rbb_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除指定条件的数据
	 * @param array $conditions
	 * @param array $shard_key
	 * @param boolean $unbuffered
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_conditions($conditions, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'rbb_status' => self::STATUS_REMOVE,
			'rbb_deleted' => startup_env::get('timestamp')
		), self::parse_conditions($conditions), $unbuffered, false, $shard_key);
	}
}

