<?php
/**
 * voa_d_uc_enterprise
 * UC/企业信息表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_uc_enterprise extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.enterprise';
	/** 主键 */
	private static $__pk = 'ep_id';
	/** 字段前缀 */
	private static $__prefix = 'ep_';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 站点已关闭 */
	const STATUS_CLOSED = 3;
	/** 企业站数据建立中（新企业注册时初始化，出现此值说明企业站数据尚未建立） */
	const STATUS_DB = 4;
	/** DNS写入中 （新企业注册，出现此值说明域名DNS正在写入中） */
	const STATUS_DNS = 5;
	/** 已删除 */
	const STATUS_REMOVE = 6;

	/**********************/

	/** 微信企业号 未开通 */
	const WXQY_CLOSE = 0;
	/** 微信企业号 已开通 */
	const WXQY_OPEN = 1;

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
	 * 查询指定的字段值是否有重复
	 * @param string $field
	 * @param string $value
	 * @param number $not_in_ep_id
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_by_field_not_in_ep_id($field, $value, $not_in_ep_id = 0, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i AND %i AND %i", array(
			$field, self::$__table, db_help::field($field, $value),
			!empty($not_in_ep_id) ? db_help::field('ep_id', $not_in_ep_id, '<>') : 1,
			db_help::field('ep_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 获取指定企业号的信息
	 * @param string $enumber
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_enumber($enumber, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('ep_enumber', $enumber), db_help::field('ep_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 计算某个uc用户m_id开通的企业数
	 * @param number $m_id
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_by_m_id($m_id = 0, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`m_id`) FROM %t WHERE %i AND %i", array(
			self::$__table, db_help::field('m_id', $m_id), db_help::field('ep_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}
}
