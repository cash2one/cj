<?php
/**
 * 项目成员表
 * $Author$
 * $Id$
 */

class voa_d_oa_project_mem extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.project_mem';
	/** 主键 */
	private static $__pk = 'pm_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 发起者, 但不参加 */
	const STATUS_OUTOF = 3;
	/** 抄送 */
	const STATUS_CC = 4;
	/** 已退出 */
	const STATUS_QUIT = 5;
	/** 已删除 */
	const STATUS_REMOVE = 6;

	/** 获取所有用户列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `pm_status`<'%d'
			ORDER BY `pm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取参会用户信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `pm_id`='%d' AND `pm_status`<'%d'
			ORDER BY `pm_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `pm_id` IN (%n) AND `pm_status`<'%d'
			ORDER BY `pm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据会议 p_id 读取用户信息 */
	public static function fetch_by_p_id($p_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `p_id`='%d' AND `pm_status`<'%d'
			ORDER BY `pm_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $p_id, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取参会用户信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `pm_status`<'%d'
			ORDER BY `pm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据会议 id 和 uid 读取参会人信息 */
	public static function fetch_by_p_id_uid($p_id, $uid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `p_id`='%d' AND `m_uid`='%d' AND `pm_status`<'%d'", array(
				self::$__table, $p_id, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 统计我参加的并且已关闭的项目数 */
	public static function count_closed($uid, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
			WHERE `a`.`m_uid`='%d' AND `a`.`pm_status`='%d' OR `b`.`p_status`='%d'", array(
				self::$__table, 'oa.project', $uid,
				self::STATUS_QUIT, voa_d_oa_project::STATUS_CLOSED
			), $shard_key
		);
	}

	/** 统计我参加的并且已完成的项目数 */
	public static function count_done($uid, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`p_id`=`b`.`p_id`
			WHERE `a`.`m_uid`='%d' AND `b`.`p_status`='%d'", array(
				self::$__table, 'oa.project', $uid, voa_d_oa_project::STATUS_COMPLETE
			), $shard_key
		);
	}

	/**
	 * 统计我参加的项目数
	 * @param int $uid
	 */
	public static function count_mine($uid, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t AS `a`
			WHERE `a`.`m_uid`='%d' AND `a`.`pm_status` IN (%n)", array(
				self::$__table, $uid,
				array(self::STATUS_NORMAL, self::STATUS_UPDATE, self::STATUS_CC),
				startup_env::get('timestamp')
			), $shard_key
		);
	}

	/**
	 * 根据 uid 读取未完成的项目
	 * @param mixed $uids
	 */
	public static function count_running_by_uid($uid, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t AS a
			LEFT JOIN %t AS b ON a.p_id=b.p_id
			WHERE a.m_uid=%d AND a.pm_status IN (%n) AND b.p_status IN (%n)", array(
				self::$__table, 'oa.project', $uid,
				array(self::STATUS_NORMAL, self::STATUS_UPDATE),
				array(voa_d_oa_project::STATUS_NORMAL, voa_d_oa_project::STATUS_UPDATE)
			), $shard_key
		);
	}

	/**
	 * 新增参会用户信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的用户信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['pm_status'])) {
			$data['pm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['pm_created'])) {
			$data['pm_created'] = startup_env::get('timestamp');
		}

		if (empty($data['pm_updated'])) {
			$data['pm_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['pm_status'])) {
			$data['pm_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['pm_updated'])) {
			$data['pm_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据 UID 删除项目用户信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'pm_status' => self::STATUS_REMOVE,
			'pm_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据 id 删除项目用户信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'pm_status' => self::STATUS_REMOVE,
			'pm_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除参加项目的人员
	 * @param int $p_id 会议id
	 * @param array $uids 用户uid数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_p_id_uid($p_id, $uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'pm_status' => self::STATUS_REMOVE,
			'pm_deleted' => startup_env::get('timestamp')
		);
		return parent::_query(self::$__table, "UPDATE %t SET `pm_status`='%d' WHERE `p_id`='%d' AND `m_uid` IN (%n)", array(
			self::$__table, self::STATUS_REMOVE, $p_id, $uids
		), $shard_key);
	}

	/**
	 * 删除指定项目 p_id(number | array) 的所有人员
	 * @param mixed $p_ids
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @return boolean
	 */
	public static function delete_by_p_ids($p_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'pm_status' => self::STATUS_REMOVE,
			'pm_deleted' => startup_env::get('timestamp')
		), db_help::field('p_id', $p_ids), $unbuffered, $low_priority, $shard_key);
	}
}
