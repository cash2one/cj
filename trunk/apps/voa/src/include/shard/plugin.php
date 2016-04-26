<?php
/**
 * 数据库分库/分表处理类
 * $Author$
 * $Id$
 */

class voa_shard_plugin extends db_shard {
    private $__db = array();
    /** 分库/分表所需参数 */
    private $__shard_key = array();
    /** 分库/分表配置 */
    private $__options = array();
    /** 逻辑表名和真实表名对照表 */
    private $__tables = array();

    /**
     * 分库方法
     * @param array $options 分库/分表配置
     * @param array $shard_key 分库/分表所需参数
     *  + int pluginid 应用id
     * @throws db_shard_exception
     * @return unknown
     */
	public function __construct($options, $shard_key) {
		$this->__options = $options;
		$this->__shard_key = $shard_key;
		/** 分库/分表中的数据库配置 */
		$this->__db = (array)$options['config'];
	}

	/**
	 * 获取数据库配置
	 * @param array $conf 默认数据库配置
	 * @return multitype:
	 */
	public function get_db_conf($conf = array()) {
		return array_merge($this->__db, $conf);
	}

	/**
	 * 获取真实的表名
	 * @param string $table 数据表名称
	 * @return string
	 */
	public function get_table($table) {
		/** 有缓存时, 直接返回 */
		if (array_key_exists($table, $this->__tables)) {
			return $this->__tables[$table];
		}

		/** 获取应用名 */
		$app_name = startup_env::get('app_name');
		/** 剔除表名中的库名前缀 */
		$tname = FALSE === stripos($table, '.') ? $table : substr($table, stripos($table, '.') + 1);
		/** 判断是否有分库/分表 */
		$shard_cfg = config::get($app_name.'.db.'.$table.'.shard');
		if (!empty($this->__shard_key['pluginid']) && $shard_cfg) {
			$this->__tables[$table] = $tname.'_'.$this->__shard_key['pluginid'];
		} else {
			$this->__tables[$table] = $tname;
		}

		return $this->__tables[$table];
	}
}
