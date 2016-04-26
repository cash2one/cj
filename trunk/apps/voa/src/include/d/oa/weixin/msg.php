<?php
/**
 * 微信消息表
 * $Author$
 * $Id$
 */

class voa_d_oa_weixin_msg extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.weixin_msg';
	/** 主键 */
	private static $__pk = 'wm_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有来自微信的消息列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wm_status`<'%d'
			ORDER BY `wm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取来自微信的消息列表 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `wm_id`='%d' AND `wm_status`<'%d'
			ORDER BY `wm_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wm_id` IN (%n) AND `wm_status`<'%d'
			ORDER BY `wm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 msgid 读取来自微信的消息 */
	public static function fetch_by_msgid($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `wm_msgid`='%d' AND `wm_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_msgids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wm_msgid` IN (%n) AND `wm_status`<'%d'
			ORDER BY `wm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据发送方用户 openid 和 createtime 读取来自微信的消息 */
	public static function fetch_by_openid_createtime($openid, $createtime, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `wm_fromusername`=%s AND `wm_createtime`='%d' AND `wm_status`<'%d'", array(
				self::$__table, $openid, $createtime, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 新增来自微信的信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['wm_status'])) {
			$data['wm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['wm_created'])) {
			$data['wm_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['wm_status'])) {
			$data['wm_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['wm_updated'])) {
			$data['wm_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除来自微信的信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return self::update(array(
			'wm_status' => self::STATUS_REMOVE,
			'wm_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}
}
