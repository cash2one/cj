<?php
/**
 * voa_d_cyadmin_recognition_bill
 * 畅移后台/识别报销单据
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_cyadmin_recognition_bill extends dao_mysql {
	/** 表名 */
	public static $__table = 'cyadmin.recognition_bill';
	/** 主键 */
	private static $__pk = 'rb_id';
	/** 字段前缀 */
	private static $__prefix = 'rb_';

	/**********************/

	/** 识别状态：未处理（等待处理） */
	const STATUS_WAIT = 0;
	/** 识别状态：已识别 */
	const STATUS_OVER = 1;
	/** 识别状态：图片不清晰 */
	const STATUS_NO_IMAGE = 2;
	/** 识别状态：非报销单据 */
	const STATUS_NO_TYPE = 3;
	/** 识别状态：识别有误 */
	const STATUS_ERROR = 4;
	/** 识别状态：已确认识别结果（已完成） */
	const STATUS_SUCCESS = 5;


	/** 分配给每次识别操作请求的数量 */
	const REQUEST_LIMIT = 2;
	/** 每条分配数据的过期时间 **/
	const REQUEST_EXPIRY_TIME = 300;
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
			"SELECT * FROM %t WHERE %i='%d' LIMIT 1",
			array(self::$__table, self::$__pk, $value), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param string|number $value 主键值
	 */
	public static function update($data, $value, $shard_key = array()) {
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
			"SELECT * FROM %t WHERE ".db_help::limit($start, $limit),
			array(self::$__table), self::$__pk, $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
	 * @author Deepseath
	 * @return number
	 */
	public static function count_all($shard_key = array()) {
		return (int) parent::_result_first(self::$__table,
			"SELECT COUNT(%i) FROM %t",
			array(self::$__pk, self::$__table), $shard_key
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
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_WAIT;
		}
		if (empty($data[self::fieldname('created')])) {
			$data[self::fieldname('created')] = startup_env::get('timestamp');
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
		return parent::_update(self::$__table, $data, $conditions, false, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
	 * @author Deepseath
	 * @param array $conditions 删除条件
	 * @return void
	 */
	public static function delete_by_conditions($conditions, $shard_key = array()) {
		return parent::_delete(self::$__table, $conditions, 0, false, $shard_key);
	}

	/**
	 * 根据条件计算总数
	 * @param  array  $conditions
	 *                            $conditions = array(
	 *                            'field1' => '查询条件', // 运算符为 =
	 *                            'field2' => array('查询条件', '查询运算符'),
	 *                            'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *                            ...
	 *                            );
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i ", array(
				self::$__table, self::parse_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的数据
	 * @param  array  $conditions
	 *                            $conditions = array(
	 *                            'field1' => '查询条件', // 运算符为 =
	 *                            'field2' => array('查询条件', '查询运算符'),
	 *                            'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *                            ...
	 *                            );
	 * @param  number $start
	 * @param  number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array(), $order = 'ASC', $forupdate = '') {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
				WHERE %i  ORDER BY %i $order".db_help::limit($start, $limit)." $forupdate", array(
						self::$__table, self::parse_conditions($conditions), self::$__pk
				), 0, $shard_key
		);
	}

	/**
	 * 根据查询条件拼凑 sql 条件
	 * @param array $conditions 查询条件
	 *                          $conditions = array(
	 *                          'field1' => '查询条件', // 运算符为 =
	 *                          'field2' => array('查询条件', '查询运算符'),
	 *                          'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *                          ...
	 *                          );
	 */
	public static function parse_conditions($conditions = array()) {
		$wheres = array();
		/** 遍历条件 */
		foreach ($conditions as $field => $v) {
			/** 非当前表字段 */
			/*
			 if (!in_array($field, self::$__fields)) {
			continue;
			}
			*/
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
	/**********************************************/

}
