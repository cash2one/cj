<?php
/**
 * 定时提醒配置表
 * $Author$
 * $Id$
 */

class voa_d_oa_remind_setting extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.remind_setting';
	/** 主键 */
	private static $__pk = 'nts_key';
	/** 所有字段名 */
	private static $__fields = array(
		'nts_key', 'nts_value', 'nts_type', 'nts_comment',
		'nts_created', 'nts_updated', 'nts_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `nts_status`<'%d'
			ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_pk($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `%i`='%d' AND `nts_status`<'%d'", array(
				self::$__table, self::$__pk, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_pks($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i IN (%n) AND `nts_status`<'%d'
			ORDER BY %i DESC", array(
				self::$__table, self::$__pk, $ids, self::STATUS_REMOVE, self::$__pk
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND nts_status", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的数据
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND nts_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['nts_status'])) {
			$data['nts_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['nts_created'])) {
			$data['nts_created'] = startup_env::get('timestamp');
		}

		if (empty($data['nts_updated'])) {
			$data['nts_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['nts_updated'])) {
			$data['nts_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除信息
	 *
	 * @param int|array $pks ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_pks($pks, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'nts_status' => self::STATUS_REMOVE,
			'nts_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $pks), $unbuffered, false, $shard_key);
	}
}

