<?php
/**
 * 投票主题表
 * $Author$
 * $Id$
 */

class voa_d_oa_vote extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.vote';
	/** 主键 */
	private static $__pk = 'v_id';
	/** 申请中 */
	const STATUS_NORMAL = 1;
	/** 已通过(已批准) */
	const STATUS_APPROVE = 2;
	/** 审批不通过 */
	const STATUS_REFUSE = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 单选 */
	const IS_SINGLE = 0;
	/** 多选 */
	const IS_MULTI = 1;

	/** 关闭状态 */
	const IS_CLOSE = 0;
	/** 启用状态 */
	const IS_OPEN = 1;

	/** 只允许指定用户投票 */
	const FRIEND_ONLY = 1;
	/** 允许所有人投票 */
	const FRIEND_ALL = 0;

	/** 获取所有投票列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `v_status`<'%d'
			ORDER BY `v_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取投票主题 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `v_id`='%d' AND `v_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `v_id` IN (%n) AND `v_status`<'%d'
			ORDER BY `v_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取投票主题 */
	public static function fetch_by_uids($uids, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid` IN (%n) AND `v_status`<'%d'
			ORDER BY `v_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 读取未结束的 */
	public static function fetch_unclosed_by_uid($uids, $start = 0, $limit = 0, $shard_key = array()) {
		$uids = (array)$uids;
		$uids[] = 0;
		return parent::_fetch_all(self::$__table, "SELECT `b`.* FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`v_id`=`b`.`v_id`
			WHERE `a`.`m_uid` IN (%n) AND `b`.`v_endtime`>'%d' AND `b`.`v_status`<'%d'
			ORDER BY `b`.`v_endtime` ASC".db_help::limit($start, $limit), array(
				'oa.vote_permit_user', self::$__table, $uids, startup_env::get('timestamp'), self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 读取已结束的 */
	public static function fetch_fin_by_uid_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		$uids = (array)$uids;
		$uids[] = 0;
		return parent::_fetch_all(self::$__table, "SELECT `b`.* FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`v_id`=`b`.`v_id`
			WHERE `a`.`m_uid` IN (%n) AND `b`.`v_endtime`<'%d' AND `b`.`v_status`<'%d'
			ORDER BY `b`.`v_updated` DESC".db_help::limit($start, $limit), array(
				'oa.vote_permit_user', self::$__table, $uids, $updated, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 统计进行中的投票 */
	public static function count_running($shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE v_endtime>%d AND v_isopen=%d AND v_status<%d", array(
				self::$__table, startup_env::get('timestamp'),
				self::IS_OPEN, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 统计已结束的投票 */
	public static function count_fin($shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE v_endtime<%d OR v_isopen=%d AND v_status<%d", array(
				self::$__table, startup_env::get('timestamp'),
				self::IS_CLOSE, self::STATUS_REMOVE
			), $shard_key
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
		if (empty($data['v_status'])) {
			$data['v_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['v_created'])) {
			$data['v_created'] = startup_env::get('timestamp');
		}

		if (empty($data['v_updated'])) {
			$data['v_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['v_updated'])) {
			$data['v_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/** 更新投票人次 */
	public static function update_voters($v_id, $gule = '+', $num = 1, $shard_key = array()) {
		return parent::_query(self::$__table, "UPDATE %t SET `v_voters`=`v_voters`{$gule}{$num} WHERE `v_id`='%d'", array(
			self::$__table, $v_id
		), $shard_key);
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
			'v_status' => self::STATUS_REMOVE,
			'v_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据uid删除投票信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'v_status' => self::STATUS_REMOVE,
			'v_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 构造查询条件SQL语句
	 * @param array $conditions
	 * @return Ambigous <number, string>
	 */
	public static function search_by_conditions($conditions = array()) {
		$where = array();
		if (isset($conditions['m_uid']) && $conditions['m_uid']) {
			$where[] = db_help::field('m_uid', $conditions['m_uid']);
		}
		if (isset($conditions['v_begintime']) && $conditions['v_begintime'] > 0) {
			$where[] = db_help::field('v_begintime', $conditions['v_begintime'], '>=');
		}
		if (isset($conditions['v_endtime']) && $conditions['v_endtime'] > 0) {
			$where[] = db_help::field('v_endtime', $conditions['v_endtime'], '<=');
		}
		if (isset($conditions['v_subject']) && $conditions['v_subject']) {
			$where[] = db_help::field('v_subject', '%'.addcslashes($conditions['v_subject'], '%_').'%', 'like');
		}
		if (isset($conditions['v_status'])) {
			$where[] = db_help::field('v_status', $conditions['v_status']);
		}
		if (!isset($conditions['v_status'])) {
			$where[] = db_help::field('v_status', self::STATUS_REMOVE, '<');
		}
		return $where ? implode(' AND ', $where) : 1;
	}

	/**
	 * 计算指定条件的投票总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_all_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`v_id`) FROM %t WHERE %i", array(
			self::$__table, self::search_by_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的投票
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `v_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::search_by_conditions($conditions)
			), self::$__pk, $shard_key
		);
	}
}
