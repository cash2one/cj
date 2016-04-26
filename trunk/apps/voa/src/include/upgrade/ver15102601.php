<?php

/**
 * voa_upgrade_ver2015102601
 * $Author$
 * $Id$
 */
class voa_upgrade_ver15102601 extends voa_upgrade_base {

	public function __construct() {

		parent::__construct();
		$this->_ver = '15102601';
	}
	
	// 升级
	public function upgrade() {
		
		// 应用组表
		$q = $this->_db->query("SHOW FIELDS FROM oa_common_plugin_group LIKE 'pay_type'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("ALTER TABLE `oa_common_plugin_group`
ADD `pay_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:标准产品,2:定制产品,3:私有部署' AFTER `cpg_ordernum`,
ADD `pay_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:已付费;2:试用期' AFTER `pay_type`,
ADD `date_start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始日期' AFTER `pay_type`,
ADD `date_end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '截止日期' AFTER `date_start`,
ADD `stop_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否关闭状态:0,不是; 1, 关闭' AFTER `date_end`");
		}
		
		// cpmenu表
		$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_module = 'system' AND ccm_operation = 'message'");
		if (! $row = $this->_db->fetch_row($q)) {
			$this->_db->query("INSERT INTO `oa_common_cpmenu` ( `cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`)
VALUES ('0', '1', 'system', 'message', '', 'operation', '0', '消息中心', '', '1', '555', '0', '1', '0', '0', '0'),
('0', '1', 'system', 'message', 'list', 'subop', '1', '未读消息', 'fa-list', '1', '556', '1', '1', '0', '0', '0'),
('0', '1', 'system', 'message', 'old', 'subop', '0', '已读消息', 'fa-eye', '1', '557', '1', '1', '0', '0', '0'),
('0', '1', 'system', 'message', 'view', 'subop', '0', '消息详情', 'fa-eye', '1', '9558', '0', '1', '0', '0', '0')");
		}
		
		// 清理缓存
		$this->_cache_clear();
		
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
