<?php
/**
 * 20150327.php
 * 工作报告（日报）应用迭代
 * php -q tool.php -n upgrade -version 20150327 -epid vchangyi_oa_upgrade
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class execute {

	/**
	 * 数据库操作对象
	 */
	protected $_db = null;
	/**
	 * 表前缀
	 */
	protected $_tablepre = 'oa_';
	/**
	 * 当前站点系统设置
	 */
	protected $_settings = array();
	/**
	 * 来自命令行请求的参数
	 */
	protected $_options = array();
	/**
	 * 来自触发此脚本的父级参数
	 */
	protected $_params = array();
	/**
	 * 储存已执行的SQL语句，文件路径
	 */
	protected $_sql_logfile = '';
	/**
	 * 储存已执行SQL语句的恢复语句，文件路径
	 */
	protected $_sql_restore_logfile = '';

	/**
	 * 当前升级的应用信息
	 */
	private $__plugin = array();

	public function __construct() {
	}

	/**
	 * 初始化环境参数
	 *
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
	 *
	 * @return void
	 */
	public function run() {
		error_reporting(E_ALL);

		$identifier = 'dailyreport';

		// 公共表结构
		$this->_common_table();

		// 获取应用信息
		$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='{$identifier}' LIMIT 1");
		$this->__plugin = $this->_db->fetch_array($query);

		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_{$identifier}'");
		if ($this->_db->fetch_row($query)) {
			// 后台菜单
			$this->_plugin_cpmenu();
			// 应用表结构
			$this->_plugin_table();
			// 应用微信企业号自定义菜单
			$this->_plugin_wxqymenu();
		}

		// 清理缓存
		$this->_cache_clear();
	}

	/**
	 * 后台菜单升级
	 */
	protected function _plugin_cpmenu() {

		// 新增日报类型
		$this->_db->query("REPLACE INTO `oa_dailyreport_setting` (`drs_key`, `drs_value`,
				`drs_type`, `drs_comment`, `drs_status`, `drs_created`, `drs_updated`,
				`drs_deleted`) VALUES('daily_type',
				'a:5:{i:1;a:2:{i:0;s:6:\"日报\";i:1;s:1:\"1\";}i:2;a:2:{i:0;s:6:\"周报\";i:1;s:1:\"1\";}i:3;a:2:{i:0;s:6:\"月报\";i:1;s:1:\"1\";}i:4;a:2:{i:0;s:6:\"季报\";i:1;s:1:\"1\";}i:5;a:2:{i:0;s:6:\"年报\";i:1;s:1:\"1\";}}',
				1, '日报类型\r\ndrs_value:0关闭  1 开启', 2, 0, ".time().", 0);");

		// 更新菜单名称
		$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_name`='工作报告' WHERE `ccm_operation`='{$this->__plugin['cp_identifier']}' AND `ccm_subop`=''");
		$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_name`='报告列表' WHERE `ccm_operation`='{$this->__plugin['cp_identifier']}' AND `ccm_subop`='list'");
		$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_name`='报告详情' WHERE `ccm_operation`='{$this->__plugin['cp_identifier']}' AND `ccm_subop`='view'");
		$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_name`='删除报告' WHERE `ccm_operation`='{$this->__plugin['cp_identifier']}' AND `ccm_subop`='delete'");
		$this->_db->query("UPDATE `oa_common_cpmenu` SET `ccm_name`='设置' WHERE `ccm_operation`='{$this->__plugin['cp_identifier']}' AND `ccm_subop`='setting'");

		return true;
	}

	/**
	 * 应用表结构升级
	 */
	protected function _plugin_table() {

		$this->_db->query("ALTER TABLE `oa_dailyreport` ADD COLUMN `dr_type` tinyint(3) unsigned   NOT NULL DEFAULT '1' COMMENT '日报类型' after `dr_subject`, ADD KEY `dr_type`(`dr_type`)");
		$this->_db->query("ALTER TABLE `oa_dailyreport_draft` ADD KEY `m_openid`(`m_openid`)");

		return true;
	}

	/**
	 * 微信企业号自定义菜单更新
	 */
	protected function _plugin_wxqymenu() {
		$api_url = 'http://' . $this->_settings['domain'] . '/api/common/post/updatewxqymenu/';

		$timestamp = startup_env::get('timestamp');

		$result = array();
		$post = array(
			'pluginid' => $this->__plugin['cp_pluginid'],
			'time' => $timestamp,
			'hash' => ''
		);
		if (!$this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url . "||".$this->__plugin['cp_pluginid']."||" . print_r($result, true));
		}

		return true;
	}

	/**
	 * 公共表结构升级
	 */
	protected function _common_table() {
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='随时发起任务，实时查看并推进任务进度，高效的任务管理平台' WHERE `cp_identifier`='project'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='支持图片、文字快速记录，记录存档、检索功能' WHERE `cp_identifier`='minutes'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='支持文字快速创建备忘，支持一键分享同事' WHERE `cp_identifier`='vnote'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='实时发起审批，实时微信提醒，固定流程方便快捷' WHERE `cp_identifier`='askfor'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='快速记录报销明细、凭据，即时消息提醒，一站式闪电报销' WHERE `cp_identifier`='reimburse'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='自定义报告类型，实时上传图文报告，快捷归档及管理' WHERE `cp_identifier`='dailyreport'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='自定义签到时间，支持支持IP与经纬度双重定，外勤人员地理位置跟踪' WHERE `cp_identifier`='sign'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='三步快速预订会议室，与会人员自动通知，二维码签到核销' WHERE `cp_identifier`='meeting'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='实时发起请假，实时微信提醒，快速审批请假申请' WHERE `cp_identifier`='askoff'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='移动版的企业通讯录，信息永不遗失，动态管理成员' WHERE `cp_identifier`='addressbook'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='巡店人员随时进行巡店评估，发现问题指定人员快速跟进，支持巡店结果排行' WHERE `cp_identifier`='inspect'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='随时随地的发起工单、派发任务；执行人员快速跟进反馈' WHERE `cp_identifier`='workorder'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='便捷地管理产品，快速分享至社交圈子；快速添加/编辑客户信息，方便维护客户关系' WHERE `cp_identifier`='travel'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='后台快速编辑公司培训文件并一键发布，员工手机端直接学习，支持可见范围设置' WHERE `cp_identifier`='train'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='后台快速下发陈列规范，门店人员按规范陈列商品，并拍照实时上传' WHERE `cp_identifier`='showroom'");
		$this->_db->query("UPDATE `oa_common_plugin` SET `cp_description`='后台编辑图文消息，定向发布公司动态、通知公告及员工动态' WHERE `cp_identifier`='news'");

		return true;
	}

	/**
	 * 清理缓存
	 */
	protected function _cache_clear() {

		// 当前站点的缓存目录
		$cachedir = $this->_params['cachedir'];

		// 读取缓存目录下的文件
		$handle = opendir($cachedir);
		// 清理后台菜单缓存文件
		if ($handle) {
			while (false !== ($file = readdir($handle))) {

				// 判断是否是有效的菜单缓存文件
				if ($file == 'cpmenu.php' || preg_match('/^adminergroupcpmenu\.\d+/', $file)) {
					// 删除
					unlink($cachedir . DIRECTORY_SEPARATOR . $file);
				}
			}
		}

		@unlink($cachedir . DIRECTORY_SEPARATOR . $file . 'plugin.dailyreport.setting.php');
		@unlink($cachedir . DIRECTORY_SEPARATOR . $file . 'plugin.php');
	}

	/**
	 * 读取远程api数据
	 *
	 * @param unknown $data
	 * @param unknown $url
	 * @param string $post
	 * @return boolean
	 */
	private function __get_json_by_post(&$data, $url, $post = '') {
		$snoopy = new snoopy();
		// 强制指定本机
		$snoopy->proxy_host = '127.0.0.1';
		$snoopy->proxy_port = 80;
		$snoopy->_isproxy = 1;
		$result = $snoopy->submit($url, $post);
		// 如果读取错误
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);
			return false;
		}

		/**
		 * 解析 json
		 */
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
}
