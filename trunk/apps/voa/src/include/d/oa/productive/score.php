<?php
/**
 * 活动/产品打分信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_productive_score extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.productive_score';
	/** 主键 */
	private static $__pk = 'ptsr_id';
	/** 所有字段名 */
	private static $__fields = array(
		'ptsr_id', 'm_uid', 'pt_id', 'pti_id', 'ptsr_score', 'ptsr_date', 'ptsr_type',
		'ptsr_status', 'ptsr_created', 'ptsr_updated', 'ptsr_deleted'
	);
	/** 待评 */
	const STATUS_DOING = 1;
	/** 已评 */
	const STATUS_DONE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 日期 */
	const TYPE_DATE = 1;
	/** 周 */
	const TYPE_WEEK = 2;
	/** 月 */
	const TYPE_MONTH = 3;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ptsr_status`<'%d'
			ORDER BY `ptsr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ptsr_id`='%d' AND `ptsr_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ptsr_id` IN (%n) AND `ptsr_status`<'%d'
			ORDER BY `ptsr_id` DESC", array(
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND ptsr_status<%d", array(
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
			WHERE %i AND ptsr_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
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
		if (empty($data['ptsr_status'])) {
			$data['ptsr_status'] = self::STATUS_DOING;
		}

		if (empty($data['ptsr_created'])) {
			$data['ptsr_created'] = startup_env::get('timestamp');
		}

		if (empty($data['ptsr_updated'])) {
			$data['ptsr_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['ptsr_updated'])) {
			$data['ptsr_updated'] = startup_env::get('timestamp');
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
			'ptsr_status' => self::STATUS_REMOVE,
			'ptsr_deleted' => startup_env::get('timestamp')
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
			'ptsr_status' => self::STATUS_REMOVE,
			'ptsr_deleted' => startup_env::get('timestamp')
		), self::parse_conditions($conditions), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据 pt_id 读取打分详情的信息
	 * @param int $pt_id 活动/产品id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_pt_id($pt_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE pt_id=%d AND ptsr_status<%d", array(
				self::$__table, $pt_id, self::STATUS_REMOVE
			), 'pti_id', $shard_key
		);
	}

	/**
	 * 根据 pt_id, pti_id 读取打分详情的信息
	 * @param int $pt_id 活动/产品id
	 * @param number $start
	 * @param number $limit
	 * @return Ambigous <multitype:, array>
	 */
	public static function fetch_by_pt_id_pti_id($pt_id, $pti_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE pt_id=%d AND pti_id=%d AND ptsr_status<%d", array(
				self::$__table, $pt_id, $pti_id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 打分信息入库
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 打分信息入库 */
		$sql_scores = array();
		foreach ($data as $_score) {
			$sql_scores[] = "(".implode(',', array(
				db_help::quote($_score['m_uid']),
				db_help::quote($_score['cr_id']),
				db_help::quote($_score['csp_id']),
				db_help::quote($_score['pt_id']),
				db_help::quote($_score['pti_id']),
				db_help::quote($_score['ptsr_score']),
				db_help::quote($_score['ptsr_date']),
				db_help::quote($_score['ptsr_type']),
				empty($_score['ptsr_status']) ? self::STATUS_DOING : $_score['ptsr_status'],
				startup_env::get('timestamp'),
				startup_env::get('timestamp')
			)).")";
		}

		if (empty($sql_scores)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(m_uid, cr_id, csp_id, pt_id, pti_id, ptsr_score, ptsr_date, ptsr_type, ptsr_status, ptsr_created, ptsr_updated) VALUES".implode(',', $sql_scores), array(self::$__table), $shard_key);
	}

	/**
	 * 获取排行列表信息
	 * @param array $cr_ids
	 * @param int $pti_id 打分项id, 为 0 时, 为总分排行
	 * @param number $start
	 * @param number $limit
	 * @param unknown $shard_key
	 */
	public static function list_rank_by_cr_id_pti_id_score($pti_id, $cr_ids = array(), $ymd = '', $uids = array(), $start = 0, $limit = 0, $shard_key = array()) {

		$wheres = array('a.pti_id=%d AND a.ptsr_date=%d');
		$params = array(self::$__table, voa_d_oa_productive_mem::$__table, $pti_id, $ymd);
		if (!empty($cr_ids)) {
			$wheres[] = 'a.cr_id IN (%n)';
			$params[] = $cr_ids;
		}

		if (!empty($uids)) {
			$wheres[] = 'b.m_uid IN (%n)';
			$params[] = $uids;
		}

		$wheres[] = 'a.ptsr_status<%d AND b.ptm_status<%d';
		$params[] = self::STATUS_REMOVE;
		$params[] = voa_d_oa_productive_mem::STATUS_REMOVE;

		$wherestr = implode(' AND ', $wheres);

		return parent::_fetch_all(self::$__table, "SELECT a.* FROM %t AS a
			LEFT JOIN %t AS b ON a.pt_id=b.pt_id
			WHERE {$wherestr}
			ORDER BY a.ptsr_score DESC ".db_help::limit($start, $limit), $params, self::$__pk, $shard_key
		);
	}
}
