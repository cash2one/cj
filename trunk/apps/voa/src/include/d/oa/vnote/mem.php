<?php
/**
 * 可查看备忘人员信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_vnote_mem extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.vnote_mem';
	/** 主键 */
	private static $__pk = 'vnm_id';
	/** 所有字段名 */
	private static $__fields = array(
		'vnm_id', 'm_uid', 'm_username', 'vn_id',
		'vnm_status', 'vnm_created', 'vnm_updated', 'vnm_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CC = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vnm_status`<'%d'
			ORDER BY `vnm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `vnm_id`='%d' AND `vnm_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vnm_id` IN (%n) AND `vnm_status`<'%d'
			ORDER BY `vnm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 vn_id 读取数据
	 * @param int $vn_id 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_vn_id($vn_id, $start = 0, $limit = 0, $shard_key = array()) {
		return self::fetch_by_conditions(array('vn_id' => $vn_id), $start, $limit, $shard_key);
	}

	/**
	 * 根据 vn_id, uid 读取数据
	 * @param int $vn_id 会议纪要id
	 * @param int $uid 用户uid
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_vn_id_uid($vn_id, $uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `vn_id`=%d AND m_uid=%d AND `vnm_status`<'%d'
			ORDER BY `vnm_id` DESC", array(
				self::$__table, $vn_id, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 根据uid查询其有权限查看的备忘列表
	 * @param int $uid 用户uid
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		$uid = intval($uid);
		return self::fetch_by_conditions(array(
			'm_uid' => array(array(0, $uid), 'in')
		), $start, $limit, $shard_key);
	}

	/**
	 * 根据条件搜索
	 * @param int $uid
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @param array $shard_key
	 */
	public static function fetch_by_search($uid, $conditions, $start = 0, $limit = 0, $shard_key = array()) {
		$wheres = array();
		$params = array();

		if (!empty($conditions['vn_created'])) {
			$wheres[] = 'b.vn_created>%d AND b.vn_created<%d';
			$params[] = $conditions['vn_created'];
			$params[] = $conditions['vn_created'] + 86400;
		} elseif (!empty($conditions['username'])) {
			$wheres[] = 'b.m_username LIKE(%s)';
			$params[] = $conditions['username'];
		}

		if (!empty($conditions['updated'])) {
			$wheres[] = 'b.vn_updated<%d';
			$params[] = $conditions['updated'];
		}

		$wheres[] = 'a.m_uid=%d';
		$params[] = $uid;

		$wherestr = empty($wheres) ? '' : implode(' AND ', $wheres);
		array_unshift($params, self::$__table, voa_d_oa_vnote::$__table);

		return self::_fetch_all(self::$__table, "SELECT a.* FROM %t AS a
			LEFT JOIN %t AS b ON a.vn_id=b.vn_id
			WHERE {$wherestr} ORDER BY a.vn_id DESC LIMIT $start, $limit", $params, self::$__pk, $shard_key
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND vnm_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的投票
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND vnm_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增投票信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['vnm_status'])) {
			$data['vnm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['vnm_created'])) {
			$data['vnm_created'] = startup_env::get('timestamp');
		}

		if (empty($data['vnm_updated'])) {
			$data['vnm_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['vnm_updated'])) {
			$data['vnm_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除投票信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'vnm_status' => self::STATUS_REMOVE,
			'vnm_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除指定vn_id（单个或多个）的日报相关参与人员
	 * @param number|array $vn_ids
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_vn_ids($vn_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'vnm_status' => self::STATUS_REMOVE,
			'vnm_deleted' => startup_env::get('timestamp')
		), db_help::field('vn_id', $vn_ids), $unbuffered, $low_priority, $shard_key);
	}
}

