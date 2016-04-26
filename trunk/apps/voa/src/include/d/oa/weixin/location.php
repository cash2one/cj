<?php
/**
 * 来自微信的位置信息表
 * $Author$
 * $Id$
 */


class voa_d_oa_weixin_location extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.weixin_location';
	/** 主键 */
	private static $__pk = 'wl_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有来自微信的地理位置信息列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wl_status`<'%d'
			ORDER BY `wl_id` DESC ".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取来自微信的地理位置信息列表 */
	public static function fetch_by_id($id, $shard_key = array()) {
		$id = intval($id);
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `wl_id`='%d' AND `wl_status`<'%d'
			ORDER BY `wl_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		$ids = rintval($ids, true);
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wl_id` IN (%n) AND `wl_status`<'%d'
			ORDER BY `wl_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取来自微信的地理位置信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `wl_status`<'%d'
			ORDER BY `wl_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增来自微信的地理位置信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {

		// 设置初始状态
		if (empty($data['wl_status'])) {
			$data['wl_status'] = self::STATUS_NORMAL;
		}

		// 设置创建时间
		if (empty($data['wl_created'])) {
			$data['wl_created'] = startup_env::get('timestamp');
		}

		// 设置更新时间
		if (empty($data['wl_updated'])) {
			$data['wl_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['wl_status'])) {
			$data['wl_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['wl_updated'])) {
			$data['wl_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除来自微信的地理位置信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return self::update(array(
			'wl_status' => self::STATUS_REMOVE,
			'wl_deleted' => startup_env::get('timestamp')
		), db_table::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 指定人员最后一次位置信息
	 * @param number $m_uid
	 * @param array $shard_key
	 * @return array
	 */
	public static function last_by_uid($m_uid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY %i DESC LIMIT 1", array(
			self::$__table, db_help::field('m_uid', $m_uid), self::$__pk
		), $shard_key);
	}

}
