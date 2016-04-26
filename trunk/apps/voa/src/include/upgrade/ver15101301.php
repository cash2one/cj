<?php
/**
 * @Author: ppker
 * @Date:   2015-10-13 11:04:34
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-13 11:31:19
 */

class voa_upgrade_ver15101301 extends voa_upgrade_base {

	/**
	 * 当前升级的应用信息
	 */
	private $__plugin = array();

	public function __construct() {

		parent::__construct();
		$this->_ver = '15101301';
	}

	// 升级
	public function upgrade() {

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
		if (!$this->_db->fetch_row($query)) {
			return true;
		}

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_alert'");
		if (!$this->_db->fetch_row($query)) {

			$this->_db->query("CREATE TABLE `oa_sign_alert` (
				  `said` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '提醒记录id',
				  `batch_id` int(10) unsigned NOT NULL COMMENT '班次ID',
				  `alert_time` int(10) unsigned NOT NULL COMMENT '提醒记录时间',
				  `type` tinyint(1) unsigned NOT NULL COMMENT '提交类型（1 => 上班, 0 => 下班）',
				  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
				  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
				  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
				  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
				  PRIMARY KEY (`said`),
				  KEY `batch_id` (`batch_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到提醒记录表'");

			// 清理缓存
			$this->_cache_clear();
		}

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {
		// 获取用户旧数据
		$sql = "select * from oa_common_setting";
		$query = $this->_db->query($sql);
		$info = array();
		while ($row = $this->_db->fetch_array($query)) {
			$info[$row['cs_key']] = $row;
		}
		// 当前站点的缓存目录
		$cachedir = $this->_site_cache_dir($info['domain']['cs_value']);

		// 清理应用信息缓存
		@unlink($cachedir . DIRECTORY_SEPARATOR . 'plugin.php');
		// 试图清理培训应用的设置缓存
		@unlink($cachedir . DIRECTORY_SEPARATOR . 'plugin.superreport.setting.php');

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir . DIRECTORY_SEPARATOR . $file);
					break;
				}
			}
		}
	}

	/**
	 * 获取指定站点的缓存目录
	 *
	 * @param string $domain
	 * @return string
	 */
	protected function _site_cache_dir($domain) {

		$dir = voa_h_func::get_sitedir(voa_h_func::get_domain($domain));
		startup_env::set('sitedir', null);

		return $dir;
	}

}
