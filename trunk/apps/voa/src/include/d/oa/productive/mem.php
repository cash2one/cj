<?php
/**
 * 可查看活动/产品信息人员信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_productive_mem extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.productive_mem';
	/** 主键 */
	private static $__pk = 'ptm_id';
	/** 所有字段名 */
	private static $__fields = array(
		'ptm_id', 'm_uid', 'm_username', 'pt_id',
		'ptm_status', 'ptm_created', 'ptm_updated', 'ptm_deleted'
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
			WHERE `ptm_status`<'%d'
			ORDER BY `ptm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ptm_id`='%d' AND `ptm_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ptm_id` IN (%n) AND `ptm_status`<'%d'
			ORDER BY `ptm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 pt_id 读取数据
	 * @param int $pt_id 会议纪要id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_pt_id($pt_id, $start = 0, $limit = 0, $shard_key = array()) {
		return self::fetch_by_conditions(array('pt_id' => $pt_id), $start, $limit, $shard_key);
	}

	/**
	 * 根据 pt_id, uid 读取数据
	 * @param int $pt_id 会议纪要id
	 * @param int $uid 用户uid
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_pt_id_uid($pt_id, $uid, $start = 0, $limit = 0) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `pt_id`=%d AND m_uid=%d AND `ptm_status`<'%d'
			ORDER BY `ptm_id` DESC", array(
				self::$__table, $pt_id, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 根据uid查询其有权限查看的活动/产品信息列表
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

		if (!empty($conditions['pt_created'])) {
			$wheres[] = 'b.pt_created>%d AND b.pt_created<%d';
			$params[] = $conditions['pt_created'];
			$params[] = $conditions['pt_created'] + 86400;
		} elseif (!empty($conditions['username'])) {
			$wheres[] = 'b.m_username LIKE(%s)';
			$params[] = $conditions['username'];
		}

		if (!empty($conditions['updated'])) {
			$wheres[] = 'b.pt_updated<%d';
			$params[] = $conditions['updated'];
		}

		$wheres[] = 'a.m_uid=%d';
		$params[] = $uid;

		$wherestr = empty($wheres) ? '' : implode(' AND ', $wheres);
		array_unshift($params, self::$__table, voa_d_oa_productive::$__table);

		return self::_fetch_all(self::$__table, "SELECT a.* FROM %t AS a
			LEFT JOIN %t AS b ON a.pt_id=b.pt_id
			WHERE {$wherestr} ORDER BY a.pt_id DESC", $params, self::$__pk, $shard_key
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND ptm_status<%d", array(
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
			WHERE %i AND ptm_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['ptm_status'])) {
			$data['ptm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ptm_created'])) {
			$data['ptm_created'] = startup_env::get('timestamp');
		}

		if (empty($data['ptm_updated'])) {
			$data['ptm_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['ptm_updated'])) {
			$data['ptm_updated'] = startup_env::get('timestamp');
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
			'ptm_status' => self::STATUS_REMOVE,
			'ptm_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除指定pt_id（单个或多个）的日报相关参与人员
	 * @param number|array $pt_ids
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_pt_ids($pt_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'ptm_status' => self::STATUS_REMOVE,
			'ptm_deleted' => startup_env::get('timestamp')
		), db_help::field('pt_id', $pt_ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 用户信息入库
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 信息入库 */
		$sql_mems = array();
		foreach ($data as $_mem) {
			$sql_mems[] = "(".implode(',', array(
				db_help::quote($_mem['pt_id']),
				db_help::quote($_mem['m_uid']),
				db_help::quote($_mem['m_username']),
				db_help::quote($_mem['ptm_status']),
				startup_env::get('timestamp'),
				startup_env::get('timestamp')
			)).")";
		}

		if (empty($sql_mems)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(pt_id, m_uid, m_username, ptm_status, ptm_created, ptm_updated) VALUES".implode(',', $sql_mems), array(self::$__table), $shard_key);
	}

	/**
	 * 通过 uid 获取已接收的活动/产品信息
	 * @param int $uid
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function list_recv_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT b.* FROM %t AS a
			LEFT JOIN %t AS b ON a.pt_id=b.pt_id
			WHERE a.m_uid=%d AND a.m_uid!=b.m_uid AND b.pt_status=%d
			ORDER BY ptm_id DESC ".db_help::limit($start, $limit), array(
			 self::$__table, voa_d_oa_productive::$__table, $uid, voa_d_oa_productive::STATUS_DONE
		), self::$__pk, $shard_key);
	}
}

