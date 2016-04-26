<?php
/**
 * 销售轨迹主题表
 * $Author$
 * $Id$
 */

class voa_d_oa_footprint extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.footprint';
	/** 主键 */
	private static $__pk = 'fp_id';
	/** 所有字段名 */
	private static $__fields = array(
		'fp_id', 'm_uid', 'm_username', 'fp_subject', 'fp_visittime', 'fp_type',
		'fp_address', 'fp_status', 'fp_created', 'fp_updated', 'fp_deleted'
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
			WHERE `fp_status`<'%d'
			ORDER BY `fp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `fp_id`='%d' AND `fp_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `fp_id` IN (%n) AND `fp_status`<'%d'
			ORDER BY `fp_id` DESC", array(
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
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND fp_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的数据
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND fp_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['fp_status'])) {
			$data['fp_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['fp_created'])) {
			$data['fp_created'] = startup_env::get('timestamp');
		}

		if (empty($data['fp_updated'])) {
			$data['fp_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['fp_updated'])) {
			$data['fp_updated'] = startup_env::get('timestamp');
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
			'fp_status' => self::STATUS_REMOVE,
			'fp_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据类型和访问时间统计总数
	 * @param int $btime
	 * @param int $etime
	 * @param string|int $type
	 * @throws service_exception
	 */
	public static function count_by_visittime_and_type($btime, $etime, $type = 0, $shard_key = array()) {
		$wheres = array('fp_visittime>%d AND fp_visittime<%d');
		$params = array($btime, $etime);
		if (!empty($type)) {
			$wheres[] = 'fp_type=%d';
			$params[] = $type;
		}

		$wherestr = implode(' AND ', $wheres);
		array_unshift($params, self::$__table);

		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE ".$wherestr, $params, $shard_key
		);
	}

	/**
	 * 根据访问时间统计总数
	 * @param int $btime
	 * @param int $etime
	 * @throws service_exception
	 */
	public static function count_week_by_visittime_and_type($btime, $etime, $type = 0, $shard_key = array()) {
		$wheres = array('fp_visittime>%d AND fp_visittime<%d');
		$params = array($btime, $etime);
		if (!empty($type)) {
			$wheres[] = 'fp_type=%d';
			$params[] = $type;
		}

		$wherestr = implode(' AND ', $wheres);
		array_unshift($params, self::$__table);

		return parent::_fetch_all(self::$__table, "SELECT fp_visitweek, COUNT(*) as `count` FROM %t
			WHERE ".$wherestr." GROUP BY fp_visitweek", $params, 'fp_visitweek', $shard_key
		);
	}

	/**
	 * 根据访问时间统计总数
	 * @param int $btime
	 * @param int $etime
	 * @param string|int $type
	 * @throws service_exception
	 */
	public static function count_day_by_visittime_and_type($btime, $etime, $type = 0, $shard_key = array()) {
		$wheres = array('fp_visittime>%d AND fp_visittime<%d');
		$params = array($btime, $etime);
		if (!empty($type)) {
			$wheres[] = 'fp_type=%d';
			$params[] = $type;
		}

		$wherestr = implode(' AND ', $wheres);
		array_unshift($params, self::$__table);

		return parent::_fetch_all(self::$__table, "SELECT fp_visitynd, COUNT(*) as `count` FROM %t
			WHERE ".$wherestr." GROUP BY fp_visitynd", $params, 'fp_visitynd', $shard_key
		);
	}
}

