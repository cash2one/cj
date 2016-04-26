<?php
/**
 * voa_d_uc_dbhost
 * UC/数据库主机表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_uc_dbhost extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.dbhost';
	/** 主键 */
	private static $__pk = 'db_id';
	/** 字段前缀 */
	private static $__prefix = 'db_';
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
	 * 找到一台未满载的主机
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_maximum($shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE `db_count`<`db_maximum` ORDER BY `db_count` DESC LIMIT 1", array(
				self::$__table
		), $shard_key);
	}

	/**
	 * 找到一台负载最小的主机
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_minimum($shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t ORDER BY `db_count` ASC LIMIT 1", array(self::$__table), $shard_key);
	}

	/**
	 * 为指定主机增加一台负载计数
	 * @param number $db_id
	 * @param number $unit
	 * @param array $shard_key
	 * @return boolean
	 */
	public static function count_add_by_db_id($db_id, $unit = 1, $shard_key = array()) {
		return parent::_incr(self::$__table, 'db_count', db_help::field('db_id', $db_id), array(), $unit);
	}

}
