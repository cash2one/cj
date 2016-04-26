<?php
/**
 * 微信墙回复信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_wxwall_post extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.wxwall_post';
	/** 主键 */
	private static $__pk = 'wwp_id';
	/** 申请中 */
	const STATUS_NORMAL = 1;
	/** 已通过(已批准) */
	const STATUS_APPROVE = 2;
	/** 已拒绝 */
	const STATUS_REFUSE = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 获取所有主题/记录详细信息列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wwp_status`<'%d'
			ORDER BY `wwp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取主题/记录详细信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `wwp_id`='%d' AND `wwp_status`<'%d'
			ORDER BY `wwp_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wwp_id` IN (%n) AND `wwp_status`<'%d'
			ORDER BY `wwp_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 通过 ww_id 读取微信墙回复信息列表 */
	public static function fetch_by_ww_id($ids, $status = array(), $start = 0, $limit = 0, $shard_key = array()) {
		if (empty($status)) {
			$where = ' AND '.db_help::field('wwp_status', self::STATUS_REMOVE, '<');
		} else {
			$status = (array)$status;
			$where = ' AND '.db_help::field('wwp_status', $status);
		}

		$sort = in_array(self::STATUS_NORMAL, $status) ? 'ASC' : 'DESC';
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ww_id` IN (%n) {$where}
			ORDER BY `wwp_created` {$sort}".db_help::limit($start, $limit), array(
				self::$__table, $ids
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 通过www_id和状态计算微信墙回复信息数
	 * @param array $ids
	 * @param array $status
	 */
	public static function count_by_ww_id($ids, $status, $shard_key = array()) {
		if (empty($status)) {
			$where = ' AND '.db_help::field('wwp_status', self::STATUS_REMOVE, '<');
		} else {
			$status = (array)$status;
			$where = ' AND '.db_help::field('wwp_status', $status);
		}

		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE ww_id IN (%n) {$where}", array(
				self::$__table, $ids
			), $shard_key
		);
	}

	/**
	 * 根据ww_id读取last_wwp_id之后发布的微信墙消息
	 * @param number $ww_id
	 * @param number $last_wwp_id
	 * @param number $start
	 * @param number $limit
	 */
	public static function fetch_all_by_ww_id_wwp_id($ww_id, $last_wwp_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE ww_id=%d AND wwp_id>%d AND wwp_status=%d
			ORDER BY `wwp_id` ASC ".db_help::limit($start,$limit),array(
				self::$__table, $ww_id, $last_wwp_id, self::STATUS_APPROVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 ww_id 和更新时间读取记录 */
	public static function fetch_by_ww_id_updated($id, $updated, $start, $limit, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ww_id`='%d' AND `wwp_updated`>'%d' AND `wwp_status`='%d'
			ORDER BY `wwp_updated` ASC".db_help::limit($start, $limit), array(
				self::$__table, $id, $updated, self::STATUS_APPROVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取微信墙回复信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `wwp_status`<'%d'
			ORDER BY `wwp_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 统计回复数
	 * @param int $ww_id 微信墙id
	 * @param int $uid 用户uid
	 * @param array $status 状态
	 */
	public static function count_by_ww_id_uid($ww_id, $uid, $status = array(), $shard_key = array()) {
		if (empty($status)) {
			$status = self::STATUS_APPROVE;
		}

		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `ww_id`='%d' AND `m_uid`='%d' AND `wwp_status` IN (%n)", array(
				self::$__table, $ww_id, $uid, $status
			), $shard_key
		);
	}

	/**
	 * 新增微信墙回复信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['wwp_status'])) {
			$data['wwp_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['wwp_created'])) {
			$data['wwp_created'] = startup_env::get('timestamp');
		}

		/** 更新时间在写入的时候和创建时间一致，用来排序（特殊） */
		if (empty($data['wwp_updated'])) {
			$data['wwp_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['wwp_updated'])) {
			$data['wwp_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据UID删除微信墙回复信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'wwp_status' => self::STATUS_REMOVE,
			'wwp_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field('m_uid', $uids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据微信墙 id 和 uid 删除微信墙回复信息
	 * @param int $ww_id 微信墙id
	 * @param int|array $uids 用户 uid
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_ww_id_uids($ww_id, $uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'wwp_status' => self::STATUS_REMOVE,
			'wwp_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, "`ww_id`='{$ww_id}' AND ".db_help::field('m_uid', $uids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据 ww_id 删除微信墙回复信息
	 * @param int|array $ids 主题id或数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_ww_id($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'wwp_status' => self::STATUS_REMOVE,
			'wwp_deleted' => startup_env::get('timestamp')
		);
		return parent::_update(self::$__table, $data, db_help::field('ww_id', $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除主题/记录详细信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {

		$data = array(
			'wwp_status' => self::STATUS_REMOVE,
			'wwp_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}
}
