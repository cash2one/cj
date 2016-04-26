<?php
/**
 * dbdiff.php
 * 比对数据表
 * @uses php tool.php -n dbdiff
 * -domain (二级域名, 如:url地址为 demo.vhcnagyi.com 时, 该值为 demo)
 * -db [操作db: oa|uc|cyadmin]
 * $Author$
 * $Id$
 */
class voa_backend_tool_dbdiff extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	/** 项目名称 */
	private $__dbs = array();
	/** 已被删除的字段 */
	private $__deleted_t2f = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
		startup_env::set('domain', $opts['domain']);
		voa_h_conf::init_db();
	}

	public function main() {

		$this->__dbs = config::get(startup_env::get('app_name').'.db.dbs');

		$n_struct = $o_struct = array();
		switch ($this->__opts['db']) {
			case 'oa':
			case 'uc':
			case 'cyadmin':
				break;
			default:
				echo 'Maybe the db is error.';
				return false;
				break;
		}

		$this->_fetch_lastest_struct($this->__opts['db'], $n_struct);
		$this->_fetch_db_struct($this->__opts['db'], $o_struct);

		/** 开始比对 */
		$diff = array();
		foreach ($n_struct as $tname => $struct) {
			if (empty($o_struct[$tname])) {
				$diff[$tname] = $struct;
				unset($o_struct[$tname]);
				continue;
			}

			/** 比对 */
			$o_rows = explode("\n", str_replace("\r", '', $o_struct[$tname]));
			$n_rows = explode("\n", str_replace("\r", '', $struct));
			$o_fields = $n_fields = array();
			$o_keys = $n_keys = array();
			$o_misc = $n_misc = array();
			$this->_fetch_fkm($o_rows, $o_fields, $o_keys, $o_misc);
			$this->_fetch_fkm($n_rows, $n_fields, $n_keys, $n_misc);

			$this->_field_diff($tname, $n_fields, $o_fields, $diff);
			$this->_index_diff($tname, $n_keys, $o_keys, $diff);
			$this->_misc_diff($tname, $n_misc, $o_misc, $diff);

			unset($o_struct[$tname]);
		}

		/** 多余表 */
		foreach ($o_struct as $tname => $struct) {
			$diff[$tname] = 'DROP TABLE '.$tname.';';
		}

		echo "--- diff -----------\n";
		echo implode("\n", $diff);
		echo "\n";
	}

	/**
	 * 获取最新的表结构
	 * @param unknown $dbname
	 * @param unknown $struct
	 * @return boolean
	 */
	protected function _fetch_lastest_struct($dbname, &$struct) {

		/** 读取所有表结构 */
		$content = file_get_contents(APP_PATH.'/docs/'.$dbname.'_structure.sql');
		$content = str_replace("\r", '', $content);
		preg_match_all('/CREATE TABLE(.*?)`(.*?)`\s+\((.*?)\)(.*?);\s+\n/ies', $content, $matches);
		$struct = array();
		foreach ($matches[0] as $_k => $_v) {
			$struct[$matches[2][$_k]] = $_v;
		}

		return true;
	}

	/**
	 * 获取数据结构
	 * @param string $dbname 库名
	 * @param array $struct 结果
	 * @return boolean
	 */
	protected function _fetch_db_struct($dbname, &$struct) {

		$confs = config::get(startup_env::get('app_name').'.db.'.$dbname);
		reset($confs);
		/** 连接数据库 */
		$cfg = current($confs);
		$db = db::init($cfg);

		/** 获取 db table */
		$struct = array();
		$query = $db->query('SHOW TABLES');
		while ($row = $db->fetch_array($query)) {
			/** 获取表名 */
			$tname = $row['Tables_in_'.$cfg['dbname']];
			$q = $db->query('SHOW CREATE TABLE '.$tname);
			$createT = $db->fetch_array($q);

			$struct[$tname] = $createT['Create Table'];
		}

		return true;
	}

	/**
	 * 杂项比较
	 * @param string $tname
	 * @param array $n_misc
	 * @param array $o_misc
	 * @param array $diff
	 * @return boolean
	 */
	protected function _misc_diff($tname, $n_misc, $o_misc, &$diff) {
		/** 表杂项比对 */
		$diff_misc = array();
		foreach ($n_misc as $_k => $_v) {

			$_ov = $o_misc[$_k];
			unset($o_misc[$_k]);
			/** 判断是否重复 */
			if (!empty($_ov) && $_ov == $n_misc[$_k]) {
				continue;
			}

			if ('COMMENT' == $_k) {
				$diff_misc[] = $_k.'=\''.$_v.'\'';
			} elseif('CHARSET' == $_k) {
				$diff_misc[] = $_k.'DEFAULT CHARACTER SET '.$_v;
			} else {
				$diff_misc[] = $_k.'='.$_v;
			}
		}

		if (!empty($diff_misc)) {
			$diff[$tname.'.misc'] = 'ALTER TABLE '.$tname.' '.implode(' ', $diff_misc).';';
		}

		return true;
	}

	/**
	 * 判断键是否存在
	 * @param string $tname 表名
	 * @param string $key 主键
	 * @return boolean
	 */
	protected function _key_exist($tname, $key) {

		if (!array_key_exists($tname, $this->__deleted_t2f)) {
			return true;
		}

		preg_match_all('/\((.*)\)/ig', str_replace(" ", "", $key), $matches);
		$ks = explode(",", $key);
		foreach ($ks as $_f) {
			$_f = trim($_f, '`');
			if (!in_array($_f, $this->__deleted_t2f[$tname])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 索引的不同
	 * @param string $tname 表名
	 * @param array $n_keys 新键值
	 * @param array $o_keys 旧键值
	 * @param array $diff 比对结果
	 * @return boolean
	 */
	protected function _index_diff($tname, $n_keys, $o_keys, &$diff) {
		/** 表索引比对 */
		//ALTER TABLE `vwxoa`.`oa_common_addressbook` DROP PRIMARY KEY, ADD PRIMARY KEY (`cab_id`)
		//ALTER TABLE `vwxoa`.`oa_common_addressbook` DROP INDEX `m_uid`, ADD INDEX `m_uid` (`m_uid`)
		$kprev = '';
		foreach ($n_keys as $_k => $_v) {

			$_ov = $o_keys[$_k];
			unset($o_keys[$_k]);
			/** 判断是否重复 */
			if (!empty($_ov) && $_ov == $n_keys[$_k]) {
				continue;
			}

			$has_key = $this->_key_exist($tname, $_ov);
			$vs = explode(' ', $_v);
			if ('PRIMARY' == $vs[0]) {
				$drop = empty($_ov) || !$has_key ? '' : 'DROP PRIMARY KEY,';
				$diff[$tname.'.k.'.$_k] = 'ALTER TABLE '.$tname.' '.$drop.' ADD '.$_v.';';
				continue;
			}

			$drop = empty($_ov) || !$has_key ? '' : 'DROP INDEX '.$_k.',';
			if ('UNIQUE' == $vs[0]) {
				$diff[$tname.'.k.'.$_k] = 'ALTER TABLE '.$tname.' '.$drop.' ADD UNIQUE '.substr($_v, 10).';';
				continue;
			}

			$diff[$tname.'.k.'.$_k] = 'ALTER TABLE '.$tname.' '.$drop.' ADD INDEX '.substr($_v, 4).';';
		}

		foreach ($o_keys as $_k => $_v) {
			if (!$this->_key_exist($tname, $_v)) {
				continue;
			}

			$diff[$tname.'.kdrop.'.$_k] = 'ALTER TABLE '.$tname.' DROP INDEX '.$_k.';';
		}

		return true;
	}

	/**
	 * 字段的不同
	 * @param array $n_fields 新字段
	 * @param array $o_fields 旧字段
	 * @param array $diff 不同
	 * @return boolean
	 */
	protected function _field_diff($tname, $n_fields, $o_fields, &$diff) {
		$fprev = '';
		foreach ($n_fields as $_k => $_v) {

			$_ov = $o_fields[$_k];
			unset($o_fields[$_k]);
			/** 判断是否重复 */
			if (!empty($_ov) && $_ov == $n_fields[$_k]) {
				$fprev = $_k;
				continue;
			}

			$ac = empty($_ov) ? 'ADD' : 'CHANGE';
			$diff[$tname.'.f.'.$_k] = 'ALTER TABLE '.$tname.' '.$ac.('ADD' == $ac ? '' : ' '.$_k).' '.$_v;
			if (!empty($fprev)) {
				$diff[$tname.'.f.'.$_k] .= ' AFTER '.$fprev;
			} else {
				$diff[$tname.'.f.'.$_k] .= ' FIRST';
			}

			$diff[$tname.'.f.'.$_k] .= ';';
			$fprev = $_k;
		}

		foreach ($o_fields as $_k => $_v) {
			$diff[$tname.'.fdrop.'.$_k] = 'ALTER TABLE '.$tname.' DROP '.$_k.';';
			$this->__deleted_t2f[$tname][] = $_k;
		}

		return true;
	}

	/**
	 * 获取字段/索引/表信息
	 * @param array $rows 表结构信息
	 * @param array $fields 字段
	 * @param array $keys 索引
	 * @param array $misc 表信息
	 * @return boolean
	 */
	protected function _fetch_fkm($rows, &$fields, &$keys, &$misc) {

		foreach ($rows as $_v) {
			$_v = trim($_v, ' ,');
			if (preg_match('/^`(.*?)`(.*?)$/ies', $_v, $match)) {
				$fields[$match[1]] = $_v;
				continue;
			}

			// PRIMARY KEY (`cab_id`),
			if (preg_match('/KEY \(?`(.*?)`\)?(.*?)$/is', $_v, $match)) {
				$keys[$match[1]] = $_v;
				continue;
			}

			//ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公司通讯录';
			if (preg_match('/ENGINE=(\w+)/is', $_v, $match)) {
				$misc['ENGINE'] = $match[1];
			}

			if (preg_match('/CHARSET=([0-9a-zA-Z]+)/is', $_v, $match)) {
				$misc['CHARSET'] = $match[1];
			}

			if (preg_match('/COMMENT\=\'(.*?)\'/is', $_v, $match)) {
				$misc['COMMENT'] = $match[1];
			}
		}

		return true;
	}
}
