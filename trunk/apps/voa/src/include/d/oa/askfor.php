<?php
/**
 * 审批表
 * $Author$
 * $Id$
 */

class voa_d_oa_askfor extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askfor';
	/** 主键 */
	private static $__pk = 'af_id';
	/** 审批中 */
	const STATUS_NORMAL = 1;
	/** 已通过(已批准) */
	const STATUS_APPROVE = 2;
	/** 通过并转审批 */
	const STATUS_APPROVE_APPLY = 3;
	/** 审批不通过 */
	const STATUS_REFUSE = 4;
	/** 草稿 */
	const STATUS_DRAFT = 5;
	/** 已催办 */
	const STATUS_REMINDER = 6;
	/** 已撤销 */
	const STATUS_CANCEL = 7;
	/** 已删除 */
	const STATUS_REMOVE = 8;

	/** 计算所有审批总数 */
	public static function count_all($shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE af_status<'%d'", array(
				self::$__table, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/** 获取所有审批列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `af_status`<'%d'
			ORDER BY `af_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 构造查询条件
	 * @param array $param
	 * @return string
	 */
	public static function search_condition($condition = array()) {
		if (empty($condition)) {
			return 1;
		}
		$where = array();
		if (isset($condition['cp_pluginid']) && !empty($condition['cp_pluginid'])) {
			$where[] = '`af`.'.db_help::field('cp_pluginid', $condition['cp_pluginid']);
		}
		if (isset($condition['af_id']) && !empty($condition['af_id'])) {
			$where[] = '`af`.'.db_help::field('af_id', $condition['af_id']);
		}
		if (isset($condition['m_uid']) && !empty($condition['m_uid'])) {
			$where[] = '`af`.'.db_help::field('m_uid', $condition['m_uid']);
		}
		if (isset($condition['m_username']) && $condition['m_username']) {
			$where[] = '`af`.'.db_help::field('m_username', $condition['m_username']);
		}
		if (isset($condition['af_subject']) && $condition['af_subject']) {
			$where[] = '`af`.'.db_help::field('af_subject', '%'.addcslashes($condition['af_subject'], '%_').'%', 'like');
		}
		if (isset($condition['af_status']) && $condition['af_status'] > 0) {
			$where[] = '`af`.'.db_help::field('af_status', $condition['af_status']);
		} else {
			$where[] = '`af`.'.db_help::field('af_status', self::STATUS_REMOVE, '<');
		}
		if (isset($condition['cd_id']) && $condition['cd_id'] > 0) {
			$where[] = '`m`.'.db_help::field('cd_id', $condition['cd_id']);
		}
		if (isset($condition['aft_id']) && $condition['aft_id'] > 0) {
			$where[] = '`af`.'.db_help::field('aft_id', $condition['aft_id']);
		}

		return $where ? implode(' AND ', $where) : '`af`.'.db_help::field('af_status', self::STATUS_REMOVE, '<');
	}

	/**
	 * 根据查询条件计算符合条件的审批数量
	 * @param array $condition
	 * @return number
	 */
	public static function count_all_by_condition($condition, $shard_key = array()) {
		if (empty($condition)) {
			return self::count_all();
		}

		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`af`.`af_id`) FROM %t `af`
			LEFT JOIN %t `m` ON `m`.`m_uid`=`af`.`m_uid` WHERE %i", array(
			self::$__table, 'oa.member', self::search_condition($condition)
		), $shard_key);
	}

	/**
	 * 返回符合条件的审批列表数据
	 * @param array $condition
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function fetch_all_by_condition($condition, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t AS `af`
			LEFT JOIN %t `m` ON `m`.`m_uid`=`af`.`m_uid`
			WHERE %i ORDER BY `af`.`af_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, 'oa.member', self::search_condition($condition)
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取审批列表 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `af_id`='%d' AND `af_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `af_id` IN (%n) AND `af_status`<'%d'
			ORDER BY `af_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	public static function fetch_mine($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return self::fetch_all_by_condition(array(
			'm_uid' => $uid
		), $start, $limit, $shard_key);
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND af_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
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


	/** 根据 uid 读取已经完成审批的列表 */
	public static function fetch_done_by_uids_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`b`.*, `a`.`m_username` AS `afp_username`, `a`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `a`
			LEFT JOIN %t AS `b` ON `a`.`af_id`=`b`.`af_id`
			WHERE `a`.`m_uid` IN (%n) AND `a`.`afp_status` IN (%n) AND `b`.`af_updated`<'%d'
			ORDER BY `b`.`af_updated` DESC".db_help::limit($start, $limit), array(
				'oa.askfor_proc', self::$__table, $uids,
				array(self::STATUS_APPROVE, self::STATUS_APPROVE_APPLY, self::STATUS_REFUSE),
				$updated
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取等待我批复的申请列表 */
	public static function fetch_deal_by_uids_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
			LEFT JOIN %t AS `afp` ON `af`.`afp_id`=`afp`.`afp_id`
			WHERE `afp`.`m_uid` IN (%n) AND `af`.`af_status` IN (%n) AND `af`.`af_updated`<'%d'
			ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, 'oa.askfor_proc', $uids,
				array(self::STATUS_NORMAL, self::STATUS_APPROVE_APPLY), $updated
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取我申请的审批列表 */
	public static function fetch_my_by_uids_updated($uids, $updated, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
			LEFT JOIN %t AS `afp` ON `af`.`afp_id`=`afp`.`afp_id`
			WHERE `af`.`m_uid` IN (%n) AND `af`.`af_status`<'%d' AND `af`.`af_updated`<'%d'
			ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, 'oa.askfor_proc', $uids, self::STATUS_REMOVE, $updated
			), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取我发起的审批中的审批列表 */
	public static function fetch_my_askforing($uid,  $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
		LEFT JOIN %t AS `afp` ON `af`.`afp_id`=`afp`.`afp_id`
		WHERE `af`.`m_uid`='%d' AND `af`.`af_status` IN (%n)
		ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
			self::$__table, 'oa.askfor_proc', $uid, array(self::STATUS_NORMAL, self::STATUS_APPROVE_APPLY)
		), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取我发起的已审批的审批列表 */
	public static function fetch_my_askfored($uid, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
		LEFT JOIN %t AS `afp` ON `af`.`afp_id`=`afp`.`afp_id`
		WHERE `af`.`m_uid`='%d' AND `af`.`af_status` IN (%n)
		ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
			self::$__table, 'oa.askfor_proc', $uid, array(self::STATUS_APPROVE)
		), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取我发起的被驳回和撤销的审批列表 */
	public static function fetch_my_refuse_cancel($uid, $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
		LEFT JOIN %t AS `afp` ON `af`.`afp_id`=`afp`.`afp_id`
		WHERE `af`.`m_uid`='%d' AND `af`.`af_status` IN (%n)
		ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
			self::$__table, 'oa.askfor_proc', $uid, array(self::STATUS_REFUSE, self::STATUS_CANCEL)
		), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取等待我批复的审批列表 */
	public static function fetch_my_approving($uid,  $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
		LEFT JOIN %t AS `afp` ON `af`.`afp_id`=`afp`.`afp_id`
		WHERE `afp`.`m_uid`='%d' AND `af`.`af_status` IN (%n)
		ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
			self::$__table, 'oa.askfor_proc', $uid,
			array(self::STATUS_NORMAL, self::STATUS_APPROVE_APPLY)
		), self::$__pk, $shard_key
		);
	}

	/** 根据 uid 读取我已批复的审批列表 */
	public static function fetch_my_approved($uid,  $start = 0, $limit = 0, $shard_key = array()) {
		$fields = "`af`.*, `afp`.`afp_status`, `afp`.`m_username` AS `afp_username`, `afp`.`m_uid` AS `afp_uid`";
		return parent::_fetch_all(self::$__table, "SELECT {$fields} FROM %t AS `af`
		LEFT JOIN %t AS `afp` ON `af`.`af_id`=`afp`.`af_id`
		WHERE `afp`.`m_uid`='%d' AND `afp`.`afp_status` IN (%n)
		ORDER BY `af`.`af_updated` DESC".db_help::limit($start, $limit), array(
			self::$__table, 'oa.askfor_proc', $uid,
			array(self::STATUS_APPROVE, self::STATUS_APPROVE_APPLY, self::STATUS_REFUSE)
		), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_my_by_conditions($conditions, $shard_key = array()) {
		$where = is_array($conditions) ? self::parse_conditions($conditions) : $conditions;
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND af_status<%d", array(
			self::$__table, $where, self::STATUS_REMOVE
		), $shard_key);
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
		if (empty($data['af_status'])) {
			$data['af_status'] = self::STATUS_DRAFT;
		}

		if (empty($data['af_created'])) {
			$data['af_created'] = startup_env::get('timestamp');
		}

		if (empty($data['af_updated'])) {
			$data['af_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['af_updated'])) {
			$data['af_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除审批信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'af_status' => self::STATUS_REMOVE,
			'af_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据uid删除审批信息
	 *
	 * @param int|array $uids 用户 uid 或 uid 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'af_status' => self::STATUS_REMOVE,
			'af_deleted' => startup_env::get('timestamp')
		), db_help::field('m_uid', $uids), $unbuffered, false, $shard_key);
	}

	/**
	 * 统计指定用户发起的审批数
	 * @param int $uid
	 */
	public static function count_mine($uid, $shard_key = array()) {
		return parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE m_uid=%d AND af_status<%d", array(
				self::$__table, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}
}
