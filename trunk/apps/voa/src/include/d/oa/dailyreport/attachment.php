<?php
/**
 * voa_d_oa_dailyreport_attachment
 * 应用/日报/附件表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_dailyreport_attachment extends dao_mysql {

	/** 表名 */
	public static $__table = 'oa.dailyreport_attachment';
	/** 主键 */
	private static $__pk = 'drat_id';
	/** 字段前缀 */
	private static $__prefix = 'drat_';
	/** 所有字段名 */
	private static $__fields = array(
		'drat_id', 'dr_id', 'drp_id', 'at_id',
		'm_uid', 'm_username',
		'drat_status', 'drat_created', 'drat_upatated', 'drat_deleted'
	);

	/**********************/

	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 99;

	/**********************/

	/**
	 * <p><strong style="color:blue">【D】获取带前缀的字段名</strong></p>
	 * @param string $field 无前缀的字段名
	 * @return string 带前缀的字段名
	 */
	public static function fieldname($field = '') {
		return self::$__prefix.$field;
	}

	/**
	 * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_field($shard_key = array()) {
		return (array)parent::_fetch_all_field(self::$__table, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键值获取单条数据</strong></p>
	 * @param number $value
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch($value, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table,
			"SELECT * FROM %t WHERE %i='%d' AND %i<'%d' LIMIT 1",
			array(self::$__table, self::$__pk, $value, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键ID值获取单条数据（不推荐使用此方法，请以fetch来替代）</strong></p>
	 * @param number $id
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return self::fetch($id, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据一组主键值来获取多条数据</strong></p>
	 * @param array $ids
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_ids($ids, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i DESC", array(
			self::$__table, db_help::field(self::$__pk, $ids), db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<'), self::$__pk
		), self::$__pk, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有数据</strong></p>
	 * @param number $start
	 * @param number $limit
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i<'%d' ORDER BY %i".db_help::limit($start, $limit),array(
			self::$__table, self::fieldname('status'), self::STATUS_REMOVE, self::$__pk
		), self::$__pk, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】列出指定条件的数据</strong></p>
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
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i DESC".db_help::limit($start, $limit), array(
			self::$__table, self::parse_conditions($conditions), db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<'), self::$__pk
		), self::$__pk, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】数据入库</strong></p>
	 * @param array $data
	 * @param boolean $return_insert_id
	 * @param boolean $replace
	 * @param boolean $silent
	 * @param array $shard_key
	 * @return Ambigous <mixed, boolean>
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_NORMAL;
		}

		if (empty($data[self::fieldname('created')])) {
			$data[self::fieldname('created')] = startup_env::get('timestamp');
		}

		if (empty($data[self::fieldname('updated')])) {
			$data[self::fieldname('updated')] = $data[self::fieldname('created')];
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键删除</strong></p>
	 * @param number|array $value
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function delete($value, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			self::fieldname('status') => self::STATUS_REMOVE,
			self::fieldname('deleted') => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $value), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键删除（不推荐此方法，请使用delete方法替代）</strong></p>
	 * @param number|array $ids
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			self::fieldname('status') => self::STATUS_REMOVE,
			self::fieldname('deleted') => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_conditions($conditions, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			self::fieldname('status') => self::STATUS_REMOVE,
			self::fieldname('deleted') => startup_env::get('timestamp')
		), self::parse_conditions($conditions), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键更新</strong></p>
	 * @param array $data
	 * @param number|array $value
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function update($data, $value, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}

		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, array(self::$__pk => $value), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件更新</strong></p>
	 * @param array $data
	 * @param array $conditions
	 * $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function update_by_conditions($data, $conditions, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}

		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, self::parse_conditions($conditions), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_all($shard_key = array()) {
		return (int) parent::_result_first(self::$__table,
				"SELECT COUNT(%i) FROM %t WHERE %i<'%d'",
				array(self::$__pk, self::$__table, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件计算所有未删除的记录总数</strong></p>
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i AND %i", array(
			self::$__table, self::$__pk, self::parse_conditions($conditions), db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据查询条件拼凑 sql 条件</strong></p>
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
		// 遍历条件
		foreach ($conditions as $field => $v) {
			if (!in_array($field, self::$__fields)) {
				// 非当前表字段，则忽略
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

	/**********************/

	/**
	 * 找到指定日报的所有相关文件附件
	 * @param number $p_id
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_by_dr_id($dr_id, $shard_key = array()) {
		$dr_id = (int)$dr_id;
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY `drat_id` DESC", array(
			self::$__table, db_help::field('dr_id', $dr_id), db_help::field('drat_status', self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
	}

}
