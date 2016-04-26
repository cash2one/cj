<?php
/**
 * voa_server_sql
 * 数据库SQL语句执行接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_sql extends dao_mysql {

	/** 本类实例 */
	private static $s_instance = NULL;

	/** 获取实例 */
	public static function instance() {
		if (is_null(self::$s_instance)) {
			self::$s_instance = new self();
		}

		return self::$s_instance;
	}

	public function __construct() {

	}

	/**
	 * 创建应用的数据表结构
	 * @param string $db_module
	 * @param string $identifier
	 * @param array $shard_key
	 * @return array 数据表名列表
	 */
	public function create_application_table($db_module = '', $identifier = '', $shard_key = array()) {
		/** 读取SQL文件内容 */
		$sql_data = $this->_application_sql_file($identifier, 'structure', true);
		if (empty($sql_data)) {
			return ;
		}
		/** 解析SQL文件分组、分行 */
		$query_list = $this->_parse_sql($sql_data);
		/** 执行SQL语句 */
		$this->_execute_sql($db_module, $query_list, $shard_key);

		// 返回数据表名列表信息
		$tablenames = array();
		foreach ($query_list as $_data) {
			if (!empty($_data['tablename'])) {
				$tablenames[] = $_data['tablename'];
			}
		}
		return $tablenames;
	}

	/**
	 * 导入应用的默认数据
	 * @param string $db_module
	 * @param string $identifier
	 * @param array $shard_key
	 * @param array|false $truncate_tablenames_data 需要清空的数据表列表。false则不干预SQL文件的内容
	 * @param array|false $insert_tablenames 需要写入数据的数据表列表，不在此列表的表数据将不导入。false则不干预SQL文件的内容
	 */
	public function import_application_data($db_module = '', $identifier = '', $shard_key = array(), $truncate_tablenames_data = array(), $insert_tablenames = array()) {

		// 执行清空的 SQL 内容
		$truncate_sql_data = '';
		// 执行清空的查询列表
		$truncate_query_list = array();
		if (is_array($truncate_tablenames_data) && $truncate_tablenames_data) {
			// 要求清空的数据表

			// 遍历表名，构造清空表数据SQL语句
			foreach ($truncate_tablenames_data as $tablename) {
				$truncate_sql_data .= 'TRUNCATE `{$prefix}'.$tablename.'{$suffix}`;'."\r\n\r\n";
			}

			// 获取到需要清空的 query 列表
			$truncate_query_list = $this->_parse_sql($truncate_sql_data);
			if (!empty($truncate_query_list)) {
				$this->_execute_sql($db_module, $truncate_query_list, $shard_key);
			}
		}


		// 读取默认表数据的 SQL 文件内容
		$sql_data = $this->_application_sql_file($identifier, 'data', true);
		if (empty($sql_data)) {
			return ;
		}

		// 解析SQL文件分组、分行
		// 检查是否存在需要插入默认数据的表
		$query_list = array();
		foreach ($this->_parse_sql($sql_data) as $_data) {
			if (!is_array($insert_tablenames) || in_array($_data['tablename'], $insert_tablenames)) {
				$query_list[] = $_data;
			}
		}

		// 执行SQL语句
		if (!empty($query_list)) {
			$this->_execute_sql($db_module, $query_list, $shard_key);
		}
	}

	/**
	 * 构造应用的SQL语句文件路径，并尝试读取
	 * @param string $identifier
	 * @param string $type data | structure
	 * @param boolean $read_content 是否读取内容
	 * @return string
	 */
	private function _application_sql_file($identifier, $type = '', $read_content = true) {
		$type != 'data' && $type = 'structure';
		$filename = $this->_sql_file($identifier, $type);
		if ($read_content) {
			$data = @file_get_contents($filename);
			if ($data) {
				return $data;
			} else {
				logger::error('sql file not exists. '.$filename);
				return '';
			}
		} else {
			return $filename;
		}
		return $read_content ? @file_get_contents($filename) : $filename;
	}

	/**
	 * 返回给定的目录名内的SQL文件绝对路径
	 * @param string $sql_dir_name
	 * @param string $sql_file_name
	 * @return string
	 */
	private function _sql_file($sql_dir_name, $sql_file_name) {
		$path = APP_PATH.DIRECTORY_SEPARATOR;
		$path .= 'src'.DIRECTORY_SEPARATOR;
		$path .= 'sql'.DIRECTORY_SEPARATOR;
		$path .= $sql_dir_name.DIRECTORY_SEPARATOR;
		$path .= $sql_file_name.'.sql';
		return $path;
	}

	/**
	 * 将SQL语句块解析为一行
	 * @param string $sql_data
	 * @return array(array(tablename => '', query => ''), ...)
	 */
	private function _parse_sql($sql_data) {
		if (empty($sql_data)) {
			return array();
		}
		$sql_data = str_replace(array("\r\n", "\r"), "\n", $sql_data);
		$query_line_list = array();
		/** 拆解完全的SQL语句，每个完整的SQL为一组$_query_block */
		foreach(explode(";\n", trim($sql_data)."\n") as $_query_block) {
			$_query_block = trim($_query_block);
			if (empty($_query_block)) {
				//跳过空的
				continue;
			}

			/** 将SQL语句分解为行，进行解析 */
			$query_line = '';
			foreach(explode("\n", trim($_query_block)) as $_block) {
				$_block = trim($_block);
				if ($_block === '' || $_block[0] == '#') {
					//空行 或 行首为#注释 则跳过
					continue;
				}
				if (isset($_block[1]) && $_block[0].$_block[1] == '--') {
					//行注释则跳过
					continue;
				}
				$query_line .= $_block;
			}

			if (empty($query_line)) {
				//SQL语句为空，则跳过
				continue;
			}

			/** 解析当前执行的SQL语句所在的数据表 */
			if (!preg_match('/{\$prefix}(\w+){\$suffix}/', $query_line, $match) && !preg_match('/{\$prefix}(\w+)/', $query_line, $match) ) {
				//找不到数据表名，则跳过
				continue;
			}

			$query_line_list[] = array(
					'tablename' => $match[1],
					'query' => str_replace($match[0], '%t', $query_line)
			);

			unset($query_line, $match, $_block);
		}
		unset($sql_data);
		return $query_line_list;
	}

	/**
	 * 执行SQL语句查询
	 * @param string $db_module
	 * @param array $query_list
	 * @param array $shard_key
	 * @return void
	 */
	private function _execute_sql($db_module, $query_list, $shard_key) {
		if (empty($query_list)) {
			return null;
		}
		foreach ($query_list as $sql) {
			if (empty($sql['tablename']) || empty($sql['query'])) {
				continue;
			}
			parent::_query($db_module.'.'.$sql['tablename'], $sql['query'], array($db_module.'.'.$sql['tablename']), $shard_key);
		}
	}

}
