<?php
/**
 * voa_h_conf
 * $Author$
 * $Id$
 */

class voa_h_conf {

	/** 根据二级域名获取企业数据库配置 */
	public static function init_db() {
		$domain = startup_env::get('domain');
		if (empty($domain)) {
			$domain = voa_h_func::get_domain();
			/** 把当前二级域名加入全局变量 */
			startup_env::set('domain', $domain);
		}

		/** 获取企业缓存的路径 */
		$sitedir = voa_h_func::get_sitedir($domain);
		$file = $sitedir.'dbconf.inc.php';

		$conf = array();
		if (!is_file($file)) {
			return false;
		}

		require_once $file;
		/** 获取应用名 */
		$app_name = startup_env::get('app_name');
		$dbs = config::get($app_name.'.db.oa');
		$dbs[0]['host'] = $conf['host'];
		$dbs[0]['dbname'] = $conf['dbname'];
		$dbs[0]['user'] = $conf['user'];
		$dbs[0]['pw'] = $conf['pw'];
		config::set($app_name.'.db.oa', $dbs);

		/** orm db */
		$orm_dbs = array(
			array(
				'dsn' => 'mysql:dbname='.$conf['dbname'].';host='.$conf['host'].';port=3306',
				'failover' => 'mysql:dbname='.$conf['dbname'].';host='.$conf['host'].';port=3306',
				'timeout' => 5,
				'user' => $conf['user'],
				'password' => $conf['pw'],
				'charset' => 'utf8',
				'tablepre' => 'oa_',
				'persistent' => false
			)
		);
		config::set($app_name.'.db.orm_oa', $orm_dbs);

		return true;
	}

	/**
	 * 设置 db 配置
	 * @param array $sets 配置信息
	 */
	public static function update_dbconf($sets) {
		/** 获取应用名 */
		$app_name = startup_env::get('app_name');
		$dbs = config::get($app_name.'.db.oa');
		//$dbs[0]['host'] = $company['c_dbhost'].':'.$company['c_dbport'];
		$dbs[0]['host'] = $sets['dbhost'];
		$dbs[0]['dbname'] = 'oa_db_'.$sets['cid'];
		$dbs[0]['user'] = 'oa_dbu_'.$sets['cid'];
		$dbs[0]['pw'] = $sets['dbpw'];
		config::set($app_name.'.db.oa', $dbs);

		/** 获取企业缓存的路径 */
		$sitedir = voa_h_func::get_sitedir($sets['domain']);
		/** 写入文件缓存 */
		$conf = array();
		$file = $sitedir.'dbconf.inc.php';
		if (is_file($file)) {
			require_once $file;
		}

		$cfg = config::get($app_name.'.db.oa.0');
		/** 如果配置文件和当前数据相同, 则 */
		if ($cfg['host'] == $conf['host'] && $cfg['pw'] == $conf['pw']) {
			return true;
		}

		rfwrite($file, "<?php\n//wbs! cache file, DO NOT modify me!\n//Created on ".rgmdate("M j, Y, G:i")."\n\n\$conf = ".rvar_export($cfg).";\n\n");
	}
}
