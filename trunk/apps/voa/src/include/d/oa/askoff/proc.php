<?php
/**
 * 请假回复信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_askoff_proc extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askoff_proc';
	/** 主键 */
	private static $__pk = 'aopc_id';
	/** 所有字段名 */
	private static $__fields = array(
		'aopc_id', 'm_uid', 'm_username', 'ao_id', 'aopc_remark',
		'aopc_status', 'aopc_created', 'aopc_updated', 'aopc_deleted'
	);
	/** 审批中 */
	const STATUS_NORMAL = 1;
	/** 已通过 */
	const STATUS_APPROVE = 2;
	/** 通过并转审批 */
	const STATUS_APPROVE_APPLY = 3;
	/** 审批不通过 */
	const STATUS_REFUSE = 4;
	/** 抄送 */
	const STATUS_CARBON_COPY = 5;
	/** 已删除 */
	const STATUS_REMOVE = 6;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `aopc_status`<'%d'
			ORDER BY `aopc_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `aopc_id`='%d' AND `aopc_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `aopc_id` IN (%n) AND `aopc_status`<'%d'
			ORDER BY `aopc_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 ao_id 读取回复的信息
	 * @param int $ao_id 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_ao_id($ao_id, $start = 0, $limit = 0, $shard_key = array()) {
		return self::fetch_by_conditions(array('ao_id' => $ao_id), $start, $limit, $shard_key);
	}

	/**
	 * 根据 uid, updated 读取数据请假主题信息
	 * @param int $uid
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @throws service_exception
	 */
	public static function list_by_uid($uid, $conditions, $start = 0, $limit = 0, $shard_key = array()) {
		$wheres = array();
		$params = array();

		$wheres[] = 'a.m_uid=%d';
		$params[] = $uid;

		if (!empty($conditions['updated'])) {
			$wheres[] = 'a.aopc_updated<%d';
			$params[] = $conditions['updated'];
		}

		/** 如果为空, 或未处理 */
		if (empty($conditions['status']) || 'doing' == $conditions['status']) {
			$wheres[] = 'a.aopc_status='.self::STATUS_NORMAL;
		} else {
			$wheres[] = 'a.aopc_status>'.self::STATUS_NORMAL.' AND a.aopc_status<'.self::STATUS_CARBON_COPY;
		}

		$wherestr = empty($wheres) ? '' : implode(' AND ', $wheres);
		array_unshift($params, self::$__table, voa_d_oa_askoff::$__table);

		$proc_field = 'a.aopc_updated, a.aopc_id as proc_id';
		return parent::_fetch_all(self::$__table, "SELECT b.*, {$proc_field} FROM %t AS a
			LEFT JOIN %t AS b ON a.ao_id=b.ao_id
			WHERE {$wherestr} AND a.m_uid!=b.m_uid ORDER BY a.aopc_updated DESC", $params, self::$__pk, $shard_key
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
		$where = is_array($conditions) ? self::parse_conditions($conditions) : $conditions;
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND aopc_status<%d", array(
			self::$__table, $where, self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 获取指定条件列表
	 * @param mixed $conditions	字符串或数组
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		$where = is_array($conditions) ? self::parse_conditions($conditions) : $conditions;
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND aopc_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, $where, self::STATUS_REMOVE, self::$__pk
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
		if (empty($data['aopc_status'])) {
			$data['aopc_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['aopc_created'])) {
			$data['aopc_created'] = startup_env::get('timestamp');
		}

		if (empty($data['aopc_updated'])) {
			$data['aopc_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['aopc_updated'])) {
			$data['aopc_updated'] = startup_env::get('timestamp');
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
			'aopc_status' => self::STATUS_REMOVE,
			'aopc_deleted' => startup_env::get('timestamp')
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
				'aopc_status' => self::STATUS_REMOVE,
				'aopc_deleted' => startup_env::get('timestamp')
		), self::parse_conditions($conditions), $unbuffered, false, $shard_key);
	}
}

