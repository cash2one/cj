<?php
/**
 * 可查看日程人员信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_plan_mem extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.plan_mem';
	public static $__main = 'oa.plan';
	/** 主键 */
	private static $__pk = 'plm_id';
	/** 所有字段名 */
	private static $__fields = array(
		'plm_id', 'm_uid', 'm_username', 'pl_id',
		'plm_status', 'plm_created', 'plm_updated', 'plm_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public static function fetch_shares_to_me($my_uid, $shard_key) {
		return parent::_fetch_all(
			self::$__table,
			"SELECT *, b.m_username AS username
			 FROM %t AS a
			 LEFT JOIN %t AS b
			 ON a.`pl_id` = b.`pl_id`
			 WHERE b.`m_uid` != '%d'
			 AND a.`plm_status`<'%d'",
			array(self::$__table, self::$__main, $my_uid, self::STATUS_REMOVE),
			self::$__pk,
			$shard_key
		);
	}

	public static function fetch_shares_detail($pl_id, $my_uid, $shard_key) {
		return parent::_fetch_first(
			self::$__table,
			"SELECT * FROM %t AS a
			 LEFT JOIN %t AS b
			 ON a.`pl_id` = b.`pl_id`
			 WHERE b.`pl_id` = '%d'
			 AND b.`m_uid` != '%d'
			 AND a.`m_uid` = '%d'
			 AND a.`plm_status`<'%d'",
			array(self::$__table, self::$__main, $pl_id, $my_uid, $my_uid, self::STATUS_REMOVE),
			$shard_key
		);
	}

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `plm_status`<'%d'
			ORDER BY `plm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			),
			self::$__pk,
			$shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `plm_id`='%d' AND `plm_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `plm_id` IN (%n) AND `plm_status`<'%d'
			ORDER BY `plm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			),
			self::$__pk,
			$shard_key
		);
	}

	/**
	 * 根据 pl_id 读取数据
	 * @param  int      $pl_id 会议纪要id
	 * @param  number   $start
	 * @param  number   $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_pl_id($pl_id, $start = 0, $limit = 0, $shard_key = array()) {
		return self::fetch_by_conditions(array('pl_id' => $pl_id), $start, $limit, $shard_key);
	}

	/**
	 * 根据uid查询其有权限查看的日程列表
	 * @param  int      $uid   用户uid
	 * @param  number   $start
	 * @param  number   $limit
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
	 * @param int    $uid
	 * @param array  $conditions
	 * @param number $start
	 * @param number $limit
	 * @param array  $shard_key
	 */
	public static function fetch_by_search($uid, $conditions, $start = 0, $limit = 0, $shard_key = array()) {
		$wheres = array();
		$params = array();
		if ('mine' == $conditions['ac']) {
			$wheres[] = 'a.m_uid=b.m_uid';
		} elseif ('recv' == $conditions['ac']) {
			$wheres[] = 'a.m_uid!=b.m_uid';
		}

		if (!empty($conditions['reporttime'])) {
			$wheres[] = 'b.pl_reporttime=%d';
			$params[] = $conditions['reporttime'];
		} elseif (!empty($conditions['username'])) {
			$wheres[] = 'b.m_username LIKE(%s)';
			$params[] = $conditions['username'];
		}

		if (!empty($conditions['updated'])) {
			$wheres[] = 'b.pl_updated<%d';
			$params[] = $conditions['updated'];
		}

		$wheres[] = 'a.m_uid=%d';
		$params[] = $uid;

		$wherestr = empty($wheres) ? '' : implode(' AND ', $wheres);
		array_unshift($params, self::$__table, voa_d_oa_plan::$__table);

		return self::_fetch_all(self::$__table, "SELECT a.* FROM %t AS a
			LEFT JOIN %t AS b ON a.pl_id=b.pl_id
			WHERE {$wherestr} ORDER BY a.pl_id DESC",
			$params,
			self::$__pk,
			$shard_key
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
	 * @param  array  $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND plm_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的投票
	 * @param  array  $conditions
	 * @param  number $start
	 * @param  number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND plm_status<%d ORDER BY %i DESC".db_help::limit($start, $limit),array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			),
			self::$__pk,
			$shard_key
		);
	}

	/**
	 * 新增投票信息
	 *
	 * @param  array       $data             数据数组, 下标为字段名, 值为对应的信息
	 * @param  boolean     $return_insert_id 是否返回自增ID
	 * @param  boolean     $replace          是否使用 replace into
	 * @param  boolean     $silent           忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['plm_status'])) {
			$data['plm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['plm_created'])) {
			$data['plm_created'] = startup_env::get('timestamp');
		}

		if (empty($data['plm_updated'])) {
			$data['plm_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['plm_updated'])) {
			$data['plm_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除投票信息
	 *
	 * @param  int|array $ids        ID或ID数组
	 * @param  boolean   $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'plm_status' => self::STATUS_REMOVE,
			'plm_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除指定pl_id（单个或多个）的日报相关参与人员
	 * @param  number|array $pl_ids
	 * @param  boolean      $unbuffered
	 * @param  boolean      $low_priority
	 * @param  array        $shard_key
	 * @return Ambigous     <void, boolean>
	 */
	public static function delete_by_pl_ids($pl_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'plm_status' => self::STATUS_REMOVE,
			'plm_deleted' => startup_env::get('timestamp')
		), db_help::field('pl_id', $pl_ids), $unbuffered, $low_priority, $shard_key);
	}
}
