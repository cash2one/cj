<?php
/**
 * 企业 oa 内部接口
 * $Author$
 * $Id$
 */

class voa_server_oa_oa {

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}
	}

	/**
	 * 企业 oa 开通操作
	 * @param array $args 用户信息
	 *  + openid 用户openid
	 */
	public function open($args) {
		if (empty($args) || !is_array($args)) {
			throw new rpc_exception('args is empty.', 100);
		}

		$req = controller_request::get_instance();
		/** 过滤数据 */
		$args = raddslashes($args);

		/** 用户名/密码/域名/uid(ucenter) */
		$username = trim($args['username']);
		$password = trim($args['password']);
		$domain = trim($args['domain']);
		$cid = intval($args['cid']);
		/** 主机/端口 */
		$dbhost = trim($args['dbhost']);
		$dbport = trim($args['dbport']);
		$dbpw = trim($args['dbpw']);
		/** 数据库名称/用户/密码 */
		$dbname = 'oa_db_'.$cid;
		$dbuser = 'oa_dbu_'.$cid;
		/** 公司名称 */
		$company = trim($args['company']);
		/** 判断参数是否缺失 */
		if (empty($username) || empty($password) || empty($domain)
				|| empty($cid) || empty($dbhost) || empty($dbport)) {
			throw new rpc_exception('args error.'.var_export($args, true), 101);
		}

		/** 获取企业缓存的路径 */
		$sitedir = voa_h_func::get_sitedir(voa_h_func::get_domain($domain));

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		/** 判断数据库是否存在 */
		$databases = $db->fetch_first("SHOW DATABASES LIKE '{$dbname}'");
		if (!empty($databases)) {
			throw new rpc_exception($dbname.' database is exist.', 102);
		}

		/** 创建数据库 */
		$selfip = config::get('voa.db.selfip');
		$db->query("DROP DATABASE IF EXISTS $dbname;") or die('清除数据库'.$dbname.'失败');
		$db->query("CREATE DATABASE $dbname DEFAULT CHARACTER SET UTF8") or die('创建数据库'.$dbname.'失败');
		$db->query("GRANT ALL PRIVILEGES ON $dbname.* to '$dbuser'@'$selfip' IDENTIFIED BY '$dbpw';") or die('授权错误$dbname='.$dbname.'|$dbuser|='.$dbuser.'|$selfip='.$selfip.'|$dbpw='.$dbpw);
		$db->query("UPDATE mysql.user SET password=Old_PASSWORD('$dbpw') WHERE user='$dbuser';") or die('更新mysql用户密码错误$dbuser='.$dbuser.'|$dbpw='.$dbpw);
		$db->query("FLUSH PRIVILEGES") or die('刷新权限 FLUSH PRIVILEGES');

		/** 获取标准 sql */
		$sql_file = APP_PATH.'/docs/structure.sql';
		$fp = fopen($sql_file, 'rb');
		$struct = fread($fp, filesize($sql_file));
		fclose($fp);
		/** 运行 sql */
		$this->_run_query($struct, $dbname, $db);

		/** 数据初始化 */
		$data_file = APP_PATH.'/docs/data.sql';
		$fp = fopen($data_file, 'rb');
		$data = fread($fp, filesize($data_file));
		fclose($fp);
		/** 入库 */
		$this->_run_query($data, $dbname, $db);

		/** 随机生成加密密钥 */
		$authkey = substr(md5($req->server('SERVER_ADDR').$req->server('HTTP_USER_AGENT').$dbhost.$dbuser.$dbpw.$dbname.$username.$password.substr(startup_env::get('timestamp'), 0, 6)), 8, 6).random(10);
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$authkey}' WHERE cs_key='authkey'");
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$domain}' WHERE cs_key='domain'");
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$company}' WHERE cs_key='sitename'");
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$cid}' WHERE cs_key='cid'");
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$dbhost}' WHERE cs_key='dbhost'");
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$dbport}' WHERE cs_key='dbport'");
		$db->query("UPDATE {$tablepre}common_setting SET cs_value='{$dbpw}' WHERE cs_key='dbpw'");

		/** 管理员 */
		$db->query("REPLACE INTO {$tablepre}common_adminer(ca_id, ca_username, ca_password, ca_locked) VALUES(1, '{$username}', '{$password}', 0)");

		/** 写入文件缓存 */
		$file = $sitedir.'dbconf.inc.php';
		$conf = array(
			'host' => $dbhost,
			'dbname' => $dbname,
			'user' => $dbuser,
			'pw' => $dbpw
		);

		rfwrite($file, "<?php\n//wbs! cache file, DO NOT modify me!\n//Created on ".rgmdate("M j, Y, G:i")."\n\n\$conf = ".rvar_export($conf).";\n\n");

		return $cid;
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
					$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
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
		$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
		$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'INNODB';
		return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
			(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=UTF8" : " TYPE=$type"
		);
	}
}
