<?php
/**
 * voa_d_oa_common_selfmenu
 * 自定义菜单数据表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_selfmenu extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.common_selfmenu';
	/** 主键 */
	private static $__pk = 'csm_id';
	/** 字段前缀 */
	private static $__prefix = 'csm_';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/**********************/

	/** 最大允许添加的职务个数 */
	const COUNT_MAX = 50;

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
			"SELECT * FROM %t WHERE %i<'%d' ORDER BY %i".db_help::limit($start, $limit),
			array(self::$__table, self::fieldname('status'), self::STATUS_REMOVE, self::fieldname('displayorder')), self::$__pk, $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有指定主键值数据</strong></p>
	 * @author Deepseath
	 * @param int|array $value
	 * @param
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function fetch_all_by_key($value, $orderby = '', $sort = 'DESC', $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i %i", array(
				self::$__table,
				db_help::field(self::$__pk, $value),
				db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<'),
				db_help::order(($orderby ? $orderby : self::$__pk), $sort),
				db_help::limit($start, $limit)
			), self::$__pk, $shard_key
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
	 * 列出最近储存的菜单历史
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_new($limit = 1, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT `csm_id`,`csm_md5`,`csm_menu`,`csm_updated` FROM %t
			WHERE %i ORDER BY `csm_updated` DESC %i", array(
			self::$__table, db_help::field('csm_status', self::STATUS_REMOVE, '<'), db_help::limit($limit)
		), self::$__pk, $shard_key);
	}

	/**
	 * 找到某个md5值的数据
	 * @param string $md5
	 * @param string $unbuffered
	 * @return boolean
	 */
	public static function fetch_by_md5($md5, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('csm_md5', $md5), db_help::field('csm_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

}
