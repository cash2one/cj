<?php
/**
 * sms 短信发送表
 * $Author$
 * $Id$
 */

class voa_d_uc_sms extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.sms';
	/** 主键 */
	private static $__pk = 'sms_id';
	/** 所有字段名 */
	private static $__fields = array(
		'sms_id', 'sms_mobile', 'sms_message', 'sms_ip',
		'sms_status', 'sms_created', 'sms_updated', 'sms_deleted'
	);
	/** 已发送 */
	const STATUS_SENDED = 1;
	/** 未成功 */
	const STATUS_FAILED = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `sms_status`<'%d'
			ORDER BY `sms_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `sms_id`='%d' AND `sms_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `sms_id` IN (%n) AND `sms_status`<'%d'
			ORDER BY `sms_id` DESC", array(
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
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND sms_status<%d", array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的数据
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND sms_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['sms_status'])) {
			$data['sms_status'] = self::STATUS_BANDED;
		}

		if (empty($data['sms_created'])) {
			$data['sms_created'] = startup_env::get('timestamp');
		}

		if (empty($data['sms_updated'])) {
			$data['sms_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['sms_updated'])) {
			$data['sms_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'sms_status' => self::STATUS_REMOVE,
			'sms_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据 mobile 读取记录
	 * @param string $mobile 手机号码
	 * @param array $shard_key 分库分表参数
	 * @return boolean
	 */
	public static function fetch_by_mobile($mobile, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE sms_mobile=%s AND sms_status<%d", array(
				self::$__table, $mobile, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 根据 ip 读取记录
	 * @param string $ip 二级域名
	 * @param array $shard_key 分库分表参数
	 * @return boolean
	 */
	public static function fetch_by_ip($ip, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE sms_ip=%s AND sms_status<%d", array(
				self::$__table, $ip, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 信息入库
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 信息入库 */
		$smsdata = array();
		foreach ($data as $_m) {
			$smsdata[] = "(".implode(',', array(
				db_help::quote($_m['sms_mobile']),
				db_help::quote($_m['sms_message']),
				db_help::quote($_m['sms_ip']),
				db_help::quote($_m['sms_status']),
				empty($_m['sms_created']) ? startup_env::get('timestamp') : $_m['sms_created']
			)).")";
		}

		if (empty($smsdata)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(sms_mobile, sms_message, sms_ip, sms_status, sms_created) VALUES%i", array(self::$__table, implode(',', $smsdata)), $shard_key);
	}
}

