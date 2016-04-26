<?php
/**
 * 插件排序表
 * $Author$
 * $Id$
 */

class voa_d_oa_common_plugin_display extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.common_plugin_display';
	/** 主键 */
	private static $__pk = 'cpd_id';
	/** 所有字段名 */
	private static $__fields = array(
		'cpd_id', 'm_uid', 'cpd_isfav', 'cp_pluginid', 'cpd_display',
		'cpd_status', 'cpd_created', 'cpd_updated', 'cpd_deleted'
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
			WHERE `cpd_status`<'%d'
			ORDER BY `cpd_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `cpd_id`='%d' AND `cpd_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `cpd_id` IN (%n) AND `cpd_status`<'%d'
			ORDER BY `cpd_id` DESC", array(
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND cpd_status<%d", array(
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
			WHERE %i AND cpd_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['cpd_status'])) {
			$data['cpd_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['cpd_created'])) {
			$data['cpd_created'] = startup_env::get('timestamp');
		}

		if (empty($data['cpd_updated'])) {
			$data['cpd_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['cpd_updated'])) {
			$data['cpd_updated'] = startup_env::get('timestamp');
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
			'cpd_status' => self::STATUS_REMOVE,
			'cpd_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 获取用户修改过排序的插件
	 * @param int $uid
	 * @param int $pluginid
	 */
	public static function fetch_by_uid_pluginids($uid, $pluginids, $shard_key = array()) {
		$pluginids = (array)$pluginids;
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE m_uid=%d AND cp_pluginid IN (%n) AND cpd_status<%d", array(
				self::$__table, $uid, $pluginids, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 获取排序列表
	 * @param int $uid
	 * @throws service_exception
	 * @return Ambigous <void, boolean>
	 */
	public static function fetch_order_list($uid, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE m_uid=%d AND cpd_status<%d
			ORDER BY cpd_isfav DESC, cpd_ordernum DESC", array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 uid, pluginid 删除应用排序
	 * @param int $uid
	 * @param array $pluginids
	 * @param array $shard_key
	 * @return boolean
	 */
	public static function del_by_uid_pluginids($uid, $pluginids, $shard_key = array()) {
		$pluginids = (array)$pluginids;
		$cons = array(
			db_help::field('m_uid', $uid),
			db_help::field('cp_pluginid', $pluginids)
		);
		return parent::_delete(self::$__table, implode(' AND ', $cons), 0, false, $shard_key);
	}

	/**
	 * 应用排序信息入库
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 打分信息入库 */
		$sql_pds = array();
		foreach ($data as $_pd) {
			$sql_pds[] = "(".implode(',', array(
				db_help::quote($_pd['m_uid']),
				db_help::quote($_pd['cpd_isfav']),
				db_help::quote($_pd['cp_pluginid']),
				db_help::quote($_pd['cpd_ordernum']),
				empty($_pd['cpd_status']) ? self::STATUS_NORMAL : $_pd['cpd_status'],
				startup_env::get('timestamp'),
				startup_env::get('timestamp')
			)).")";
		}

		if (empty($sql_pds)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(m_uid, cpd_isfav, cp_pluginid, cpd_ordernum, cpd_status, cpd_created, cpd_updated) VALUES".implode(',', $sql_pds), array(self::$__table), $shard_key);
	}
}

