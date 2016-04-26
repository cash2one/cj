<?php
/**
 * 微信墙表
 * $Author$
 * $Id$
 */

class voa_d_oa_wxwall extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.wxwall';
	/** 主键 */
	private static $__pk = 'ww_id';
	/** 申请中 */
	const STATUS_NORMAL = 1;
	/** 已通过(已批准) */
	const STATUS_APPROVE = 2;
	/** 审批不通过 */
	const STATUS_REFUSE = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 关闭 */
	const IS_CLOSE = 0;
	/** 开启 */
	const IS_OPEN = 1;

	/** 获取所有微信墙列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ww_status`<'%d' ORDER BY `ww_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取微信墙列表 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ww_id`='%d' AND `ww_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 根据微信墙管理员登录名获取微信墙信息
	 * @param string $admin
	 * @return array
	 */
	public static function fetch_by_admin($admin, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ww_admin`=%s AND `ww_status`<'%d'", array(
				self::$__table, $admin, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ww_id` IN (%n) AND `ww_status`<'%d'
			ORDER BY `ww_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取微信墙列表 */
	public static function fetch_by_uids($uids, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid` IN (%n) AND `ww_status`<'%d'
			ORDER BY `ww_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 读取审核中的 */
	public static function fetch_mine_apply($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `ww_status`='%d'
			ORDER BY `ww_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 读取运行中的 */
	public static function fetch_running($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ww_endtime`>'%d' AND `ww_status`<'%d' AND `ww_isopen`='%d'
			ORDER BY `ww_endtime` ASC".db_help::limit($start, $limit), array(
				self::$__table, startup_env::get('timestamp'), self::STATUS_REMOVE, self::IS_OPEN
			), self::$__pk, $shard_key
		);
	}

	/** 根据最后更新时间读取 */
	public static function fetch_fin_by_updated($updated, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ww_endtime`<'%d' AND `ww_status`<'%d'
			ORDER BY `ww_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $updated, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增审批信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['ww_status'])) {
			$data['ww_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ww_created'])) {
			$data['ww_created'] = startup_env::get('timestamp');
		}

		if (empty($data['ww_updated'])) {
			$data['ww_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['ww_updated'])) {
			$data['ww_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'ww_status' => self::STATUS_REMOVE,
			'ww_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据uid删除
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'ww_status' => self::STATUS_REMOVE,
			'ww_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 通过二维码场景id获取微信墙信息
	 * @param int $sceneid
	 */
	public static function fetch_by_sceneid($sceneid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ww_sceneid`='%d' AND `ww_status`='%d'
			ORDER BY `ww_created` DESC, `ww_updated` DESC", array(
				self::$__table, $sceneid, self::STATUS_APPROVE
			), $shard_key
		);
	}

	/**
	 * 找到所有关闭或者结束了的微信墙ww_id
	 */
	public static function fetch_all_close($shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT `ww_id` FROM %t
			WHERE `ww_isopen`=0 OR `ww_endtime`<'%d'", array(
				self::$__table, startup_env::get('timestamp')
			), self::$__pk, $shard_key
		);
	}

	/** 读取运行中的数目 */
	public static function count_running($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `ww_endtime`>'%d' AND `ww_status`<'%d' AND `ww_isopen`='%d'", array(
				self::$__table, startup_env::get('timestamp'), self::STATUS_REMOVE, self::IS_OPEN
			), $shard_key
		);
	}

	/** 读取已结束/关闭的数目 */
	public static function count_fin($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE ww_endtime<%d OR ww_isopen=%d", array(
				self::$__table, startup_env::get('timestamp'), self::IS_CLOSE
			), $shard_key
		);
	}

	/**
	 * 构造查询条件SQL语句
	 * @param array $conditions
	 * @return Ambigous <number, string>
	 */
	public static function search_by_conditions($conditions = array()) {
		$where = array();
		if (isset($conditions['ww_status'])) {
			$where[] = db_help::field('ww_status', $conditions['ww_status']);
		}


		if (!isset($conditions['ww_status'])) {
			$where[] = db_help::field('ww_status', self::STATUS_REMOVE, '<');
		}
		return $where ? implode(' AND ', $where) : 1;
	}

	/**
	 * 指定状态微信墙总数
	 * @param mixed $status
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `ww_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::search_by_conditions($conditions)
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 计算指定状态的微信墙总数
	 * @param mixed $status
	 * @return Ambigous <void, boolean>
	 */
	public static function count_all_by_conditions($conditions, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(`ww_id`) FROM %t WHERE %i", array(
			self::$__table, self::search_by_conditions($conditions)
		), $shard_key);
	}
}
