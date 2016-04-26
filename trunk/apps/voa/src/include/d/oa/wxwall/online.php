<?php
/**
 * 微信墙在线用户操作类
 * $Author$
 * $Id$
 */

class voa_d_oa_wxwall_online extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.wxwall_online';
	/** 主键 */
	private static $__pk = 'wwo_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有在线列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wwo_status`<'%d'
			ORDER BY `wwo_created` DESC ".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取在线信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `wwo_id`='%d' AND `wwo_status`<'%d'
			ORDER BY `wwo_created` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `wwo_id` IN (%n) AND `wwo_status`<'%d'
			ORDER BY `wwo_created` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}


	/**
	 * 添加在线信息
	 * @param array $data 字段数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insernc_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['wwo_status'])) {
			$data['wwo_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['wwo_created'])) {
			$data['wwo_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insernc_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['wwo_status'])) {
			$data['wwo_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['wwo_updated'])) {
			$data['wwo_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除在线信息
	 * @param int|array $ids wwo_id
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'wwo_status' => self::STATUS_REMOVE,
			'wwo_deleted' => startup_env::get('timestamp')
		);
		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据用户的openid和微信墙ww_id找到在线信息
	 * @param unknown $m_openid
	 * @param unknown $wwo_id
	 */
	public static function fetch_by_openid_id($m_openid, $ww_id = false, $shard_key = array()) {
		$where = false !== $ww_id ? ' AND '.db_help::field('ww_id', $ww_id) : '';
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE m_openid=%s AND wwo_status<%d{$where}
			ORDER BY `wwo_updated` DESC,`wwo_created` DESC LIMIT 1", array(
				self::$__table, $m_openid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 找到所有在线的微信墙id
	 */
	public static function fetch_all_by_online($shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT `ww_id` FROM %t
			WHERE ww_status<%d GROUP BY `ww_id`", array(
				self::$__table, self::STATUS_REMOVE
			), 'ww_id', $shard_key
		);
	}

	/**
	 * 删除指定微信墙ww_ids的在线信息
	 * @param unknown $ww_ids
	 */
	public static function delete_by_ww_id($ww_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'wwo_status'=>self::STATUS_REMOVE,
			'wwo_deleted'=>startup_env::get('timestamp'),
		);
		parent::_update(self::$__table, $data, db_help::field('ww_id', $ww_ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 删除指定的openid的在线信息
	 */
	public static function delete_by_openid_wwid($m_openid, $ww_id, $shard_key = array()) {
		$data = array(
			'm_openid'=>$m_openid,
			'wwo_status' => self::STATUS_REMOVE,
			'wwo_deleted' => startup_env::get('timestamp'),
		);
		parent::_update(self::$__table, $data, db_help::field('ww_id', $ww_id), false, false, $shard_key);
	}

}
