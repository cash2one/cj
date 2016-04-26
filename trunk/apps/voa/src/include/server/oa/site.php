<?php
/**
 * voa_server_oa_site
 * OA企业站站点的操作类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_oa_site {

	protected $_domain = '';

	/** 是否开启调试日志，开启后，所有数据将会写入日志，正式运行不允许开启！会导致日志文件过大！ */
	protected $_debug_open = false;

	public function __construct() {
	}

	/**
	 * 开通企业站
	 * 建库、建表
	 * @param array $params
	 * + enterprise // 企业信息
	 *  + domain // 域名
	 *  + name // 企业名称
	 *  + ep_id // 企业ID
	 *  + ep_wxqy // 是否启用了微信企业号
	 * + adminer // 管理员信息
	 *  + mobilephone // 管理员手机号
	 *  + realname // 管理员姓名
	 *  + email // 管理员邮箱
	 *  + password // 管理员密码
	 * + dbhost // OA企业站数据库主机信息
	 *  + dbhost // 主机名
	 *  + dbname // 数据库名
	 *  + dbuser // 数据库用户名
	 *  + dbpw // 数据库密码
	 *  + lanip // 内网IP
	 * + dbadmin // OA企业站数据库管理员账号
	 *  + host // 主机名
	 *  + user // 用户名
	 *  + pw // 密码
	 * @return boolean
	 */
	public function open($params) {

		logger::error('install begin: ' . var_export($params, true));
		if (empty($params)) {
			throw new rpc_exception('rpc call params is null', 1001);
		}
		foreach (array('enterprise', 'dbhost', 'dbadmin', 'adminer') as $k) {
			if (!isset($params[$k])) {
				throw new rpc_exception('params "'.$k.'" not set', 1004);
			}
		}

		$this->_debug('params:'.print_r($params, true));

		// 需要的参数
		$domain = $params['enterprise']['domain'];
		$dbhost = $params['dbhost'];
		$dbadmin = $params['dbadmin'];
		$enterprise = $params['enterprise'];
		$adminer = $params['adminer'];

		$this->_domain = $domain;

		// 当前站点缓存目录
		$sitedir = voa_h_func::get_sitedir(voa_h_func::get_domain($domain));

		// 连接数据库，使用该DB主机的管理用户
		$cfg = config::get('voa.db.dbadmin');
		$cfg = array(
			'host' => $dbadmin['host'],
			'user' => $dbadmin['user'],
			'pw' => $dbadmin['pw'],
			'charset' => $cfg['charset'],
			'pconnect' => $cfg['pconnect'],
			'dbname' => '',
			'tablepre' => $cfg['tablepre']
		);
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);
		logger::error('connect ok: ' . var_export($cfg, true));

		/*
		 * 1.检查数据库是否存在
		 * 2.建库、授权数据库用户、设置数据库密码、刷新数据库权限
		 * 3.建表、导入默认数据
		 * 4.更新系统设置变量：写入common_setting 数据
		 * 5.检查并写入默认部门数据
		 * 6.写入通讯录表数据
		 * 7.写入用户表数据
		 * 8.检查并写入默认最高管理组数据
		 * 9.更新管理员表数据
		 * 10.更新通讯录表 、 通讯录搜索表
		 * 11.建立数据库配置缓存文件
		 */


		// 检查数据库是否存在
		$databases = $db->fetch_first("SHOW DATABASES LIKE '{$dbhost['dbname']}'");
		if (!empty($databases)) {
			throw new rpc_exception($dbhost['dbname'].' database is exist.', 1003);
		}

		logger::error('database ' . $dbhost['dbname']);
		// 使用事务
		try {

			$db->begin();

			/** 开始建库、创建用户、授权、刷新权限 */
			$sqls = array(
					array(
							"DROP DATABASE IF EXISTS {$dbhost['dbname']}",
							"清除数据库[{$dbhost['dbname']}]失败",
							1005
			),
			array(
					"CREATE DATABASE {$dbhost['dbname']} DEFAULT CHARACTER SET {$cfg['charset']}",
					"创建数据库[{$dbhost['dbname']}]失败",
					1007
			),
			array(
					"GRANT ALL PRIVILEGES ON {$dbhost['dbname']}.* TO '{$dbhost['dbuser']}'@'{$dbhost['lanip']}' IDENTIFIED BY '{$dbhost['dbpw']}'",
					"授权错误dbname={$dbhost['dbname']}|dbuser={$dbhost['dbuser']},dbpw={$dbhost['dbpw']},lan={$dbhost['lanip']}",
					1009
			),
			array(
					"UPDATE mysql.user SET password=PASSWORD('{$dbhost['dbpw']}') WHERE user='{$dbhost['dbuser']}'",
					"更新 mysql 用户密码错误dbuser={$dbhost['dbuser']},dbpw={$dbhost['dbpw']}",
					1011
			),
			array(
					"FLUSH PRIVILEGES",
					"刷新数据库权限失败",
					1013
			),
			);
			foreach ($sqls as $s) {
				if (!$db->query($s[0])) {
					throw new rpc_exception($s[1], $s[2]);
				}
			}
			/** /////////结束建库 */

			$db->select_db($dbhost['dbname']);

			/** 开始建表、导入默认数据 */
			// 找到公共表结构文件，建表
			$file_structure = APP_PATH.'/docs/oa_structure.sql';
			if (!is_file($file_structure)) {
				throw new rpc_exception('找不到公共表结构文件', 1015);
			}
			$structure_sql = file_get_contents($file_structure);
			$this->_run_query($structure_sql, $dbhost['dbname'], $db);

			// 找到默认数据，导入
			$file_data = APP_PATH.'/docs/oa_data.sql';
			if (!is_file($file_data)) {
				throw new rpc_exception('找不到默认表数据文件', 1017);
			}
			$data_sql = file_get_contents($file_data);
			$this->_run_query($data_sql, $dbhost['dbname'], $db);
			/** /////// 结束建表、导入默认数据 */

			// 构造初始化的数据表数据
			$req = controller_request::get_instance();

			/** 写入系统设置变量、值 $settings */
			// 随机生成加密密钥
			$authkey = substr(md5($req->server('SERVER_ADDR').$req->server('HTTP_USER_AGENT')
					.$dbhost['dbhost'].$dbhost['dbuser'].$dbhost['dbpw'].$dbhost['dbname']
					.$adminer['mobilephone'].$adminer['password']
					.substr(startup_env::get('timestamp'), 0, 6)), 8, 6).random(10);
			// 系统设置项
			$settings = array(
				'appname' => $enterprise['name'],// 应用名
				'authkey' => $authkey,// 加密密钥
				'domain' => $domain, // 站点域名
				'sitename' => $enterprise['name'], // 企业名称
				'ep_id' => $enterprise['ep_id'], // 企业ID
				'ep_wxqy' => voa_d_oa_common_setting::WXQY_AUTH,//$enterprise['ep_wxqy'] ? voa_d_oa_common_setting::WXQY_AUTH : 0, // 是否启用了微信企业帐号
				'corp_id' => '', // 企业微信 corp_id
				'corp_secret' => '', // 企业微信 corp_secret
				'qrcode' => '',// 二维码地址
				'dbpw' => $dbhost['dbpw'],
				'dbname' => $dbhost['dbname'],
				'dbuser' => $dbhost['dbuser'],
				'openid' => '', // 企业号的openid，同corp_id一致
				'token' => md5(startup_env::get('timestamp').random(10)), // 微信接口token
				'register_time' => startup_env::get('timestamp'),// 企业开通时间
			);
			// 构造分析出数据库主机名和端口
			list($settings['dbhost'], $settings['dbport']) = explode(':', $dbhost['dbhost'].':');
			// 找到数据表内存在的设置
			$settings_db = array();
			$query = $db->query("SELECT `cs_value`, `cs_key` FROM `{$tablepre}common_setting`");
			while ($row = $db->fetch_array($query)) {
				$settings_db[$row['cs_key']] = $settings_db[$row['cs_value']];
			}
			// 执行更新 setting
			foreach ($settings as $_key => $_value) {
				if (isset($settings_db[$_key])) {
					$db->query("UPDATE `{$tablepre}common_setting` SET `cs_value`=".db_help::quote($_value)." WHERE `cs_key`=".db_help::quote($_key)."");
				} else {
					$db->query("REPLACE INTO `{$tablepre}common_setting` (`cs_key`,`cs_value`) VALUES (".db_help::quote($_key).", ".db_help::quote($_value).")");
				}
			}
			/** ///////结束系统设置变量与值的写入 */

			/** 设置默认的部门 $department */
			// 找到默认的部门ID，
			$department = $db->fetch_first("SELECT * FROM `{$tablepre}common_department` ORDER BY `cd_id` ASC LIMIT 1");
			if (empty($department)) {
				// 如没有找到则写入一个默认值
				$department = array(
						'cd_upid' => 0,
						'cd_name' => '默认部门',
						'cd_usernum' => 0,
						'cd_qywxid' => 1,
						'cd_qywxparentid' => 1,
						'cd_status' => voa_d_oa_common_department::STATUS_NORMAL,
						'cd_created' => startup_env::get('timestamp'),
				);
				$this->_debug('department:'.print_r($department, true));
				$db->query("INSERT INTO `{$tablepre}common_department` (".implode(',', db_help::quote_field(array_keys($department))).")
						VALUES (".implode(',', db_help::quote(array_values($department))).")");
				$department['cd_id'] = $db->insert_id();
			}
			/** ///////结束默认部门的设置 */

			/** 写入通讯录数据 $addressbook */
			$pinyin = new pinyin();
			/**$addressbook = array(
					'cab_mobilephone' => $adminer['mobilephone'],
					'cab_realname' => $adminer['realname'],
					'cab_email' => $adminer['email'],
					'cab_index' => $pinyin->to_ucwords_first($adminer['realname'], 4),
					'cd_id' => $department['cd_id'],
					'cab_active' => 1,
					'cab_status' => voa_d_oa_common_addressbook::STATUS_NORMAL,
					'cab_created' => startup_env::get('timestamp'),
			);
			$this->_debug('addressbook:'.print_r($addressbook, true));
			// 写入表
			$db->query("INSERT INTO `{$tablepre}common_addressbook` (".implode(',', db_help::quote_field(array_keys($addressbook))).")
					VALUES (".implode(',', db_help::quote(array_values($addressbook))).")");
			$addressbook['cab_id'] = $db->insert_id();
			$addressbook['m_openid'] = $addressbook['cab_id'];*/
			/** /////// 结束通讯录写入 */

			// 生成用户的加密密码和密码盐值
			$new_password = $new_salt = '';
			list($new_password, $new_salt) = voa_h_func::generate_password($adminer['password'], null, true, 6);

			/** 添加用户到用户表 $member */
			/*
			$member = array(
				//'cab_id' => $addressbook['cab_id'],
				'm_openid' => random(32),
				'm_username' => $adminer['realname'],
				'm_index' => $pinyin->to_ucwords_first($adminer['realname'], 4),
				'm_mobilephone' => $adminer['mobilephone'],
				'cd_id' => $department['cd_id'],
			);
			// 生成用户表的密码字符串和散列值
			$member['m_password'] = $new_password;
			$member['m_salt'] = $new_salt;
			$this->_debug('member:'.print_r($member, true));
			// 写入表
			$db->query("REPLACE INTO `{$tablepre}member` (".implode(',', db_help::quote_field(array_keys($member))).")
					VALUES (".implode(',', db_help::quote(array_values($member))).")");
			$member['m_uid'] = $db->insert_id();

			// 用户扩展表 $member_field
			$member_field = array(
				'm_uid' => $member['m_uid'],
				'mf_status' => voa_d_oa_member_field::STATUS_NORMAL,
				'mf_created' => startup_env::get('timestamp')
			);
			$db->query("REPLACE INTO `{$tablepre}member_field` (".implode(',', db_help::quote_field(array_keys($member_field))).")
					VALUES (".implode(',', db_help::quote(array_values($member_field))).")");
			$this->_debug('member_field:'.print_r($member_field, true));
			*/
			/** /////// 结束用户数据插入 */

			/** 检查最高权限管理组信息 $adminergroup */
			$adminergroup = $db->fetch_first("SELECT * FROM `{$tablepre}common_adminergroup` WHERE
				`cag_enable`=".voa_d_oa_common_adminergroup::ENABLE_SYS." ORDER BY `cag_id` ASC LIMIT 1");
			if (!$adminergroup) {
				$adminergroup = array(
					'cag_title' => '系统管理组',
					'cag_enable' => voa_d_oa_common_adminergroup::ENABLE_SYS,
					'cag_role' => serialize(array()),
					'cag_description' => '系统最高权限组',
					'cag_status' => voa_d_oa_common_adminergroup::STATUS_NORMAL,
					'cag_created' => startup_env::get('timestamp'),
				);
				$this->_debug('adminergroup:'.print_r($adminergroup, true));
				$db->query("INSERT INTO `{$tablepre}common_adminergroup` (".implode(',', db_help::quote_field(array_keys($adminergroup))).")
						VALUES (".implode(',', db_help::quote(array_values($adminergroup))).")");
				$adminergroup['cag_id'] = $db->insert_id();
			}
			/** //////// 结束管理员组检查*/

			/** 写入或更新管理员信息 $cp_adminer */
			$cp_adminer = array(
				'ca_mobilephone' => $adminer['mobilephone'],
				'ca_email' => isset($adminer['email']) ? $adminer['email'] : '',
				'ca_username' => $adminer['realname'],
				'cag_id' => $adminergroup['cag_id'],
				'ca_locked' => voa_d_oa_common_adminer::LOCKED_SYS,
				//'ca_realname' => $adminer['realname'],
				//'ca_mobilephone' => $adminer['mobilephone'],
				//'m_uid' => $member['m_uid'],
			);
			$this->_debug('cp_adminer:'.print_r($cp_adminer, true));
			$cp_adminer['ca_password'] = $new_password;
			$cp_adminer['ca_salt'] = $new_salt;
			// 尝试找到系统管理员
			$old_adminer = $db->fetch_first("SELECT * FROM `{$tablepre}common_adminer` WHERE
				`ca_locked`='".voa_d_oa_common_adminer::LOCKED_SYS."' ORDER BY `ca_id` ASC LIMIT 1");
			if ($old_adminer) {
				// 存在一个管理员帐号，则更新
				$update_adminer_fields = $comma = '';
				foreach ($cp_adminer as $_key => $_value) {
					$update_adminer_fields .= $comma.db_help::field($_key, $_value);
					$comma = ',';
				}
				$db->query("UPDATE `{$tablepre}common_adminer` SET {$update_adminer_fields} WHERE `ca_id`='{$old_adminer['ca_id']}'");
			} else {
				// 不存在系统管理员，则将开通者信息写入做为系统管理员
				$db->query("INSERT INTO `{$tablepre}common_adminer` (".implode(',', db_help::quote_field(array_keys($cp_adminer))).")
						VALUES (".implode(',', db_help::quote(array_values($cp_adminer))).")");
				$cp_adminer['ca_id'] = $db->insert_id();
			}
			/** ////// 完成管理员更新 */

			/** 更新通讯录表 以及 通讯录搜索表 $adminer_search */
			/**$db->query("UPDATE `{$tablepre}common_addressbook` SET `m_openid`='{$addressbook['cab_id']}' WHERE `cab_id`='{$addressbook['cab_id']}'");
			$cabs_message = array(
				$addressbook['cab_realname'], $addressbook['cab_mobilephone'], $addressbook['cab_email']
			);
			$adminer_search = array(
				'cab_id' => $addressbook['cab_id'],
				'm_uid' => $member['m_uid'],
				'cabs_message' => implode("\n", $cabs_message),
				'cabs_status' => voa_d_oa_common_addressbook::STATUS_NORMAL,
				'cabs_created' => startup_env::get('timestamp'),
			);
			$this->_debug('adminer_search:'.print_r($adminer_search, true));
			$db->query("REPLACE `{$tablepre}common_addressbook_search` (".implode(',', db_help::quote_field(array_keys($adminer_search))).")
				VALUES (".implode(',', db_help::quote(array_values($adminer_search))).")");*/
			/** ////// 完成通讯录表以及通讯录搜索表更新 */

			// 将管理员信息写入到uc管理员表
			$timestamp = startup_env::get('timestamp');
			$uc_cfg = config::get('voa.db.uc');
			$uc_cfg = $uc_cfg[0];
			$db_uc = &db::init($uc_cfg);
			$sql = "INSERT INTO `{$uc_cfg['dbname']}`.`{$uc_cfg['tablepre']}enterprise_adminer`
					(`ep_id`, `ca_id`, `realname`, `mobilephone`, `userstatus`, `password`, `salt`, `status`, `created`, `updated`)
					VALUES ({$enterprise['ep_id']}, {$cp_adminer['ca_id']}, '{$adminer['realname']}', '{$cp_adminer['ca_mobilephone']}',
							 1, '{$new_password}', '{$new_salt}', 1, {$timestamp}, {$timestamp})";
			$db_uc->query($sql);

			$db->commit();

		} catch (Exception $e) {
			$db->rollback();
			logger::error($e);
			throw new rpc_exception('执行开通建立企业站点操作失败', 10010);
		}

		/** 写入文件缓存 */
		$file = $sitedir.'dbconf.inc.php';
		$conf = array(
				'host' => $dbhost['dbhost'],
				'dbname' => $dbhost['dbname'],
				'user' => $dbhost['dbuser'],
				'pw' => $dbhost['dbpw']
		);
		rfwrite($file, "<?php\n//wbs! cache file, DO NOT modify me!\n//Created on ".rgmdate("M j, Y, G:i")."\n\n\$conf = ".rvar_export($conf).";\n\n");

		// 返回结果，如果有需要uc和主站的数据，在这里添加
		return array('m_uid' => 0);
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
