<?php
/**
 * voa_d_uc_common_cpmenu
 * 系统配置数据表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_uc_common_cpmenu extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.common_cpmenu';
	/** 主键 */
	private static $__pk = 'ccm_id';
	/** 字段前缀 */
	private static $__prefix = 'ccm_';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/**********************/

	/** 系统菜单 */
	const SYSTEM_MENU = 1;
	/** 插件应用菜单 */
	const CUSTOM_MENU = 0;

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
	 * @param boolean $force 是否强制已删除了的数据
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
	 * @param boolean $force 是否强制读取已删除数据
	 * @return array
	 */
	public static function fetch_all($start = 0, $limit = 0, $force = false, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i ".db_help::limit($start, $limit),array(
				self::$__table, ($force ? 1 : db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<'))
		), self::$__pk, $shard_key);
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
	 * 读取指定的系统或者插件菜单
	 * @param number $ccm_system 是否为系统菜单
	 * @param string $force 是否强制读取已删除了的数据
	 * @return array
	 */
	public static function fetch_all_ccm_system($ccm_system, $force = FALSE, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i", array(
			self::$__table, db_help::field('ccm_system', $ccm_system),
			($force ? 1 : db_help::field('ccm_status', self::STATUS_REMOVE, '<'))
		), self::$__pk, $shard_key);
	}

	/**
	 * 找到某个核心主模块的菜单
	 * @param string $ccm_module
	 * @param boolean $force
	 * @return array
	 */
	public static function fetch_by_ccm_module($ccm_module, $force = FALSE, $shard_key = array()) {
		return (array) parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i AND %i", array(
				self::$__table, db_help::field('ccm_module', $ccm_module), db_help::field('ccm_type', 'module'),
				($force ? 1 : db_help::field('ccm_status', self::STATUS_REMOVE, '<'))
			), $shard_key
		);
	}

	/**
	 * 找到指定菜单插件的菜单
	 * @param number $cp_pluginid
	 * @param boolean $force
	 */
	public static function fetch_all_by_cp_pluginid($cp_pluginid, $force = FALSE, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i", array(
			self::$__table, db_help::field('cp_pluginid', $cp_pluginid),
			($force ? 1 : db_help::field('ccm_status', self::STATUS_REMOVE, '<'))
		), self::$__pk, $shard_key);
	}

	/**
	 * 计算指定分类下的所有菜单总数
	 * @param string $ccm_module
	 * @return number
	 */
	public static function count_by_not_module($ccm_module, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`ccm_id`) FROM %t WHERE %i AND %i AND %i", array(
			self::$__table, db_help::field('ccm_module', $ccm_module),
			db_help::field('cp_pluginid', 0, '>'), db_help::field('ccm_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}
}
