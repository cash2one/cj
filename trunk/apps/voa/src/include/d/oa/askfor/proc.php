<?php
/**
 * 审批进度表
 * $Author$
 * $Id$
 */

class voa_d_oa_askfor_proc extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askfor_proc';
	/** 主键 */
	private static $__pk = 'afp_id';
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
	/** 已催办 */
	const STATUS_REMINDER = 6;
	/** 已撤销 */
	const STATUS_CANCEL = 7;
	/** 已删除 */
	const STATUS_REMOVE = 8;

	/** 获取所有审批进度列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afp_status`<'%d'
			ORDER BY `afp_id` ASC".db_help::limit($start, $limit), array(
			self::$__table, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取审批列表 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `afp_id`='%d' AND `afp_status`<'%d'
			ORDER BY `afp_id` ASC", array(
			self::$__table, $id, self::STATUS_REMOVE
		), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afp_id` IN (%n) AND `afp_status`<'%d'
			ORDER BY `afp_id` ASC", array(
			self::$__table, $ids, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/** 根据 af_id 读取进度列表 */
	public static function fetch_by_af_id($af_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `af_id`='%d' AND `afp_status`<'%d'
			ORDER BY `afp_id` DESC".db_help::limit($start, $limit), array(
			self::$__table, $af_id, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	public static function fetch_by_af_ids($af_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `af_id` IN (%n) AND `afp_status`<'%d'
			ORDER BY `afp_id` DESC".db_help::limit($start, $limit), array(
			self::$__table, $af_id, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据审批 id 和 uid 读取进度信息
	 * @param unknown_type $af_id
	 * @param unknown_type $uid
	 */
	public static function fetch_by_af_id_uid($af_id, $uid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `af_id`='%d' AND `m_uid`='%d' AND `afp_status`<'%d'", array(
			self::$__table, $af_id, $uid, self::STATUS_REMOVE
		), $shard_key
		);
	}

	public static function fetch_by_af_id_uids($af_id, $uids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `af_id`='%d' AND `m_uid` IN (%n) AND `afp_status`<'%d'", array(
			self::$__table, $af_id, $uids, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/** 统计等待我审批的事项 */
	public static function count_by_uid($uid, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `m_uid`='%d' AND `afp_status`='%d'", array(
			self::$__table, $uid, self::STATUS_NORMAL
		), $shard_key
		);
	}

	/**
	 * 新增审批进度信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['afp_status'])) {
			$data['afp_status'] = self::STATUS_CARBON_COPY;
		}

		if (empty($data['afp_created'])) {
			$data['afp_created'] = startup_env::get('timestamp');
		}

		if (empty($data['afp_updated'])) {
			$data['afp_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/**
	 * 批量新增信息
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 信息入库 */
		$sql_ats = array();
		foreach ($data as $_at) {
			$sql_ats[] = "(".implode(',', array(
					$_at['af_id'], '"'.$_at['m_uid'].'"','"'. $_at['m_username'].'"', $_at['afp_status'],
					startup_env::get('timestamp'), startup_env::get('timestamp')
				)).")";
		}

		if (empty($sql_ats)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(af_id, m_uid, m_username, afp_status, afp_created, afp_updated) VALUES".implode(',', $sql_ats), array(self::$__table), $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['afp_status'])) {
			$data['afp_status'] = self::STATUS_APPROVE;
		}

		if (empty($data['afp_updated'])) {
			$data['afp_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除审批信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'afp_status' => self::STATUS_REMOVE,
			'afp_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据uid删除审批信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'afp_status' => self::STATUS_REMOVE,
			'afp_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据审批ID删除审批信息
	 *
	 * @param int|array $af_ids 用户 $af_id 或 $af_id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_af_ids($af_ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'afp_status' => self::STATUS_REMOVE,
			'afp_deleted' => startup_env::get('timestamp')
		), db_help::field('af_id', $af_ids), $unbuffered, false, $shard_key);
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND afp_status<%d", array(
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
			WHERE %i AND afp_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
			self::$__table, $where, self::STATUS_REMOVE, self::$__pk
		), self::$__pk, $shard_key
		);
	}
}
