<?php
/**
 * 会议成员表
 * $Author$
 * $Id$
 */

class voa_d_oa_meeting_mem extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.meeting_mem';
	/** 主键 */
	private static $__pk = 'mm_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 确认参加 */
	const STATUS_CONFIRM = 2;
	/** 确认不参加 */
	const STATUS_ABSENCE = 3;
	/** 已取消 */
	const STATUS_CANCEL = 4;
	/** 已删除 */
	const STATUS_REMOVE = 5;
	/** 会议的状态 */
	const MEETING_ALL = 0;
	const MEETING_NEW = 1;
	const MEETING_FIN = 2;

	/** 获取所有用户列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mm_status`<'%d'
			ORDER BY `mm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取参会用户信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `mm_id`='%d' AND `mm_status`<'%d'
			ORDER BY `mm_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mm_id` IN (%n) AND `mm_status`<'%d'
			ORDER BY `mm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据会议 mt_id 读取参会用户信息 */
	public static function fetch_by_mt_id($mt_id, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mt_id`='%d' AND `mm_status`<'%d'
			ORDER BY `mm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $mt_id, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取参会用户信息 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `mm_status`<'%d'
			ORDER BY `mm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据会议 id 和 uid 读取参会人信息 */
	public static function fetch_by_mt_id_uid($mt_id, $uid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `mt_id`='%d' AND `m_uid`='%d' AND `mm_status`<'%d'", array(
				self::$__table, $mt_id, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 统计确认参加的用户数 */
	public static function count_by_mt_id($mt_id, $status = array(), $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE `mt_id`='%d' AND `mm_status` IN (%n)", array(
				self::$__table, $mt_id, $status
			), $shard_key
		);
	}

	/**
	 * 统计待参加的会议数
	 * @param int $uid
	 */
	public static function count_by_uid($uid, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`mt_id`=`b`.`mt_id`
			WHERE `a`.`m_uid`='%d' AND `a`.`mm_status` IN (%n) AND `b`.`mt_endtime`<'%d'", array(
				self::$__table, 'oa.meeting', $uid,
				array(self::STATUS_NORMAL, self::STATUS_CONFIRM), startup_env::get('timestamp')
			), $shard_key
		);
	}

	/**
	 * 根据 uid 读取我需要参加的会议
	 * @param int $uid 用户uid
	 * @param int $status 会议状态, 0: 所有, 1:未结束, 2:已结束
	 */
	public static function count_join_by_uid($uid, $status = 0, $shard_key = array()) {
		/** 状态条件 */
		$where = '';
		if (self::MEETING_NEW == $status) {
			$where = ' AND `mt_endtime`>'.startup_env::get('timestamp');
		} else if(self::MEETING_FIN == $status) {
			$where = ' AND `mt_endtime`<='.startup_env::get('timestamp');
		}

		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t AS `mm`
			LEFT JOIN %t AS `mt` ON `mm`.`mt_id`=`mt`.`mt_id`
			WHERE `mm`.`m_uid` IN (%n) AND `mm_status`<'%d'{$where}", array(
				self::$__table, 'oa.meeting', array($uid, 0),
				voa_d_oa_meeting_mem::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 根据 uid 读取我需要参加的会议
	 * @param int $uid 用户uid
	 * @param int $updated 会议信息最后更新时间
	 * @param int $start
	 * @param int $limit
	 * @param int $status 会议状态, 0: 所有, 1:未结束, 2:已结束
	 */
	public static function fetch_join_by_uid_updated($uid, $updated, $start = 0, $limit = 0, $status = 0, $shard_key = array()) {
		/** 状态条件 */
		$where = '';
		if (self::MEETING_NEW == $status) {
			$where = ' AND `mt_endtime`>'.startup_env::get('timestamp');
		} else if(self::MEETING_FIN == $status) {
			$where = ' AND `mt_endtime`<='.startup_env::get('timestamp');
		}

		return (array)parent::_fetch_all(self::$__table, "SELECT `b`.* FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`mt_id`=`b`.`mt_id`
			WHERE `a`.`m_uid`='%d' AND `a`.`mm_status` IN (%n){$where}", array(
				self::$__table, 'oa.meeting', $uid,
				array(self::STATUS_NORMAL, self::STATUS_CONFIRM), startup_env::get('timestamp')
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据查询条件拼凑 sql 条件
	 * @param array $conditions 查询条件
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 */
	public static function parse_conditions($conditions = array()) {
		$wheres = array();
		/** 遍历条件 */
		foreach ($conditions as $field => $v) {
			/** 非当前表字段 */
			if (!in_array($field, self::$__fields)) {
				continue;
			}

			$f_v = $v;
			$gule = '=';
			/** 如果条件为数组, 则 */
			if (is_array($v)) {
				$f_v = $v[0];
				$gule = empty($v[1]) ? '=' : $v[1];
			}

			$wheres[] = db_help::field($field, $f_v, $gule);
		}

		return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND mm_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的投票
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND mm_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增参会用户信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的用户信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['mm_status'])) {
			$data['mm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['mm_created'])) {
			$data['mm_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['mm_status'])) {
			$data['mm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['mm_updated'])) {
			$data['mm_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据 UID 删除参会用户信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mm_status' => self::STATUS_REMOVE,
			'mm_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据 id 删除参会用户信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mm_status' => self::STATUS_REMOVE,
			'mm_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 删除参加会议的人员
	 * @param int $mt_id 会议id
	 * @param array $uids 用户uid数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_mt_id_uid($mt_id, $uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'mm_status' => self::STATUS_REMOVE,
			'mm_deleted' => startup_env::get('timestamp')
		);
		return parent::_query(self::$__table, "UPDATE %t SET `mm_status`='%d' WHERE `mt_id`='%d' AND `m_uid` IN (%n)", array(
			self::$__table, self::STATUS_REMOVE, $mt_id, $uids
		), $shard_key);
	}

	/**
	 * 删除指定会议的参会人员记录
	 * @param array $mt_id
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_mt_id($mt_id, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mm_status' => self::STATUS_REMOVE,
			'mm_deleted' => startup_env::get('timestamp')
		), db_help::field('mt_id', $mt_id), $unbuffered, false, $shard_key);
	}

	/**
	 * 某人在某个时间段内确认参会数
	 * @param number $m_uid
	 * @param number $start_time
	 * @param number $end_time
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_by_m_uid($m_uid, $start_time, $end_time, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`m_uid`) FROM %t
				WHERE `m_uid`=%d AND `mm_created`>%d AND `mm_created`<%d AND `mm_status`=%d", array(
			self::$__table, $m_uid, $start_time, $end_time, self::STATUS_CONFIRM
		), $shard_key);
	}

}
