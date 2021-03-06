<?php
/**
 * 新闻公告升级脚本.20160104
 * php -q tool.php -n upgrade -version 20160104 -epid vchangyi_oa
 * User: Muzhitao
 * Date: 2015/11/3 0003
 * Time: 15:33
 * Email：muzhitao@vchangyi.com
 */

error_reporting(E_ALL);
class execute {

	/** 数据库操作对象 */
	protected $_db = null;
	/** 表前缀 */
	protected $_tablepre = 'oa_';
	/** 当前站点系统设置 */
	protected $_settings = array();
	/** 来自命令行请求的参数 */
	protected $_options = array();
	/** 来自触发此脚本的父级参数 */
	protected $_params = array();
	/** 储存已执行的SQL语句，文件路径 */
	protected $_sql_logfile = '';
	/** 储存已执行SQL语句的恢复语句，文件路径 */
	protected $_sql_restore_logfile = '';

	/** 当前升级的应用信息 */
	private $__plugin = array();

	public function __construct() {
	}

	/**
	 * 初始化环境参数
	 * @param object $db 数据库操作对象
	 * @param string $tablepre 表前缀
	 * @param array $settings 当前站点的setting
	 * @param array $options 传输进来的外部参数
	 * @param array $params 一些环境参数，来自触发执行本脚本
	 * @see voa_backend_tool_upgrade::main()
	 */
	public function init($db, $tablepre, $settings, $options, $params) {
		$this->_db = $db;
		$this->_tablepre = $tablepre;
		$this->_settings = $settings;
		$this->_options = $options;
		$this->_params = $params;
	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);
		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='news' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		if ($this->__plugin) {

			// 更新表结构
			if (!$this->_plugin_table()) {
				return false;
			}
			// 微信企业号菜单更新
			$this->_plugin_wxqymenu();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 表结构升级
	 */
	protected function _plugin_table() {

		$query = $this->_db->query("SHOW TABLES LIKE '%oa_news%'");
		if (!$this->_db->fetch_array($query)) {
			return false;
		}

		$columns = array();
		$query = $this->_db->query("SHOW COLUMNS FROM oa_news");
		while ($row = $this->_db->fetch_array($query)) {
			$columns[$row['Field']] = $row;
		}

		if (!isset($columns['is_message'])) {
			$this->_db->query("ALTER TABLE `oa_news` ADD `is_message` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推送消息'");
		}
		$query = $this->_db->query("SELECT * FROM oa_news_setting WHERE `key`='fixed'");
		if (!$row = $this->_db->fetch_array($query)) {
			$this->_db->query("INSERT INTO oa_news_setting (`key`, `value`, `type`, `comment`, `created`) VALUES('fixed', 'a:3:{s:4:\"name\";s:12:\"新建公告\";s:7:\"orderid\";s:1:\"1\";s:7:\"checked\";i:1;}', 1, '固定菜单', '1432871777')");
		}
		if (!isset($columns['send_no_time'])) {
			$this->_db->query("ALTER TABLE `oa_news` ADD `send_no_time` INT(10) UNSIGNED NOT NULL COMMENT '未读人员发送时间' AFTER `read_num`");
		}
		if (!isset($columns['multiple'])) {
			$this->_db->query("ALTER TABLE `oa_news` ADD `multiple` INT(10) UNSIGNED NOT NULL COMMENT '多条时间戳' AFTER `deleted`");
		}
		if (!isset($columns['is_all2'])) {
			$this->_db->query("ALTER TABLE `oa_news` ADD `is_all2` tinyint(1) UNSIGNED NOT NULL COMMENT '评论权限 ' AFTER `deleted`");
		}

		$nc_columns = array();
		$query = $this->_db->query("SHOW COLUMNS FROM oa_news_comment");
		while ($row = $this->_db->fetch_array($query)) {
			$nc_columns[$row['Field']] = $row;
		}
		if (!isset($nc_columns['p_username'])) {
			$this->_db->query("ALTER TABLE `oa_news_comment` ADD `p_username` VARCHAR(100) NOT NULL COMMENT '父级评论回复用户姓名' AFTER `ne_id`");
		}
		if (!isset($nc_columns['m_username'])) {
			$this->_db->query("ALTER TABLE `oa_news_comment` ADD `m_username` VARCHAR(100) NOT NULL COMMENT '顶级评论回复用户姓名' AFTER `ne_id`");
		}
		return true;
	}

	/**
	 * 通过查询数据库更新菜单
	 */
	protected function _plugin_wxqymenu() {

		$domain = $this->_settings['domain'];

		$api_url = 'http://' . $domain . '/api/common/post/databasewxqymenu/';
		// 获取微信菜单
		$menu = $this->_list_categories();
		if ($menu) {
			$menu_data = array();
			$twonodes = false;
			foreach ($menu as $k => $v) {
				if (isset($v['nodes'])) { //如果有子菜单
					$submenu = array();
					foreach ($v['nodes'] as $subv) {
						$submenu[] = array(
							'type' => 'view',
							'name' => $subv['name'],
							'url' => '{domain_url}/frontend/news/list?nca_id=' . $subv['nca_id'] . '&navtitle='.$subv['name'].'&pluginid=' . $this->__plugin['cp_pluginid']
						);
					}
					$menu_data[] = array(
						'name' => $v['name'],
						'sub_button' => $submenu
					);
					$twonodes = true;
				} else { //如果没有子菜单
					$menu_data[] = array(
						'type' => 'view',
						'name' => $v['name'],
						'url' => '{domain_url}/frontend/news/list?nca_id=' . $v['nca_id'] . '&navtitle='.$v['name'].'&pluginid=' . $this->__plugin['cp_pluginid']
					);
				}
			}
		}

		if ($twonodes) {
			return true;
		}
		$new = array(
			'type' => 'view',
			'name' => '新建公告',
			'url' => '{domain_url}/frontend/news/template/?pluginid={pluginid}',
		);
		if (count($menu_data) > 2) {
			$new_menu[] = $new;
			$new_menu[] = array(
				'name' => '公告列表',
				'sub_button' => $menu_data,
			);
		} else {
			$new_menu = $menu_data;
			array_unshift($new_menu, $new);
		}

		$post = array(
			'pluginid' =>  $this->__plugin['cp_pluginid'],
			'time' => startup_env::get('timestamp'),
			'hash' => '',
			'data' => $new_menu,
		);

		$result = array();
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url . "||".$this->__plugin['cp_pluginid']."||" . print_r($result, true));
		}

		return true;
	}

	/**
	 * 获取所有类型
	 * @return array
	 */
	protected  function _list_categories() {

		// 获取所有类型
		$query = $this->_db->query("SELECT * FROM `oa_news_category` WHERE `status`<3 ORDER BY orderid ASC");

		//整理输出
		$result = array();
		while ($row = $this->_db->fetch_array($query)) {
			if ($row['parent_id'] == 0) {
				$result[$row['nca_id']]['nca_id'] = $row['nca_id'];
				$result[$row['nca_id']]['name'] = $row['name'];
				$result[$row['nca_id']]['orderid'] = $row['orderid'];
			}
			if ($row['parent_id'] != 0) {
				$result[$row['parent_id']]['nodes'][] = $row;
			}
		}

		//排序
		$orderids = array();
		foreach ($result as &$cat) {
			$orderids[] = $cat['orderid'];
			$suborderids = array();
			if (isset($cat['nodes'])) {
				foreach ($cat['nodes'] as $sub) {
					$suborderids[] = $sub['orderid'];
				}
				array_multisort($suborderids, SORT_ASC, $cat['nodes']);
			}
		}
		array_multisort($orderids, SORT_ASC, $result);
		return $result;
	}

	/**
	 * 读取远程api数据
	 * @param unknown $data
	 * @param unknown $url
	 * @param string $post
	 * @return boolean
	 */
	private function __get_json_by_post(&$data, $url, $post = '') {

		$snoopy = new snoopy();
		$snoopy->proxy_host = '127.0.0.1';
		$snoopy->proxy_port = '80';
		$result = $snoopy->submit($url, $post);
		// 如果读取错误
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);
			return false;
		}

		$data = (array) json_decode($snoopy->results, true);
		if (empty($data) || !empty($data['errcode'])) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $snoopy->results . '|' . $snoopy->status);
			return false;
		}

		if ($data['errcode'] == '45009') {
			// 如果接口请求超限，则稍等10秒重试
			echo '[...wait retry...]';
			sleep(mt_rand(6, 12));
			$data = array();
			return $this->__get_json_by_post($data, $url, $post);
		}

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 清理应用信息缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.php');
		// 试图清理培训应用的设置缓存
		@unlink($cachedir.DIRECTORY_SEPARATOR.'plugin.'.$this->__plugin['cp_identifier'].'.setting.php');

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir.DIRECTORY_SEPARATOR.$file);
					break;
				}
			}
		}
	}
}

//end
