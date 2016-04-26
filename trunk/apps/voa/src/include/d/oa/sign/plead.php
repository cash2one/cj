<?php
/**
 * 签到申诉表
 * $Author$
 * $Id$
 */

class voa_d_oa_sign_plead extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.sign_plead';
	/** 主键 */
	private static $__pk = 'sp_id';
	/** 未处理 */
	const STATUS_UN_DONE = 1;
	/** 已处理 */
	const STATUS_DONE = 2;
	/** 删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有签到申诉列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `sp_status`<'%d'
			ORDER BY `sp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取签到申诉信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `sp_id`='%d' AND `sp_status`<'%d'
			ORDER BY `sp_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `sp_id` IN (%n) AND `sp_status`<'%d'
			ORDER BY `sp_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取签到申诉信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `sp_status`<'%d'
			ORDER BY `sp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增签到信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['sp_status'])) {
			$data['sp_status'] = self::STATUS_UN_DONE;
		}

		if (empty($data['sp_created'])) {
			$data['sp_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['sp_updated'])) {
			$data['sp_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据UID删除签到信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'sp_status' => self::STATUS_REMOVE,
			'sp_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据id删除签到信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'sp_status' => self::STATUS_REMOVE,
			'sp_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
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

		if (isset($conditions['sp_year']) && $conditions['sp_year'] > 0) {
			$where[] = db_help::field('sp_year', $conditions['sp_year']);
		}

		if (isset($conditions['sp_month']) && $conditions['sp_month'] > 0) {
			$where[] = db_help::field('sp_month', $conditions['sp_month']);
		}

		if (isset($conditions['sp_message'])) {
			$where[] = db_help::field('sp_message', '%'.addcslashes($conditions['sp_message'], '%_').'%', 'like');
		}

		if (isset($conditions['sp_status'])) {
			$where[] = db_help::field('sp_status', $conditions['sp_status']);
		} else{
			$where[] = db_help::field('sp_status', self::STATUS_REMOVE, '<');
		}

		return $where ? implode(' AND ', $where) : 1;
	}

	/**
	 * 计算指定条件的申诉总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_all_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`%i`) FROM %t WHERE %i", array(
			self::$__pk, self::$__table, self::search_by_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的申诉记录
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `%i` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::search_by_conditions($conditions), self::$__pk
			), self::$__pk, $shard_key
		);
	}
}
