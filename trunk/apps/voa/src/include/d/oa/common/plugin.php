<?php
/**
 * 插件表
 * $Author$
 * $Id$
 */

class voa_d_oa_common_plugin extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.common_plugin';
	/** 主键 */
	private static $__pk = 'cp_pluginid';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;
	/** 新应用（从未启用过） */
	const AVAILABLE_NEW = 0;
	/** 启用状态：等待开启 */
	const AVAILABLE_WAIT_OPEN = 1;
	/** 启用状态：等待关闭 */
	const AVAILABLE_WAIT_CLOSE = 2;
	/** 启用状态：等待删除 */
	const AVAILABLE_WAIT_DELETE = 3;
	/** 启用状态：已启用*/
	const AVAILABLE_OPEN = 4;
	/** 启用状态：已关闭 */
	const AVAILABLE_CLOSE = 5;
	/** 启用状态：已删除 */
	const AVAILABLE_DELETE = 6;
	/** 未开放的应用 */
	const AVAILABLE_NONE = 255;

	/** 获取所有插件列表 */
	public static function fetch_all($start = 0, $limit = 0, $force = FALSE, $shard_key = array()) {
		/****修改应用显示顺序，张景龙，2016-04-11
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY `cp_pluginid` DESC".db_help::limit($start, $limit), array(
				self::$__table, ($force ? 1 : db_help::field('cp_status', self::STATUS_REMOVE, '<'))
			), self::$__pk, $shard_key
		);
		*/
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY `cp_displayorder`".db_help::limit($start, $limit), array(
				self::$__table, ($force ? 1 : db_help::field('cp_status', self::STATUS_REMOVE, '<'))
			), self::$__pk, $shard_key
		);
	}

	/** 根据插件 id 读取插件信息 */
	public static function fetch_by_ids($ids, $shard_key = array()) {
		$ids = rintval($ids);
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `cp_pluginid` IN (%n) AND `cp_status`<'%d'", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据唯一标识读取插件记录
	 * @param string $identifier 唯一标识字串
	 */
	public static function fetch_by_identifier($identifier, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `cp_identifier`=%s AND `cp_status`<'%d'", array(
				self::$__table, $identifier, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 验证唯一标识是否存在
	 * @param string|array 唯一标识
	 */
	public static function identifier_exists($identifier, $shard_key = array()) {
		$data = parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `cp_identifier` IN (%n) AND `cp_status`<'%d'", array(
				self::$__table, $identifier, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);

		/** 取唯一标识 */
		$idents = array();
		foreach ($data as $k => $v) {
			$idents[] = $v['cp_identifier'];
		}

		$diff = array_diff($identifier, $idents);
		return empty($diff) ? true : false;
	}

	/**
	 * 新增插件
	 *
	 * @param array $data 插件数据数组, 下标为字段名, 值为对应的插件信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['cp_status'])) {
			$data['cp_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['cp_created'])) {
			$data['cp_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['cp_status'])) {
			$data['cp_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['cp_updated'])) {
			$data['cp_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除插件
	 *
	 * @param int|array $ids 插件ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return self::update(array(
			'cp_status' => self::STATUS_REMOVE,
			'cp_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 按插件是否可用提取所有插件信息
	 * @param number $available
	 * @return array
	 */
	public static function fetch_all_by_available($available = 1, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND %i ORDER BY `cp_pluginid` ASC", array(
				self::$__table, db_help::field('cp_available', $available), db_help::field('cp_status', self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
	}

	/**
	 * 读取全部插件信息包含已标记为删除的
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_have_delete($start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t ORDER BY %i ASC", array(
			self::$__table, self::$__pk
		), self::$__pk, $shard_key);
	}

	/**
	 * 读取指定id的插件信息
	 * @param number $cp_pluginid
	 * @param boolean $force 是否强制读取已删除的
	 * @return array
	 */
	public static function fetch_by_cp_pluginid($cp_pluginid, $force = FALSE, $shard_key = array()) {
		return (array) parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field(self::$__pk, $cp_pluginid),
			($force ? 1 : db_help::field('cp_status', self::STATUS_REMOVE, '<'))
		), $shard_key);
	}

	/**
	 * 读取指定cma_id的插件信息
	 * @param number $cma_id
	 * @param string $force 是否强制读取已删除的
	 * @return array
	 */
	public static function fetch_by_cma_id($cma_id, $force = FALSE, $shard_key = array()) {
		if ($cma_id <= 0) {
			return array();
		}

		return (array) parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('cma_id', $cma_id),
			($force ? 1 : db_help::field('cp_status', self::STATUS_REMOVE, '<'))
		), $shard_key);
	}

	/**
	 * 找到指定的模块分组的第一个插件信息
	 * @param number $cmg_id
	 * @return array
	 */
	public static function fetch_first_by_cmg_id($cmg_id, $shard_key = array()) {
		return (array) parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i ASC LIMIT 1", array(
			self::$__table, db_help::field('cmg_id', $cmg_id), db_help::field('cp_status', self::STATUS_REMOVE, '<'), 'cp_displayorder'
		), $shard_key);
	}

	/**
	 * 找到指定应用型代理id对应的插件信息
	 * @param number $cp_agentid
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_cp_agentid($cp_agentid, $shard_key = array()) {
		return (array) parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i ASC LIMIT 1", array(
			self::$__table, db_help::field('cp_agentid', $cp_agentid), db_help::field('cp_status', self::STATUS_REMOVE, '<')
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
			/** 非当前表字段 */
			/*if (!in_array($field, self::$__fields)) {
				continue;
			}*/

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
	 * 设置所有不等于 cp_pluginid 但等于 cp_agentid 的应用的 cp_agentid 字段为空
	 * @param number $cp_pluginid
	 * @param number $agentid
	 * @param array $shard_key
	 * @return array
	 */
	public static function clear_agentid($cp_pluginid, $agentid, $shard_key = array()) {
		return (array) parent::_update(self::$__table, array('cp_agentid' => '', 'cp_suiteid' => ''), self::parse_conditions(array(
			'cp_agentid' => $agentid,
			'cp_pluginid' => array($cp_pluginid, '<>'),
		)), false, false, $shard_key);
	}

	public static function installed_count($shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE `cp_lastopen`>'0'", array(
			self::$__pk, self::$__table), $shard_key);
	}

}
