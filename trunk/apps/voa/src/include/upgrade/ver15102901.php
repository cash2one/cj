<?php
/**
 * voa_upgrade_ver15102901
 * $Author$
 * $Id$
 */

class voa_upgrade_ver15102901 extends voa_upgrade_base {

	/** 当前升级的应用信息 */
	private $__plugin = array();

	public function __construct() {

		parent::__construct();
		$this->_ver = '15102901';
	}

	// 升级
	public function upgrade() {

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='news' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		$row = $this->_db->query("SHOW TABLES LIKE 'oa_news'");
		if ($this->_db->fetch_row($row)) {
			// 应用表结构升级
			$this->_plugin_table();
		}

		// 清理缓存
		$this->_cache_clear();

		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

		// 隐藏权限菜单
		$query_pos = $this->_db->query("SELECT * FROM `oa_common_cpmenu` WHERE ccm_module = 'office' AND ccm_operation = 'news' AND ccm_subop = 'issue'");

		if ($result = $this->_db->fetch_row($query_pos)) {

			$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_display` = '0' WHERE `ccm_id` = ".$result[0]);
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
