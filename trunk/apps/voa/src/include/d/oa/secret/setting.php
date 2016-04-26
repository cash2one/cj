<?php
/**
 * 秘密配置表
 * $Author$
 * $Id$
 */

class voa_d_oa_secret_setting extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.secret_setting';
	/** 主键 */
	private static $__pk = 'sts_key';
	/** 所有字段名 */
	private static $__fields = array(
		'sts_key', 'sts_value', 'sts_type', 'sts_comment',
		'sts_created', 'sts_updated', 'sts_deleted'
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
			WHERE `sts_status`<'%d'
			ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_pk($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `%i`='%d' AND `sts_status`<'%d'", array(
				self::$__table, self::$__pk, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_pks($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i IN (%n) AND `sts_status`<'%d'
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i", array(
			self::$__table, self::parse_conditions($conditions)
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
			WHERE %i ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::$__pk
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
		if (empty($data['sts_status'])) {
			$data['sts_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['sts_created'])) {
			$data['sts_created'] = startup_env::get('timestamp');
		}

		if (empty($data['sts_updated'])) {
			$data['sts_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['sts_updated'])) {
			$data['sts_updated'] = startup_env::get('timestamp');
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
			'sts_status' => self::STATUS_REMOVE,
			'sts_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $pks), $unbuffered, false, $shard_key);
	}

	/**
	 * 以变量名为键名输出所有变量信息
	 * @return array
	 */
	public static function fetch_all_setting($shard_key = array()) {
		$list = array();
		$prefix = 'sts_';
		$query = parent::_query(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY `{$prefix}key` ASC", array(
				self::$__table, db_help::field($prefix.'status', self::STATUS_REMOVE, '<')
		), $shard_key);
		while ($row = parent::_fetch(self::$__table, $query)) {
			$list[$row[$prefix.'key']] = array(
					'key' => $row[$prefix.'key'],
					'value' => $row[$prefix.'value'],
					'type' => $row[$prefix.'type'],
					'comment' => $row[$prefix.'comment']
			);
		}

		return $list;
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public static function update_setting($data, $shard_key = array()) {
		$prefix = 'sts_';
		/** 确定需要进行更新还是插入 */
		$is_update_keys = array();
		$tmp = parent::_fetch_all(self::$__table, "SELECT `{$prefix}key` FROM %t WHERE %i", array(
				self::$__table, db_help::field($prefix.'key', array_keys($data)), db_help::field($prefix.'status', self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
		foreach ($tmp AS $row) {
			$is_update_keys[$row[$prefix.'key']] = $row[$prefix.'key'];
		}

		foreach ($data as $key => $value) {
			$data = array(
					$prefix.'value' => $value,
					$prefix.'status' => self::STATUS_UPDATE,
					$prefix.'updated' => startup_env::get('timestamp')
			);
			if (isset($is_update_keys[$key])) {
				parent::_update(self::$__table, $data, array($prefix.'key' => $key), false, false, $shard_key);
			} else {
				$data[$prefix.'key'] = $key;
				$data[$prefix.'type'] = @unserialize($value) === false ? 0 : 1;
				$data[$prefix.'status'] = self::STATUS_NORMAL;
				$data[$prefix.'created'] = startup_env::get('timestamp');
				parent::_insert(self::$__table, $data, false, false, false, $shard_key);
			}
		}

		return true;
	}

}

