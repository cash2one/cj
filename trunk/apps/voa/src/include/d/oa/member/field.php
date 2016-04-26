<?php
/**
 * 用户信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_member_field extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.member_field';
	/** 主键 */
	private static $__pk = 'm_uid';
	/** 字段前缀 */
	private static $__prefix = 'mf_';
	/** 所有字段名 */
	private static $__fields = array(
		'm_uid', 'mf_address', 'mf_idcard', 'mf_telephone', 'mf_qq', 'mf_weixinid',
		'mf_birthday', 'mf_remark', 'mf_status', 'mf_created', 'mf_updated', 'mf_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 微信h5 */
	const DEVICE_HTML5 = 1;
	/** PC浏览器 */
	const DEVICE_PC = 2;
	/** 安卓设备 */
	const DEVICE_ANDROID = 3;
	/** 苹果设备 */
	const DEVICE_IOS = 4;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `mf_status`<'%d'
			ORDER BY `m_uid` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `mf_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid` IN (%n) AND `mf_status`<'%d'
			ORDER BY `m_uid` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
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
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND mf_status<%d", array(
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
			WHERE %i AND mf_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增投票信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['mf_status'])) {
			$data['mf_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['mf_created'])) {
			$data['mf_created'] = startup_env::get('timestamp');
		}

		if (empty($data['mf_updated'])) {
			$data['mf_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['mf_updated'])) {
			$data['mf_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除投票信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'mf_status' => self::STATUS_REMOVE,
			'mf_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
	 * @author Deepseath
	 * @return array
	 */
	public static function fetch_all_field() {
		return parent::_fetch_all_field(self::$__table);
	}

	/**
	 * 统计某个字段的数据被登记次数，除了m_uid外
	 * 如，统计weixinid被登记次数
	 * @param string $field 字段名
	 * @param string $value 数据
	 * @param number $m_uid 除此m_uid外
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_by_field_not_uid($field, $value, $m_uid = 0, $shard_key = array()) {
		$m_uid = (int)$m_uid;
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(`m_uid`) FROM %t WHERE %i AND %i AND %i", array(
			self::$__table,
			db_help::field($field, $value),
			db_help::field('mf_status', self::STATUS_REMOVE, '<'),
			$m_uid ? db_help::field('m_uid', $m_uid, '<>') : 1
		), $shard_key);
	}

	/**
	 * 找到与给定微信号相关联的所有uid
	 * @param string|array $weixinid
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_uid_by_weixinid($weixinid, $shard_key = array()) {
		$conditions = array(
			'mf_weixinid' => array($weixinid, is_array($weixinid) ? 'in' : '='),
		);
		return array_keys(self::fetch_by_conditions($conditions, 0, 0, $shard_key));
	}

}
