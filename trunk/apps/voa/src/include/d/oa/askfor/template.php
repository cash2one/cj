<?php
/**
 * 审批模板表
 * $Author$
 * $Id$
 */

class voa_d_oa_askfor_template extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askfor_template';
	/** 主键 */
	private static $__pk = 'aft_id';
	/** 正常状态 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 删除 */
	const STATUS_REMOVE = 3;

	/** 启用 */
	const IS_USE = 1;
	/** 禁用 */
	const UNUSE = 0;

	/** 获取所有信息列表 */
	public static function fetch_all_for_is_use($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `aft_status`<'%d' AND is_use='%d'
			ORDER BY `orderid` ASC, `aft_created` DESC".db_help::limit($start, $limit), array(
			self::$__table, self::STATUS_REMOVE, self::IS_USE
		), self::$__pk, $shard_key
		);
	}

	/** 获取所有信息列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `aft_status`<'%d'
			ORDER BY `aft_created` DESC".db_help::limit($start, $limit), array(
			self::$__table, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/** 获取所有信息列表总数 */
	public static function count_all($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE aft_status<'%d'", array(
			self::$__table, self::STATUS_REMOVE
		), $shard_key
		);
	}

	/** 根据 id 读取信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `aft_id`='%d' AND `aft_status`<'%d'
			ORDER BY `aft_id` DESC", array(
			self::$__table, $id, self::STATUS_REMOVE
		), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `aft_id` IN (%n) AND `aft_status`<'%d'
			ORDER BY `aft_id` DESC", array(
			self::$__table, $ids, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `aft_status`<'%d'
			ORDER BY `aft_id` DESC".db_help::limit($start, $limit), array(
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
	public static function fetch_by_af_id($af_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `af_id` IN (%n) AND `aft_status`<'%d'
			ORDER BY `aft_id` DESC".db_help::limit($start, $limit), array(
			self::$__table, $af_id, self::STATUS_REMOVE
		), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['aft_status'])) {
			$data['aft_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['aft_created'])) {
			$data['aft_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['aft_status'])) {
			$data['aft_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['aft_updated'])) {
			$data['aft_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据UID删除信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'aft_status' => self::STATUS_REMOVE,
			'aft_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据id删除信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'aft_status' => self::STATUS_REMOVE,
			'aft_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}
}
