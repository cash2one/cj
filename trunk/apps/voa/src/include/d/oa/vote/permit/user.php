<?php
/**
 * 允许进行投票的用户表
 * $Author$
 * $Id$
 */

class voa_d_oa_vote_permit_user extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.vote_permit_user';
	/** 主键 */
	private static $__pk = 'vpu_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有列表(基本没啥意义) */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vpu_status`<'%d'
			ORDER BY `vpu_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取允许投票的用户信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `vpu_id`='%d' AND `vpu_status`<'%d'
			ORDER BY `vpu_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vpu_id` IN (%n) AND `vpu_status`<'%d'
			ORDER BY `vpu_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 v_id 读取允许投票的用户 */
	public static function fetch_by_v_id($v_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `v_id`='%d' AND `vpu_status`<'%d'
			ORDER BY `vpu_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $v_id, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 v_id m_uid 查询权限记录  */
	public static function fetch_by_v_id_uid($v_id, $m_uid, $shard_key = array()) {
		$uids = array($m_uid, 0);
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE v_id=%d AND m_uid IN (%n) AND vpu_status<%d", array(
				self::$__table, $v_id, $uids, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 新增允许查看日志/记录信息的用户
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的用户信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['vpu_status'])) {
			$data['vpu_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['vpu_created'])) {
			$data['vpu_created'] = startup_env::get('timestamp');
		}

		if (empty($data['vpu_updated'])) {
			$data['vpu_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['vpu_status'])) {
			$data['vpu_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['vpu_updated'])) {
			$data['vpu_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据UID删除允许投票的用户
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vpu_status' => self::STATUS_REMOVE,
			'vpu_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field('m_uid', $uids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除允许投票的用户
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vpu_status' => self::STATUS_REMOVE,
			'vpu_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据v_id删除允许投票的用户
	 *
	 * @param int|array $tids 主题 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_v_id($tids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vpu_status' => self::STATUS_REMOVE,
			'vpu_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field('v_id', $tids), $unbuffered, $low_priority, $shard_key);
	}

	public static function delete_by_v_id_uid($v_id, $uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vpu_status' => self::STATUS_REMOVE,
			'vpu_deleted' => startup_env::get('timestamp')
		);
		return parent::_query(self::$__table, "UPDATE %t SET `vpu_status`='%d' WHERE `v_id`='%d' AND `m_uid` IN (%n)", array(
			self::$__table, self::STATUS_REMOVE, $v_id, $uids
		), $shard_key);
	}
}
