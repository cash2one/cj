<?php

/**
 * 培训应用迭代.20160314
 * php -q tool.php -n upgrade -version 20160314 -epid vchangyi_oa
 * User: wowxavi
 * Date: 2016/03/14
 * Time: 15:33
 * Email: wowxavi@qq.com
 */
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
	 * @param array  $settings 当前站点的setting
	 * @param array  $options 传输进来的外部参数
	 * @param array  $params 一些环境参数，来自触发执行本脚本
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

		$timestamp = startup_env::get('timestamp');
		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='train' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 读取培训信息
		$this->_db->query("UPDATE oa_common_plugin SET cp_pluginid=45,cp_suiteid='',cp_agentid='',cp_available=255 WHERE cp_identifier='train'");
		$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES(25, 'jobtrain', 1, 5, '{$this->__plugin['cp_suiteid']}', '{$this->__plugin['cp_agentid']}', 1043, {$this->__plugin['cp_available']}, 0, '培训', 'jobtrain.png', '企业培训功能', 'jobtrain*', 'jobtrain', 'jobtrain.php', '0.1', 0, 0, 0, 1, 0, 0, 0)");

		// 清理缓存
		$this->_cache_clear();

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_train_category'");
		if ($this->_db->fetch_row($query)) {
			$this->_init_table();

			//获取会员总数
			$e_count = $this->_db->query("SELECT COUNT(m_uid) as count FROM `oa_member` WHERE m_status<3");
			$count = $this->_db->fetch_row($e_count);
			$member_count = $count[0];
			//临时分类下学习总人数存储
			$study_sum_arr = array();
			//升级目录表
			$query = $this->_db->query("SELECT * FROM `oa_train_category` WHERE `status`<3");
			$result = array();
			//分类循环
			while ($row = $this->_db->fetch_array($query)) {
				//读取权限
				$right_query = $this->_db->query("SELECT * FROM `oa_train_category_right` WHERE `tc_id`={$row['tc_id']}");
				$m_uids = $cd_ids = array(0);
				$is_all = 0;
				//学习总人数
				$study_sum = 0;
				while ($right_row = $this->_db->fetch_array($right_query)) {
					if($right_row['is_all']){
						$is_all = 1;
						$study_sum = $member_count;
					}else{
						if($right_row['m_uid']){
							$m_uids[] = $right_row['m_uid'];
						}
						if($right_row['cd_id']){
							$cd_ids[] = $right_row['cd_id'];
						}
					}
					//插入权限
					$this->_db->query("INSERT INTO `oa_jobtrain_right` (`aid`, `cid`, `is_all`, `m_uid`, `cd_id`, `status`, `created`) VALUES (0, {$right_row['tc_id']}, {$right_row['is_all']}, {$right_row['m_uid']}, {$right_row['cd_id']}, 1, {$timestamp});");
				}
        		$m_uids_str = implode(',', $m_uids);
        		$cd_ids_str = implode(',', $cd_ids);
        		//获取文章数
        		$e_count = $this->_db->query("SELECT COUNT(ta_id) as count FROM `oa_train_article`
				 WHERE tc_id = {$row['tc_id']} AND status<3");
				$count = $this->_db->fetch_row($e_count);
				$count = $count[0];
				//插入分类
				$this->_db->query("INSERT INTO `oa_jobtrain_category` (`id`, `title`, `pid`, `orderid`, `is_open`, `is_all`, `cd_ids`, `m_uids`, `article_num`, `status`, `created`) VALUES ({$row['tc_id']}, '".$row['title']."', 0, 0, 1, {$is_all}, '{$cd_ids_str}', '{$m_uids_str}', {$count}, 1, {$timestamp});");
				//统计学习人数
				if($is_all==1){
					$study_sum_arr[$row['tc_id']] = $study_sum;
				}else{
					$where = 'm_status<3 AND ( m_uid IN('.$m_uids_str.') OR cd_id IN('.$cd_ids_str.') )';
					$e_count = $this->_db->query("SELECT COUNT(m_uid) as count FROM `oa_member` WHERE {$where}");
					$count = $this->_db->fetch_row($e_count);
					$study_sum_arr[$row['tc_id']] = $count[0];
				}

			}
			//文章循环
			$query = $this->_db->query("SELECT a.*,b.content,c.ca_username FROM `oa_train_article` a LEFT JOIN `oa_train_article_content` b ON a.ta_id=b.ta_id LEFT JOIN `oa_common_adminer` c ON a.ca_id=c.ca_id WHERE a.status<3");
			$result = array();
			while ($row = $this->_db->fetch_array($query)) {
				//获取学习数
        		$e_count = $this->_db->query("SELECT COUNT(tam_id) as count FROM `oa_train_article_member`
				 WHERE ta_id = {$row['ta_id']} AND status<3");
				$study_num = $this->_db->fetch_row($e_count);
				$study_num = $study_num[0];
				//插入文章
				$this->_db->query("INSERT INTO `oa_jobtrain_article` (`cid`, `type`, `title`, `author`, `m_uid`, `m_username`, `summary`, `preview_summary`, `content`, `is_secret`, `is_comment`, `is_publish`, `publish_time`, `is_loop`, `study_num`, `coll_num`, `status`, `created`, `updated`, `study_sum`) VALUES ({$row['tc_id']}, 0, '".$row['title']."', '', {$row['ca_id']}, '".$row['ca_username']."', '', '', '".$row['content']."', 0, 1, 1, {$timestamp}, 0, {$study_num}, 0, 1, {$timestamp}, {$timestamp}, ".$study_sum_arr[$row['tc_id']].");");
			}
			//学习情况循环
			$query = $this->_db->query("SELECT a.*,b.tc_id FROM `oa_train_article_member` a LEFT JOIN `oa_train_article` b ON a.ta_id=b.ta_id WHERE a.status<3");
			$result = array();
			while ($row = $this->_db->fetch_array($query)) {
				//读取会员
				$e_memeber = $this->_db->query("SELECT a.*,b.cd_name FROM `oa_member` a LEFT JOIN `oa_common_department` b ON a.cd_id=b.cd_id WHERE a.m_uid={$row['m_uid']}");
				$memeber = $this->_db->fetch_row($e_memeber);
				//插入学习情况
				$this->_db->query("INSERT INTO `oa_jobtrain_study` (`aid`, `m_uid`, `m_username`, `department`,`mobile`, `study_time`, `status`, `created`) VALUES ({$row['ta_id']}, {$row['m_uid']}, '".$memeber['m_username']."', '".$memeber['cd_name']."', '".$memeber['m_mobilephone']."', {$row['read_time']}, 1, {$timestamp});");
			}
			// 应用菜单升级
			$this->_plugin_cpmenu();

			// 更新菜单
			$this->_plugin_wxqymenu();
		}

		return true;
	}

	protected function _init_table() {

		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_category` (
  `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID,内容分类ID',
  `title` varchar(30) DEFAULT '' COMMENT '类型名',
  `pid` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父类型ID',
  `orderid` smallint(6) UNSIGNED NOT NULL DEFAULT '1' COMMENT '排序',
  `is_open` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否启用, 1=启用，0=不启用',
  `is_all` tinyint(1) UNSIGNED NOT NULL COMMENT '是否全部用户',
  `cd_ids` varchar(255) NOT NULL COMMENT '部门ids',
  `m_uids` varchar(255) NOT NULL COMMENT '用户ids',
  `article_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '统计文章数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 文章分类表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_article` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID,内容ID',
  `cid` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类型0=文章，1=音图，2=视频',
  `title` varchar(200) DEFAULT '' COMMENT '标题',
  `author` char(54) DEFAULT '' COMMENT '作者',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '创建人用户名',
  `summary` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
  `preview_summary` varchar(255) NOT NULL DEFAULT '' COMMENT '预览说明',
  `content` text NOT NULL COMMENT '内容',
  `attachments` text NOT NULL COMMENT '附件：音图和附件序列化数组',
  `video_id` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '视频id',
  `is_secret` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否保密：0=不保密；1=保密',
  `cover_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封面图片ID',
  `is_comment` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否评论：0=不评论；1=评论',
  `is_publish` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否发布：0=草稿；1=发布',
  `publish_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间',
  `is_loop` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否循环播放音图：0=否；1=是',
  `study_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习人数',
  `study_sum` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习总人数',
  `coll_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏人数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `type` (`type`),
  KEY `is_publish` (`is_publish`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 文章表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_study` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `department` varchar(64) NOT NULL DEFAULT '' COMMENT '部门',
  `job` varchar(64) NOT NULL DEFAULT '' COMMENT '职务',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `study_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习时间',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 学习表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_right` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内容类别ID',
  `is_all` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全部人员可查看：0=不是；1=是',
  `m_uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '人员Id',
  `cd_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `cid` (`cid`),
  KEY `is_all` (`is_all`),
  KEY `m_uid` (`m_uid`),
  KEY `cd_id` (`cd_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 权限表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_coll` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `m_username` char(54) NOT NULL DEFAULT '' COMMENT '用户名',
  `department` varchar(64) NOT NULL DEFAULT '' COMMENT '部门',
  `job` varchar(64) NOT NULL DEFAULT '' COMMENT '职务',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 收藏表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_comment` (
  `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID,评论ID' ,
  `aid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复的文章主题ID',
  `toid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复id',
  `m_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `m_username` char(54) NOT NULL COMMENT '用户名称' ,
  `to_uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复用户ID',
  `to_username` char(54) NOT NULL COMMENT '回复用户名' ,
  `content` text COMMENT '评论内容',
  `zan_num` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `toid` (`toid`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='培训 - 评论表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_comment_zan` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `m_uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞用户uid',
  `comment_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '培训评论ID',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `m_uid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训 - 评论点赞表'");
		$this->_db->query("CREATE TABLE IF NOT EXISTS `oa_jobtrain_setting` (
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训 - 设置表'");
		$this->_db->query("REPLACE INTO `oa_jobtrain_setting` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('perpage',	'10',	0,	'每页显示的培训文章条数',	2,	1401755858,	1401761836,	0),
('pluginid', '45', 0, '插件id', 1, 0, 0, 0),
('agentid', '0', '0', '', '1', '0', '0', '0')");
	}

	/**
	 * 更新微信企业号的自定义菜单
	 */
	protected function _plugin_wxqymenu() {

		$api_url = 'http://'.$this->_settings['domain'].'/api/common/post/updatewxqymenu/';

		$result = array();
		$post = array(
			'pluginid' => $this->__plugin['cp_pluginid'],
			'agentid' => $this->__plugin['cp_agentid'],
			'identifier' => 'jobtrain'
		);
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url."||".print_r($result, true));
		}

		return true;
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
	 * 应用菜单升级
	 */
	protected function _plugin_cpmenu() {

		// 删除旧有的菜单
		$this->_db->query("DELETE FROM `oa_common_cpmenu` WHERE ccm_operation='train'");
		// 添加新的菜单
		$query = $this->_db->query("SELECT ccm_id FROM `oa_common_cpmenu` WHERE ccm_operation='jobtrain' ");
		$menu = $this->_db->fetch_row($query);
		if(!$menu){
			$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
	(25,	0,	'office',	'jobtrain',	'',	'operation',	1,	'培训',	'',	1,	1025,	1,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'add',	'subop',	0,	'添加内容',	'fa-plus',	102,	102,	1,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'cataadd',	'subop',	0,	'添加分类',	'fa-plus',	104,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'catadel',	'subop',	0,	'删除分类',	'fa-trash-o',	107,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'cataedit',	'subop',	0,	'编辑分类',	'fa-edit',	105,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'catalist',	'subop',	0,	'分类设置',	'fa-gear',	103,	103,	1,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'cataview',	'subop',	0,	'查看分类',	'fa-eye',	106,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'collexport',	'subop',	0,	'导出收藏',	'fa-list',	112,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'colllist',	'subop',	0,	'收藏人数',	'fa-eye',	111,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'del',	'subop',	0,	'删除内容',	'fa-trash-o',	110,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'edit',	'subop',	0,	'编辑内容',	'fa-edit',	108,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'list',	'subop',	1,	'知识管理',	'fa-list',	101,	101,	1,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'studyexport',	'subop',	0,	'导出学习',	'fa-list',	114,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'studylist',	'subop',	0,	'学习人数',	'fa-eye',	113,	1025,	0,	1,	1453707636,	1453707636,	0),
	(25,	0,	'office',	'jobtrain',	'view',	'subop',	0,	'知识详情',	'fa-eye',	109,	1025,	0,	1,	1453707636,	1453707636,	0);");
		}
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		$handle = opendir($cachedir);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if (false === stripos($file, 'dbconf.inc.php')) {
					@unlink($cachedir . '/' . $file);
				}
			}
			closedir($handle);
		}

	}

	/**
	 * 运行 sql
	 * @param string $sql sql字串
	 * @param object $db 帐号密码
	 */
	protected function _run_query($sql, &$db) {

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
					$this->_db->query($this->_create_table($query));
				} else {
					$this->_db->query($query);
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

}
