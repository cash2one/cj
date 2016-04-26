<?php
/**
 * 审批模板表
 * $Author$
 * $Id$
 */

class voa_d_oa_askfor_customdata extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askfor_customdata';
	/** 主键 */
	private static $__pk = 'afcd_id';
	/** 正常状态 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有信息列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afcd_status`<'%d'
			ORDER BY `afcd_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 获取所有信息列表总数 */
	public static function count_all($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE afcd_status<'%d'", array(
				self::$__table, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 根据 id 读取信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `afcd_id`='%d' AND `afcd_status`<'%d'
			ORDER BY `afcd_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `afcd_id` IN (%n) AND `afcd_status`<'%d'
			ORDER BY `afcd_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `afcd_status`<'%d'
			ORDER BY `afcd_id` DESC".db_help::limit($start, $limit), array(
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
			WHERE `af_id` IN (%n) AND `afcd_status`<'%d'
			ORDER BY `afcd_id` ASC".db_help::limit($start, $limit), array(
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
		if (empty($data['afcd_status'])) {
			$data['afcd_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['afcd_created'])) {
			$data['afcd_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/**
	 * 批量新增信息
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 附件信息入库 */
		$sql_ats = array();
		foreach ($data as $_at) {
			$sql_ats[] = "(".implode(',', array(
				$_at['af_id'], '"'.$_at['field'].'"','"'.$_at['name'].'"', '"'.$_at['value'].'"',
				self::STATUS_NORMAL, startup_env::get('timestamp'), startup_env::get('timestamp')
			)).")";
		}

		if (empty($sql_ats)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(af_id, field, name, value, afcd_status, afcd_created, afcd_updated) VALUES".implode(',', $sql_ats), array(self::$__table), $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['afcd_status'])) {
			$data['afcd_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['afcd_updated'])) {
			$data['afcd_updated'] = startup_env::get('timestamp');
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
			'afcd_status' => self::STATUS_REMOVE,
			'afcd_deleted' => startup_env::get('timestamp')
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
			'afcd_status' => self::STATUS_REMOVE,
			'afcd_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}
}
