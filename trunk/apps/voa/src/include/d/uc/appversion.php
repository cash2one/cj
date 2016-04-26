<?php
/**
 * UC/APP版本信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_uc_appversion extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.appversion';
	/** 主键 */
	private static $__pk = 'ver_id';
	/** 字段前缀 */
	private static $__prefix = 'ver_';

	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已删除 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** forceupdate字段，是否标记强制更新客户端：是 */
	const FORCEUPDATE_YES = 1;
	/** forceupdate字段，是否标记强制更新客户端：否 */
	const FORCEUPDATE_NO = 0;

	/** 客户端类型：IOS */
	const CLIENT_TYPE_IOS = 'ios';
	/** 客户端类型：android */
	const CLIENT_TYPE_ANDROID = 'android';
	/** 客户端类型：windows phone */
	const CLIENT_TYPE_WINPHONE = 'windows phone';

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
	 * 获取指定客户端的最新版本信息
	 * @param string $clienttype
	 * @param array $shard_key
	 * @return array
	 */
	public static function last_by_clienttype($clienttype, $shard_key = array()) {
		$last = (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY `ver_date` DESC, `ver_id` DESC LIMIT 1", array(
			self::$__table, db_help::field('ver_clienttype', $clienttype), db_help::field('ver_status', self::STATUS_REMOVE, '<')
		), $shard_key);
		if (empty($last)) {
			$last = (array) self::fetch_all_field($shard_key);
			$last['ver_clienttype'] = $clienttype;
		}

		return $last;
	}

	/**
	 * 计算指定客户端的更新历史数
	 * @param string $clienttype
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_all_by_clienttype($clienttype, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i AND %i", array(
			self::$__pk, self::$__table, db_help::field('ver_clienttype', $clienttype), db_help::field('ver_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 获取指定客户端的更新列表
	 * @param string $clienttype
	 * @param number $start
	 * @param number $limit
	 * @param string $order
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_by_clienttype($clienttype, $start, $limit, $order, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i, %i %i", array(
			self::$__table, db_help::field('ver_clienttype', $clienttype), db_help::field('ver_status', self::STATUS_REMOVE, '<'),
			db_help::order('ver_date', $order), db_help::field('ver_id', $order),
			db_help::limit($start, $limit)
		), self::$__pk, $shard_key);
	}

}
