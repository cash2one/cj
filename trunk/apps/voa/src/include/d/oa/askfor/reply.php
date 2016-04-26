<?php
/**
 * 审批评论的回复表
 * $Author$
 * $Id$
 */

class voa_d_oa_askfor_reply extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askfor_reply';
	/** 主键 */
	private static $__pk = 'afr_id';
	/** 正常状态 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有签到申诉列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afr_status`<'%d'
			ORDER BY `afr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取签到申诉信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `afr_id`='%d' AND `afr_status`<'%d'
			ORDER BY `afr_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afr_id` IN (%n) AND `afr_status`<'%d'
			ORDER BY `afr_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取签到申诉信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `afr_status`<'%d'
			ORDER BY `afr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据审批 id 获取信息列表
	 * @param int|array $af_id 审批 id 或 id 数组
	 * @param int $start
	 * @param int $limit
	 */
	public static function fetch_by_afc_id($afc_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afc_id` IN (%n) AND `afr_status`<'%d'
			ORDER BY `afr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $afc_id, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 返回给定的审核评论id的所有回复信息
	 * @param array $afc_ids
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_afc_ids($afc_ids, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE afc_id='%d' AND afr_status<'%d' ORDER BY `afr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $afc_ids, self::STATUS_REMOVE
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
		if (empty($data['afr_status'])) {
			$data['afr_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['afr_created'])) {
			$data['afr_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['afr_status'])) {
			$data['afr_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['afr_updated'])) {
			$data['afr_updated'] = startup_env::get('timestamp');
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
			'afr_status' => self::STATUS_REMOVE,
			'afr_deleted' => startup_env::get('timestamp')
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
			'afr_status' => self::STATUS_REMOVE,
			'afr_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}
}
