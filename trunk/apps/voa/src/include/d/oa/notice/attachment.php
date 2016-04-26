<?php
/**
 * voa_d_oa_notice_attachment
 * 通知公告附件表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_oa_notice_attachment extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.notice_attachment';
	/** 主键 */
	private static $__pk = 'ntat_id';
	/** 所有字段名 */
	private static $__fields = array(
		'ntat_id', 'm_uid', 'm_username', 'nt_id', 'at_id',
		'ntat_status', 'ntat_created', 'ntat_updated', 'ntat_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已删除 */
	const STATUS_REMOVE = 2;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ntat_status`<'%d'
			ORDER BY `ntat_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ntat_id`='%d' AND `ntat_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ntat_id` IN (%n) AND `ntat_status`<'%d'
			ORDER BY `ntat_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 uid 读取回复的信息
	 * @param int $uid 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return self::fetch_by_conditions(array('m_uid' => $uid), $start, $limit, $shard_key);
	}

	/**
	 * 根据 nt_id 读取附件信息
	 * @param mix $nt_id
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 */
	public static function fetch_by_nt_id($nt_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT a.nt_id, b.* FROM %t AS a
			LEFT JOIN %t AS b ON a.at_id=b.at_id
			WHERE a.nt_id IN (%n) AND a.ntat_status<%d
			ORDER BY a.at_id DESC", array(
				self::$__table, voa_d_oa_common_attachment::$__table, (array)$nt_id, self::STATUS_REMOVE
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND ntat_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的记录
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND ntat_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['ntat_status'])) {
			$data['ntat_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ntat_created'])) {
			$data['ntat_created'] = startup_env::get('timestamp');
		}

		if (empty($data['ntat_updated'])) {
			$data['ntat_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['ntat_updated'])) {
			$data['ntat_updated'] = startup_env::get('timestamp');
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
			'ntat_status' => self::STATUS_REMOVE,
			'ntat_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除指定条件的数据
	 * @param array $conditions
	 * @param array $shard_key
	 * @param boolean $unbuffered
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_conditions($conditions, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'ntat_status' => self::STATUS_REMOVE,
			'ntat_deleted' => startup_env::get('timestamp')
		), self::parse_conditions($conditions), $unbuffered, false, $shard_key);
	}

	/**
	 * 查询存在于公告附件表的公共附件at_id
	 * 返回以at_id为键名的数组
	 * @param number $nt_id
	 * @param array $at_id
	 * @param number $start
	 * @param number $limit
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_nt_id_at_id($nt_id, $at_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT `at_id` FROM %t WHERE %i AND %i AND %i", array(
			self::$__table, db_help::field('nt_id', $nt_id), db_help::field('at_id', $at_id), db_help::field('ntat_status', self::STATUS_REMOVE, '<')
		), 'at_id', $shard_key);
	}
}

