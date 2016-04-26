<?php

/**
 * voa_upgrade_ver15101201
 * $Author$
 * $Id$
 */
class voa_upgrade_ver15101201 extends voa_upgrade_base {

	/**
	 * 当前升级的应用信息
	 */
	private $__plugin = array();

	public function __construct() {

		parent::__construct();
		$this->_ver = '15101201';
	}
	
	// 升级
	public function upgrade() {
		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
		if ($this->_db->fetch_row($query)) {
			
			// 更新所有签到状态为0的数据为1
			$this->_db->query("UPDATE oa_sign_record SET sr_sign = 1 WHERE sr_sign = 0");
			
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
