<?php
/**
 * 项目进度表
 * $Author$
 * $Id$
 */

class voa_d_oa_project_proc extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.project_proc';
	/** 主键 */
	private static $__pk = 'pp_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有进度列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `pp_status`<'%d'
			ORDER BY `pp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取进度信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `pp_id`='%d' AND `pp_status`<'%d'
			ORDER BY `pp_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `pp_id` IN (%n) AND `pp_status`<'%d'
			ORDER BY `pp_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据会议 p_id 读取进度信息 */
	public static function fetch_by_p_id($p_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `p_id`='%d' AND `pp_status`<'%d'
			ORDER BY `pp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $p_id, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取进度信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `pp_status`<'%d'
			ORDER BY `pp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据会议 id 和 uid 读取进度信息 */
	public static function fetch_by_p_id_uid($p_id, $uid, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `p_id`='%d' AND `m_uid`='%d' AND `pp_status`<'%d'", array(
				self::$__table, $p_id, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增项目进度信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的用户信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['pp_status'])) {
			$data['pp_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['pp_created'])) {
			$data['pp_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['pp_status'])) {
			$data['pp_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['pp_updated'])) {
			$data['pp_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据 UID 删除项目进度信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'pp_status' => self::STATUS_REMOVE,
			'pp_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据 id 删除项目进度信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'pp_status' => self::STATUS_REMOVE,
			'pp_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除参加项目的人员进度
	 * @param int $p_id 会议id
	 * @param array $uids 用户uid数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_p_id_uid($p_id, $uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'pp_status' => self::STATUS_REMOVE,
			'pp_deleted' => startup_env::get('timestamp')
		);
		return parent::_query(self::$__table, "UPDATE %t SET `pp_status`='%d' WHERE `p_id`='%d' AND `m_uid` IN (%n)", array(
			self::$__table, self::STATUS_REMOVE, $p_id, $uids
		), $shard_key);
	}

	/**
	 * 删除指定项目 p_id(number | array) 的所有进度信息
	 * @param mixed $p_ids
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @return boolean
	 */
	public static function delete_by_p_ids($p_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'pp_status' => self::STATUS_REMOVE,
			'pp_deleted' => startup_env::get('timestamp')
		), db_help::field('p_id', $p_ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 统计某人在某个时间段的总任务数
	 * @param number $uid 指定人员ID
	 * @param mixed $is_complete 完成状态。true=已完成的，false=未完成的，其他=全部
	 * @param number $start_time 统计开始时间
	 * @param number $end_time 统计结束时间
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_by_uid($uid, $is_complete, $start_time, $end_time, $shard_key = array()) {

		if ($is_complete === true) {// 完成的
			$complete = " AND pp_progress=100";
		} elseif ($is_complete === false) {// 未完成的
			$complete = " AND pp_progress<100";
		} else {// 所有的
			$complete = '';
		}

		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`pp_id`) FROM %t
				WHERE m_uid=%d %i AND pp_status<%d AND pp_created>%d AND pp_created<%d"
				, array(self::$__table, $uid, $complete, self::STATUS_REMOVE, $start_time, $end_time), $shard_key
		);
	}

}
