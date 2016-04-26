<?php
/**
 * voa_d_oa_common_setting
 * 系统配置数据表
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_common_setting extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.common_setting';
	/** 主键 */
	private static $__pk = 'cs_key';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 数组 */
	const TYPE_ARRAY = 1;
	const TYPE_NORMAL = 0;

	/** 微信企业号开通状态：未开通 */
	const WXQY_CLOSE = 0;
	/** 微信企业号开通状态：已开通手动开启应用模式（旧方式） */
	const WXQY_MANUAL = 1;
	/** 微信企业号开通状态：已开通授权模式启用应用（新方式20141210） */
	const WXQY_AUTH = 2;

	/**
	 * 根据缓存名称读取缓存
	 * @param string $param 缓存名
	 */
	public static function fetch($key, $shard_key = array()) {
		$data = self::fetch_by_keys(array($key), $shard_key);
		return isset($data[$key]) ? $data[$key] : false;
	}

	/**
	 * 根据缓存名称读取
	 * @param array $keys 缓存名数组
	 */
	public static function fetch_by_keys($keys, $shard_key = array()) {
		return parent::_fetch_all(self::$__table,
			"SELECT * FROM %t WHERE cs_key IN (%n) AND cs_status<'%d'",
			array(self::$__table, $keys, self::STATUS_REMOVE), self::$__pk, $shard_key
		);
	}

	/**
	 * 读取所有
	 * @param int $start
	 * @param int $limit
	 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table,
			"SELECT * FROM %t WHERE cs_status<'%d' ".db_help::limit($start, $limit),
			array(self::$__table, self::STATUS_REMOVE), self::$__pk, $shard_key
		);
	}

	/**
	 * 数据入库
	 * @param array $data 入库数据数组
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $shard_key = array()) {
		if (empty($data['cs_status'])) {
			$data['cs_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['cs_created'])) {
			$data['cs_created'] = startup_env::get('timestamp');
		}

		if (empty($data['cs_updated'])) {
			$data['cs_updated'] = $data['cs_created'];
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, false, $shard_key);
	}

	/**
	 * 更新
	 * @param array $data 需要更新的数据
	 * @param array|string $conditions 更新条件
	 */
	public static function update($data, $conditions, $shard_key = array()) {
		if (empty($data['cs_status'])) {
			$data['cs_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['cs_updated'])) {
			$data['cs_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $conditions, false, false, $shard_key);
	}

	/**
	 * 根据条件删除
	 * @param array|string $conditions 删除条件
	 */
	public static function delete($conditions, $shard_key = array()) {
		return self::update(array(
			'cs_status' => self::STATUS_REMOVE,
			'cs_deleted' => startup_env::get('timestamp')
		), $conditions, $shard_key);
	}

	/**
	 * 以变量名为键名输出所有变量信息
	 * @return array
	 */
	public static function fetch_all_setting($shard_key = array()) {
		$list = array();
		$prefix = 'cs_';
		$query = parent::_query(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY `{$prefix}key` ASC", array(
			self::$__table, db_help::field($prefix.'status', self::STATUS_REMOVE, '<')
		), $shard_key);
		while ($row = parent::_fetch(self::$__table, $query)) {
			$list[$row[$prefix.'key']] = array(
				'key' => $row[$prefix.'key'],
				'value' => $row[$prefix.'value'],
				'type' => $row[$prefix.'type'],
				'comment' => $row[$prefix.'comment']
			);
		}

		return $list;
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public static function update_setting($data, $shard_key = array()) {
		$prefix = 'cs_';
		/** 确定需要进行更新还是插入 */
		$is_update_keys = array();
		$tmp = parent::_fetch_all(self::$__table, "SELECT `{$prefix}key` FROM %t WHERE %i", array(
			self::$__table, db_help::field($prefix.'key', array_keys($data)), db_help::field($prefix.'status', self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
		foreach ($tmp AS $row) {
			$is_update_keys[$row[$prefix.'key']] = $row[$prefix.'key'];
		}

		foreach ($data as $key => $value) {
			$data = array(
				$prefix.'value' => $value,
				$prefix.'status' => self::STATUS_UPDATE,
				$prefix.'updated' => startup_env::get('timestamp'),
			);
			if (isset($is_update_keys[$key])) {
				parent::_update(self::$__table, $data, array($prefix.'key' => $key), false, false, $shard_key);
			} else {
				$data[$prefix.'key'] = $key;
				$data[$prefix.'type'] = @unserialize($value) === false ? 0 : 1;
				$data[$prefix.'status'] = self::STATUS_NORMAL;
				$data[$prefix.'created'] = startup_env::get('timestamp');
				parent::_insert(self::$__table, $data, false, false, false, $shard_key);
			}
		}

		return true;
	}
}
