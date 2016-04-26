<?php
/**
 * voa_server_oa_suite
 * OA企业站读取套件相关数据
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_oa_suite {

	protected $_domain = '';

	/** 是否开启调试日志，开启后，所有数据将会写入日志，正式运行不允许开启！会导致日志文件过大！ */
	protected $_debug_open = false;

	public function __construct() {
	}

	public function get($params) {

		if (empty($params)) {
			throw new rpc_exception('rpc call params is null', 10019);
		}

		if (!isset($params['suiteid'])) {
			throw new rpc_exception('rpc lose param "suiteid"', 10020);
		}

		$suiteid = $params['suiteid'];

		$s_suite = service::factory('voa_s_oa_common_plugin_group');
		$suite = $s_suite->fetch_by_conditions(array(
			'cpg_suiteid' => $suiteid
		));
		if (!isset($suite)) {
			$this->_error_message('指定套件数据不存在');
		}

		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

		return array('suite' => $suite, 'plugins' => $plugins);
	}

	/**
	 * 运行 sql
	 * @param string $sql sql字串
	 * @param object $db 帐号密码
	 */
	protected function _run_query($sql, $dbname, &$db) {
		$db->select_db($dbname);
		$sql = str_replace("\r", "\n", $sql);
		$ret = array();
		$num = 0;
		foreach (explode(";\n", trim($sql)) as $query) {
			$queries = explode("\n", trim($query));
			foreach ($queries as $query) {
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}

			$num ++;
		}

		unset($sql);
		foreach ($ret as $query) {
			$query = trim($query);
			if ($query) {
				if (substr($query, 0, 12) == 'CREATE TABLE') {
					$name = preg_replace('/CREATE TABLE ([a-z0-9_]+) .*/is', "\\1", $query);
					$db->query($this->_create_table($query));
				} else {
					mysql_db_query($dbname, $query) or die('mysql error<br>'.$query.'<br>'.mysql_error());
				}
			}
		}
	}

	/**
	 * 整理表格创建 sql
	 * @param string $sql sql 语句
	 */
	protected function _create_table($sql) {
		$type = strtoupper(preg_replace('/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU', "\\2", $sql));
		$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'INNODB';
		return preg_replace('/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU', "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE={$type} DEFAULT CHARSET=UTF8" : " TYPE=$type"
		);
	}

	/**
	 * 写入调试日志信息
	 * @param string $data
	 */
	protected function _debug($data) {
		if ($this->_debug_open) {
			logger::error($this->_domain.':'.$data);;
		}
	}
}
