<?php
/**
 * 会议室表
 * $Author$
 * $Id$
 */

class voa_d_oa_meeting_room extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.meeting_room';
	/** 主键 */
	private static $__pk = 'mr_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 会议室容积：小 */
	const VOLUME_SMALL = 1;
	/** 会议室容积：中 */
	const VOLUME_MIDDLE = 2;
	/** 会议室容积：大 */
	const VOLUME_BIG = 3;
	/** 会议室数据最多记录数 */
	const COUNT_MAX = 30;

	/** 获取会议室总数 */
	public static function count_all($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `mr_status`<'%d'", array(
				self::$__table, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 获取所有会议室列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mr_status`<'%d'
			ORDER BY `mr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取会议室信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `mr_id`='%d' AND `mr_status`<'%d'
			ORDER BY `mr_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mr_id` IN (%n) AND `mr_status`<'%d'
			ORDER BY `mr_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增会议室信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['mr_status'])) {
			$data['mr_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['mr_created'])) {
			$data['mr_created'] = startup_env::get('timestamp');
		}

		if (empty($data['mr_updated'])) {
			$data['mr_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['mr_status'])) {
			$data['mr_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['mr_updated'])) {
			$data['mr_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除会议室信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mr_status' => self::STATUS_REMOVE,
			'mr_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 获取会议室字段默认值
	 * @return array
	 */
	public static function fetch_all_field($shard_key = array()) {
		return parent::_fetch_all_field(self::$__table, $shard_key);
	}
}
