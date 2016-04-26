<?php
/**
 * 会议表
 * $Author$
 * $Id$
 */

class voa_d_oa_meeting extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.meeting';
	/** 主键 */
	private static $__pk = 'mt_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已取消 */
	const STATUS_CANCEL = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;
	/** 会议的状态 */
	const MEETING_ALL = 0;
	const MEETING_NEW = 1;
	const MEETING_FIN = 2;

	/**
	 * 返回所有会议总数
	 */
	public static function count_all($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `mt_status`<'%d'", array(
				self::$__table, self::STATUS_REMOVE,
			), $shard_key
		);
	}

	/** 获取所有会议列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mt_status`<'%d'
			ORDER BY `mt_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取会议信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `mt_id`='%d' AND `mt_status`<'%d'
			ORDER BY `mt_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mt_id` IN (%n) AND `mt_status`<'%d'
			ORDER BY `mt_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 mr_id 读取会议列表
	 * @param int $mr_id 房间id
	 * @param int $status 读取状态, 0: 所有, 1:未结束, 2:已结束
	 * @param int $start
	 * @param int $limit
	 */
	public static function fetch_by_mr_id($mr_id, $status = 0, $start = 0, $limit = 0, $shard_key = array()) {
		/** 状态条件 */
		$where = '';
		if (self::MEETING_NEW == $status) {
			$where = ' AND `mt_endtime`>'.startup_env::get('timestamp');
		} else if(self::MEETING_FIN == $status) {
			$where = ' AND `mt_endtime`<='.startup_env::get('timestamp');
		}

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mr_id`='%d' AND `mt_status`<'%d'{$where}
			ORDER BY `mt_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $mr_id, self::STATUS_CANCEL
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据用户 uid 读取我发起的会议信息
	 * @param int $uid 用户uid
	 * @param int $start
	 * @param int $limit
	 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `mt_status`<'%d'
			ORDER BY `mt_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	
	/**
	 * 新增会议
	 *
	 * @param array $data 会议数据数组, 下标为字段名, 值为对应的会议信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['mt_status'])) {
			$data['mt_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['mt_created'])) {
			$data['mt_created'] = startup_env::get('timestamp');
		}

		if (empty($data['mt_updated'])) {
			$data['mt_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['mt_status'])) {
			$data['mt_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['mt_updated'])) {
			$data['mt_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据UID删除会议信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mt_status' => self::STATUS_REMOVE,
			'mt_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据id删除会议信息
	 *
	 * @param int|array $ids 会议 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mt_status' => self::STATUS_REMOVE,
			'mt_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 构造查询SQL语句
	 * @param array $conditions
	 * @return string
	 */
	public static function search_by_conditions($conditions) {
		$where = array();
		if (isset($conditions['m_username']) && $conditions['m_username']) {
			$where[] = db_help::field('m_username', $conditions['m_username']);
		}
		if (isset($conditions['mt_username']) && $conditions['mt_username']) {
			$where[] = db_help::field('mt_username', $conditions['mt_username']);
		}
		if (isset($conditions['mr_id']) && $conditions['mr_id'] > 0) {
			$where[] = db_help::field('mr_id', $conditions['mr_id']);
		}
		/** 会议进行状态 */
		if (isset($conditions['expire']) && $conditions['expire'] >= 0) {
			/** 判断状态 */
			if ($conditions['expire'] == 0) {
				//未开始
				$where[] = db_help::field('mt_begintime', startup_env::get('timestamp'), '>');
			} elseif ($conditions['expire'] == 1) {
				//正进行
				$where[] = db_help::field('mt_begintime', startup_env::get('timestamp'), '<=');
				$where[] = db_help::field('mt_endtime', startup_env::get('timestamp'), '>=');
			} else {
				//已结束
				$where[] = db_help::field('mt_endtime', startup_env::get('timestamp'), '<');
			}
		}
		/** 会议状态 */
		if (isset($conditions['mt_status'])) {
			//指定了会议状态
			/** 判断会议状态 */
			if ($conditions['mt_status'] == self::STATUS_CANCEL) {
				//已取消的
				$where[] = db_help::field('mt_status', self::STATUS_CANCEL, '=');
			} else {
				//未取消的
				$where[] = db_help::field('mt_status', self::STATUS_CANCEL, '<');
			}
		} else {
			//未指定状态，获取全部未删除的
			$where[] = db_help::field('mt_status', self::STATUS_REMOVE, '<');
		}
		if (isset($conditions['mt_subject']) && $conditions['mt_subject']) {
			$where[] = db_help::field('mt_subject', '%'.addcslashes($conditions['mt_subject'], '%_').'%', 'like');
		}
		return $where ? implode(' AND ', $where) : 1;
	}

	/**
	 * 计算指定条件的会议数量
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i", array(
				self::$__pk, self::$__table, self::search_by_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的会议
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `mt_id` DESC".db_help::limit($start, $limit),array(
				self::$__table, self::search_by_conditions($conditions)
			), self::$__pk, $shard_key
		);
	}
	
	/**
	 * 列出指定条件的会议
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions2($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `mt_begintime` ASC".db_help::limit($start, $limit),array(
				self::$__table, $conditions
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 uid 读取自己发布的会议
	 * @param int $uid
	 */
	public static function count_mine($uid, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `m_uid`='%d' AND mt_status<%d", array(
				self::$__table, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}
	

	
}
