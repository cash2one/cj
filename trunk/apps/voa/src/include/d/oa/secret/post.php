<?php
/**
 * 秘密回复信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_secret_post extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.secret_post';
	/** 主键 */
	private static $__pk = 'stp_id';
	/** 所有字段名 */
	private static $__fields = array(
		'stp_id', 'm_uid', 'm_username', 'st_id', 'stp_subject', 'stp_message', 'stp_first',
		'stp_status', 'stp_created', 'stp_updated', 'stp_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 不是主题 */
	const FIRST_NO = 0;
	/** 是主题 */
	const FIRST_YES = 1;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `stp_status`<'%d'
			ORDER BY `stp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `stp_id`='%d' AND `stp_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `stp_id` IN (%n) AND `stp_status`<'%d'
			ORDER BY `stp_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i", array(
			self::$__table, self::parse_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的投票
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增投票信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['stp_status'])) {
			$data['stp_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['stp_created'])) {
			$data['stp_created'] = startup_env::get('timestamp');
		}

		if (empty($data['stp_updated'])) {
			$data['stp_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['stp_updated'])) {
			$data['stp_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除投票信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'stp_status' => self::STATUS_REMOVE,
			'stp_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 计算一组主题的回复数
	 * @param array $st_ids
	 * @param array $shard_key
	 * @return array
	 */
	public static function count_group_by_st_ids($st_ids, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT `st_id`, COUNT(`stp_id`) AS `_count` FROM %t
			WHERE %i AND %i AND %i GROUP BY `st_id`", array(
					self::$__table, db_help::field('st_id', $st_ids), db_help::field('stp_first', self::FIRST_NO),
					db_help::field('stp_status', self::STATUS_REMOVE, '<')
			), 'st_id', $shard_key);
	}

	/**
	 * 找到主题的内容
	 * @param number $st_id
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_st_id($st_id, $shard_key = array()) {
		return (array) parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i AND %i LIMIT 1", array(
				self::$__table, db_help::field('st_id', $st_id), db_help::field('stp_first', self::FIRST_YES),
				db_help::field('stp_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 列出指定某个主题的回复列表
	 * @param number $st_id
	 * @param number $start
	 * @param number $limit
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_by_st_id($st_id, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i AND %i
				ORDER BY `stp_id` ASC %i", array(
							self::$__table, db_help::field('st_id', $st_id), db_help::field('stp_first', self::FIRST_NO),
							db_help::field('stp_status', self::STATUS_REMOVE, '<'), db_help::limit($start, $limit)
					), self::$__pk, $shard_key);
	}

	/**
	 * 统计指定某个主题的回复数
	 * @param number $st_id
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_all_by_st_id($st_id, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`stp_id`) FROM %t WHERE %i AND %i AND %i", array(
				self::$__table, db_help::field('st_id', $st_id), db_help::field('stp_first', self::FIRST_NO),
				db_help::field('stp_status', self::STATUS_REMOVE, '<')
		), $shard_key);
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
				'stp_status' => self::STATUS_REMOVE,
				'stp_deleted' => startup_env::get('timestamp')
		), self::parse_conditions($conditions), $unbuffered, false, $shard_key);
	}
}

