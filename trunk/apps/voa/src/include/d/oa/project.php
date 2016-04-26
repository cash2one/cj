<?php
/**
 * 项目表
 * $Author$
 * $Id$
 */

class voa_d_oa_project extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.project';
	/** 主键 */
	private static $__pk = 'p_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已完成 */
	const STATUS_COMPLETE = 3;
	/** 已关闭 */
	const STATUS_CLOSED = 4;
	/** 已删除 */
	const STATUS_REMOVE = 5;

	/** 计算所有总数 */
	public static function count_all($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE p_status<'%d'", array(
				self::$__table, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `p_status`<'%d'
			ORDER BY `p_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取项目列表 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `p_id`='%d' AND `p_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `p_id` IN (%n) AND `p_status`<'%d'
			ORDER BY `p_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取已经完成项目的列表 */
	public static function fetch_done_by_uids_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t AS `a`
			WHERE `a`.`m_uid` IN (%n) AND `a`.`p_status` IN (%n) AND `a`.`p_updated`<'%d'
			ORDER BY `a`.`p_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uids, array(self::STATUS_COMPLETE), $updated
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据uid计算已经完成的项目总数
	 * @param array $uids
	 * @param number $updated
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_done_by_uids_updated($uids, $updated, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`a`.`p_id`) FROM %t AS `a`
				WHERE `a`.`m_uid` IN (%n) AND `a`.`p_status` IN (%n) AND `a`.`p_updated`<'%d'", array(
			self::$__table, $uids, array(self::STATUS_COMPLETE), $updated
		), $shard_key);
	}

	/** 根据 uid 读取已关闭的项目列表 */
	public static function fetch_closed_by_uids_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`b`.*, `a`.`m_username` AS `pm_username`, `a`.`m_uid` AS `pm_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
			WHERE `a`.`m_uid` IN (%n) AND `b`.`p_status`='%d' AND `b`.`p_updated`<'%d'
			ORDER BY `a`.`pm_updated` DESC".db_help::limit($start, $limit), array(
				'oa.project_mem', self::$__table, $uids, self::STATUS_CLOSED, $updated
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据uid计算已关闭的项目数
	 * @param array $uids
	 * @param number $updated
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_closed_by_uids_updated($uids, $updated = 0, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`b`.`p_id`) FROM %t AS `a`
				LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
				WHERE `a`.`m_uid` IN (%n) AND `b`.`p_status`='%d' AND `b`.`p_updated`<'%d'", array(
			'oa.project_mem', self::$__table, $uids, self::STATUS_CLOSED, $updated
		), $shard_key);
	}

	/** 根据 uid 读取我参加的项目列表 */
	public static function fetch_my_by_uids_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`b`.*, `a`.`m_username` AS `pm_username`, `a`.`m_uid` AS `pm_uid`, `a`.`pm_updated` AS `pm_updated`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
			WHERE `a`.`m_uid` IN (%n) AND `a`.`pm_status`<'%d' AND `a`.`pm_updated`<'%d'
			AND `b`.`p_status`<'%d'
			ORDER BY `b`.`p_updated` DESC".db_help::limit($start, $limit), array(
				'oa.project_mem', self::$__table, $uids, voa_d_oa_project_mem::STATUS_QUIT, $updated,
				self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据uid计算我参加的项目数
	 * @param array $uids
	 * @param number $updated
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_my_by_uids_updated($uids, $updated, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`b`.`p_id`) FROM %t AS `a`
				LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
				WHERE `a`.`m_uid` IN (%n) AND `a`.`pm_status`<'%d' AND `a`.`pm_updated`<'%d' AND `b`.`p_status`<'%d'", array(
			'oa.project_mem', self::$__table, $uids, voa_d_oa_project_mem::STATUS_QUIT, $updated, self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 根据uid计算我参与的正在进行的项目
	 * @param array $uids
	 * @param number $updated
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_myactive_by_uids_updated($uids, $updated, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`b`.`p_id`) FROM %t AS `a`
				LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
				WHERE `a`.`m_uid` IN (%n) AND `a`.`pm_status` IN (%n) AND `a`.`pm_updated`<'%d' AND `b`.`p_status`<'%d'", array(
						'oa.project_mem', self::$__table, $uids, array(
							voa_d_oa_project_mem::STATUS_NORMAL, voa_d_oa_project_mem::STATUS_UPDATE, voa_d_oa_project_mem::STATUS_OUTOF, voa_d_oa_project_mem::STATUS_CC
						), $updated, self::STATUS_COMPLETE
					), $shard_key);
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
		if (empty($data['p_status'])) {
			$data['p_status'] = self::STATUS_DRAFT;
		}

		if (empty($data['p_created'])) {
			$data['p_created'] = startup_env::get('timestamp');
		}

		if (empty($data['p_updated'])) {
			$data['p_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['p_updated'])) {
			$data['p_updated'] = startup_env::get('timestamp');
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
			'p_status' => self::STATUS_REMOVE,
			'p_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据uid删除信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'p_status' => self::STATUS_REMOVE,
			'p_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 构造查询SQL语句
	 * @param array $conditions
	 * @return string
	 */
	public static function search_by_conditions($conditions) {
		$where = array();
		if (isset($conditions['m_uid']) && $conditions['m_uid']) {
			$where[] = db_help::field('m_uid', $conditions['m_uid']);
		}
		if (isset($conditions['p_subject']) && $conditions['p_subject']) {
			$where[] = db_help::field('p_subject', '%'.addcslashes($conditions['p_subject'], '%_').'%','like');
		}
		if (isset($conditions['p_begintime']) && $conditions['p_begintime'] > 0) {
			$where[] = db_help::field('p_begintime', $conditions['p_begintime'], '>=');
		}
		if (isset($conditions['p_endtime']) && $conditions['p_endtime'] > 0) {
			$where[] = db_help::field('p_endtime', $conditions['p_endtime'], '<=');
		}
		$where[] = db_help::field('p_status', self::STATUS_REMOVE, '<');
		return $where ? implode(' AND ', $where) : 1;
	}

	/**
	 * 计算指定条件的项目数量
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i", array(
			self::$__pk, self::$__table, self::search_by_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的项目
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `p_id` DESC".db_help::limit($start, $limit),array(
				self::$__table, self::search_by_conditions($conditions)
			), self::$__pk, $shard_key
		);
	}
}
