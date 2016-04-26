<?php
/**
 * 数据库操作类
 * $Author$
 * $Id$
 */

class db_shard {
	/**
	 *  对象集合
	 *
	 *  @var object
	 */
	private static $_instances = array();
	/**
	 * 默认分库分表规则
	 *  array(
	 * 		'plugin' => '项目名_shard_plugin'
	 *  );
	 */
	public static $rules = array();

	/**
	 * 分库工厂方法
	 * @param array $options 分库/分表配置
	 * @param array $shard_key 分库/分表所需参数
	 * @throws db_shard_exception
	 * @return unknown
	 */
	public static function &factory($options, $shard_key) {
		ksort($options);
		$skey = md5(http_build_query($options).http_build_query($shard_key));
		if (array_key_exists($skey, self::$_instances)) {
			return self::$_instances[$skey];
		}

		$rule = $options['rule'];
		$rules = self::$rules;

		/** 取分库/分表参数, 并转成数据 */
		$rule_keys = $options['keys'];
		if (!is_array($options['keys'])) {
			$rule_keys = array($options['keys']);
		}

		/** 判断必须的键值是否都存在 */
		foreach ($rule_keys as $key) {
			if (!$shard_key[$key]) {
				throw new db_shard_exception('shard_key invalid');
			}
		}

		/** 执行默认分库/分表操作 */
		if (array_key_exists($rule, $rules)) {
			$class = $rules[$rule];
			self::$_instances[$skey] = new $class($options, $shard_key);
			return self::$_instances[$skey];
		}

		/** 执行 */
		self::$_instances[$skey] = new $rule($options, $shard_key);
		return self::$_instances[$skey];
	}

	/**
	 * 获取数据库配置
	 * @param array $conf 默认数据库配置
	 * @return multitype:
	 */
	public function get_db_conf($conf = array()) {
		return $conf;
	}

	/**
	 * 获取真实的表名
	 * @param string $table 数据表名称
	 * @return string
	 */
	public function get_table($table = '') {
		return $table;
	}
}
