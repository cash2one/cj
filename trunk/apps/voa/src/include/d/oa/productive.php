<?php
/**
 * 活动/产品主题表
 * $Author$
 * $Id$
 */

class voa_d_oa_productive extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.productive';
	/** 主键 */
	private static $__pk = 'pt_id';
	/** 所有字段名 */
	private static $__fields = array(
		'pt_id', 'm_uid', 'm_username', 'pt_lng', 'pt_lat', 'csp_id',
		'pt_note', 'pt_status', 'pt_created', 'pt_updated', 'pt_deleted'
	);
	/** 待巡 */
	const STATUS_WAITING = 1;
	/** 进行中 */
	const STATUS_DOING = 2;
	/** 已巡 */
	const STATUS_DONE = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 待巡 */
	const STATUS_WAITING_TEXT = '待巡';
	/** 进行中 */
	const STATUS_DOING_TEXT = '进行中';
	/** 已巡 */
	const STATUS_DONE_TEXT = '已巡' ;
	/** 已删除 */
	const STATUS_REMOVE_TEXT = '已删除';


	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `pt_status`<'%d'
			ORDER BY `pt_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `pt_id`='%d' AND `pt_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `pt_id` IN (%n) AND `pt_status`<'%d'
			ORDER BY `pt_id` DESC", array(
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
	public static function count_by_conditions($conditions, $start_date = 0, $end_date = 0, $shard_key = array()) {
		$date_condi = array();
		if ($start_date > 0) {
			$date_condi[] = " pt_updated > $start_date ";
		}
		if ($end_date > 0) {
			$date_condi[] = " pt_updated < $end_date ";
		}
		$date_condi = implode(' AND ', $date_condi);

		if ($date_condi) {
			$date_condi = ' AND '.$date_condi;
		}

		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND pt_status<%d $date_condi ", array(
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
	public static function fetch_by_conditions($conditions,  $start_date = 0, $end_date = 0, $start = 0, $limit = 0, $shard_key = array()) {
		$date_condi = array();
		if ($start_date > 0) {
			$date_condi[] = " pt_updated > $start_date ";
		}
		if ($end_date > 0) {
			$date_condi[] = " pt_updated < $end_date ";
		}
		$date_condi = implode(' AND ', $date_condi);
		if ($date_condi) {
			$date_condi = ' AND '.$date_condi;
		}
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND pt_status<%d $date_condi ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['pt_status'])) {
			$data['pt_status'] = self::STATUS_WAITING;
		}

		if (empty($data['pt_created'])) {
			$data['pt_created'] = startup_env::get('timestamp');
		}

		if (empty($data['pt_updated'])) {
			$data['pt_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['pt_updated'])) {
			$data['pt_updated'] = startup_env::get('timestamp');
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
			'pt_status' => self::STATUS_REMOVE,
			'pt_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据 csp_id 读取活动/产品信息
	 * @param int $csp_id 门店id
	 * @param array $sts 状态数组
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 */
	public static function fetch_by_csp_id_status($csp_id, $sts = array(), $start = 0, $limit = 0, $shard_key = array()) {
		$params = array(self::$__table, $csp_id);
		$wheres = array('csp_id=%d');
		if (0 < $sts) {
			$params[] = $sts;
			$wheres[] = 'pt_status IN (%n)';
		}

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE ".implode(' AND ', $wheres)."
			ORDER BY pt_id DESC ".db_help::limit($start, $limit), $params, self::$__pk, $shard_key
		);
	}

	/**
	 * 根据uid读取活动/产品信息
	 * @param int $uid 用户UID
	 * @param int $status 记录状态
	 * @param number $start
	 * @param number $limit
	 * @param array $shard_key
	 * @return Ambigous <void, boolean>
	 */
	public static function list_by_uid($uid, $status, $start = 0, $limit = 0, $shard_key = array()) {
		$status = (array)$status;
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE m_uid IN (%n) AND pt_status IN (%n) ORDER BY pt_created DESC ".db_help::limit($start, $limit), array(
				self::$__table, $uid, $status
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 打分信息入库
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 打分信息入库 */
		$sql_ins = array();
		foreach ($data as $_score) {
			$sql_ins[] = "(".implode(',', array(
				db_help::quote($_score['it_id']),
				db_help::quote($_score['sponsor_uid']),
				db_help::quote($_score['m_uid']),
				db_help::quote($_score['m_username']),
				db_help::quote($_score['csp_id']),
				empty($_score['pt_status']) ? self::STATUS_WAITING : $_score['pt_status'],
				startup_env::get('timestamp'),
				startup_env::get('timestamp')
			)).")";
		}

		if (empty($sql_ins)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(it_id, sponsor_uid, m_uid, m_username, csp_id, pt_status, pt_created, pt_updated) VALUES".implode(',', $sql_ins), array(self::$__table), $shard_key);
	}
}

