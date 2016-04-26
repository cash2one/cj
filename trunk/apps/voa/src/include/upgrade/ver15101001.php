<?php

/**
 * voa_upgrade_ver2015082101
 * $Author$
 * $Id$
 */
class voa_upgrade_ver15101001 extends voa_upgrade_base {

	/**
	 * 当前升级的应用信息
	 */
	private $__plugin = array();

	public function __construct() {

		parent::__construct();
		$this->_ver = '15101001';
	}

	// 升级
	public function upgrade() {
		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
		if ($this->_db->fetch_row($query)) {
			$identifier = 'sign';
			// 获取应用信息
			$query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='{$identifier}' LIMIT 1");
			$this->__plugin = $this->_db->fetch_array($query);

			// 获取用户旧数据
			$sql = "select * from oa_sign_setting";
			$query = $this->_db->query($sql);
			$info = array();
			while ($row = $this->_db->fetch_array($query)) {
				$info[$row['ss_key']] = $row;
			}
			//原来设置的值
			$work_begin_hi = $info['work_begin_hi']['ss_value'];
			$work_end_hi = $info['work_end_hi']['ss_value'];
			$late_range = ceil($info['late_range']['ss_value'] / 60);
			$leave_early_range = ceil($info['leave_early_range']['ss_value'] / 60);
			$work_days = $info['work_days']['ss_value'];
			$start_begin = startup_env::get('timestamp');
			// 格式时间 09:30 -> 930
			$work_begin_hi = (int)implode('', explode(':', $work_begin_hi));
			$work_end_hi = (int)implode('', explode(':', $work_end_hi));
			// 获取最顶级部门id
			$q = $this->_db->query("SELECT * FROM `oa_common_department` WHERE cd_upid = 0 AND cd_status<" . voa_d_oa_common_department::STATUS_REMOVE);
			$top_id = 0;
			while ($row = $this->_db->fetch_array($q)) {
				$top_id = $row['cd_id'];
			}
			// 新建外出考勤附件表
			$q = $this->_db->query("SHOW TABLES LIKE 'oa_sign_attachment'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("CREATE TABLE `oa_sign_attachment` (
  `said` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `outid` int(10) unsigned NOT NULL COMMENT '外部考勤记录id',
  `atid` varchar(255) NOT NULL COMMENT '附件id',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`said`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='外出考勤附件表'");
			}

			// 新建班次表
			$q = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("CREATE TABLE `oa_sign_batch` (
  `sbid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '班次id',
  `name` varchar(255) NOT NULL COMMENT '班次名称',
  `work_begin` int(10) unsigned NOT NULL COMMENT '工作开始时间',
  `work_end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工作结束时间',
  `work_days` varchar(255) NOT NULL COMMENT '工作日',
  `start_begin` int(10) unsigned NOT NULL COMMENT '启用时间',
  `start_end` int(10) unsigned NOT NULL COMMENT '截止时间',
  `longitude` double NOT NULL COMMENT '经度',
  `latitude` double NOT NULL COMMENT '纬度',
  `address` varchar(255) NOT NULL COMMENT '考勤地点',
  `address_range` int(10) unsigned NOT NULL COMMENT '考勤范围',
  `sb_set` int(10) unsigned NOT NULL COMMENT '上下班打卡设置,1上班，2下班，3上下班',
  `late_range` int(10) unsigned NOT NULL COMMENT '晚退多久算加班',
  `remind_on` varchar(255) NOT NULL DEFAULT '' COMMENT '签到提醒',
  `remind_off` varchar(255) NOT NULL DEFAULT '' COMMENT '签退提醒',
  `leave_early_range` int(10) NOT NULL DEFAULT '0' COMMENT '早退时间范围',
  `come_late_range` int(10) NOT NULL DEFAULT '0' COMMENT '晚到多久算迟到',
  `enable` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用,1启用,0停用',
  `range_on` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启考勤范围',
  `sign_on` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启签到提醒',
  `sign_off` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启签退提醒',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sbid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班次表'");
				// 默认班次信息
				$this->_db->query("INSERT INTO `oa_sign_batch` (`sbid`, `name`, `work_begin`, `work_end`, `work_days`, `start_begin`, `start_end`, `longitude`, `latitude`, `address`, `address_range`, `sb_set`, `late_range`, `enable`, `range_on`, `sign_on`, `sign_off`, `status`, `created`, `updated`, `deleted`) VALUES
							(1,	'默认班次', '{$work_begin_hi}', '{$work_end_hi}', '{$work_days}','{$start_begin}',	'0',	0,	0,	'',	1000,	3,	0,	1, 0, 0, 0,	2,	0,	1440039196,	0)");
			}
			// 新建班次关联部门表
			$q = $this->_db->query("SHOW TABLES LIKE 'oa_sign_department'");
			if (! $row = $this->_db->fetch_row($q)) {
				// 新建班次关联部门表
				$this->_db->query("CREATE TABLE `oa_sign_department` (
  `sdid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `sbid` int(10) unsigned NOT NULL COMMENT '班次id',
  `department` int(10) unsigned NOT NULL COMMENT '部门d',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`sdid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='班次部门关联表'
			");
				// 班次部门默认数据
				$this->_db->query("INSERT INTO `oa_sign_department` (`sdid`, `sbid`, `department`, `status`, `created`, `updated`, `deleted`) VALUES
							(null, 1, '{$top_id}', 1, 1437127210, 1437127210, 0)");
			}
			//新建签到地理位置优化表
			$q = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record_location'");
			if (! $row = $this->_db->fetch_row($q)) {
				//新建操作
				$this->_db->query("CREATE TABLE `oa_sign_record_location` (
  `loid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自动增量',
  `longitude` decimal(9,5) NOT NULL COMMENT '经度',
  `latitude` decimal(9,5) NOT NULL COMMENT '纬度',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态=1.初始化，2.更新，3删除',
  `created` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`loid`),
  KEY `longitude` (`longitude`),
  KEY `latitude` (`latitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='经纬度 地址 存储表'");
			}
				// 设置cd_id默认值
				// 判断有无cd_id字段
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'cd_id'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE oa_sign_record ADD cd_id int(10) NOT NULL COMMENT '签到时班次ID对应的部门ID' AFTER m_username");
				// 有cd_id字段
			}
			//部门赋值
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'cd_id'");
			if ($row = $this->_db->fetch_row($q)) {
				// 字段是否为空，为空则更新旧数据为最顶级部门id
				$q = $this->_db->query("SELECT * FROM oa_sign_record WHERE cd_id = 0");
				if ($row = $this->_db->fetch_row($q)) {
					$this->_db->query("UPDATE oa_sign_record SET cd_id = {$top_id}");
				}
			}
			//判断cp_memu表中sign_setting状态
			$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_operation = 'sign' AND ccm_subop = 'setting'");
			if ($row = $this->_db->fetch_row($q)) {
				//判断该记录状态
				$this->_db->query("UPDATE oa_common_cpmenu SET ccm_status = 3 WHERE ccm_operation = 'sign' AND ccm_subop = 'setting'");
			}

			// oa_sign_record表更改字段
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_sign'");
			if (! $row = $this->_db->fetch_row($q)) {
				//初始值5表示未被使用
				$this->_db->query("ALTER TABLE oa_sign_record ADD sr_sign int(10)  NOT NULL DEFAULT '5' COMMENT '考勤状态'");
			}
			// 升级oa_sign_record表sr_batch字段
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_batch'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE oa_sign_record ADD sr_batch int(10) NOT NULL COMMENT '所属班次'");
			}
			// 班次里有无值
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_batch'");
			if ($row = $this->_db->fetch_row($q)) {
				$q = $this->_db->query("SELECT * FROM oa_sign_record WHERE sr_batch = 0");
				if ($row = $this->_db->fetch_row($q)) {
					$this->_db->query("update oa_sign_record SET sr_batch = 1");
				}
			}
			// 班次复制
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_sign'");
			if ($row = $this->_db->fetch_row($q)) {
				$q = $this->_db->query("SELECT * FROM oa_sign_record WHERE sr_sign = 5");
				if ($row = $this->_db->fetch_row($q)) {
					$this->_db->query("UPDATE oa_sign_record SET sr_sign = sr_status");
				}
			}
			// 记录状态默认值
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_sign'");
			if ($row = $this->_db->fetch_row($q)) {
				$q = $this->_db->query("SELECT * FROM oa_sign_record WHERE sr_sign NOT IN (5)");
				if ($row = $this->_db->fetch_row($q)) {
					$this->_db->query("UPDATE oa_sign_record SET sr_status = 1");
				}
			}
			// 查询是否有sr_overtime
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_overtime'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE oa_sign_record add sr_overtime int(10) NOT NULL COMMENT '加班时长'");
			}
			// 查询是否有sr_addunusual
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_addunusual'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE oa_sign_record ADD sr_addunusual int(10) NOT NULL DEFAULT 0 COMMENT '地理位置异常,1异常, 0正常'");
			}
			// 备注表添加字段
			// 查询是否有type字段
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_detail LIKE 'type'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE `oa_sign_detail` ADD `type` tinyint(3) NOT NULL COMMENT '当前备注的签到类型: 1=签到 2=签退' AFTER `sd_reason`");
			}
			// 上报地理位置表添加字段
			// 查询是否有sl_note
			$q = $this->_db->query("SHOW FIELDS FROM oa_sign_location LIKE 'sl_note'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("ALTER TABLE `oa_sign_location` ADD `sl_note` TEXT NOT NULL DEFAULT '' COMMENT '签到备注' after `sl_address`");
			}
			// oa_common_cpmenu表添加数据
			$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_subop='blist'");
			if (! $row = $this->_db->fetch_row($q)) {
				$this->_db->query("INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES
({$this->__plugin['cp_pluginid']},	0,	'office',	'sign',	'blist',	'subop',	0,	'班次安排',	'fa-gear',	103,	1016,	1,	1,	1436771561,	1436771561,	0),
({$this->__plugin['cp_pluginid']},	1,	'office',	'sign',	'badd',	'subop',	0,	'添加班次',	'fa-gear',	103,	1014,	0,	1,	1436771561,	1436771561,	0),
({$this->__plugin['cp_pluginid']},	1,	'office',	'sign',	'bdelete',	'subop',	0,	'删除班次',	'fa-times',	103,	1014,	0,	1,	1436771561,	1436771561,	0),
({$this->__plugin['cp_pluginid']},	1,	'office',	'sign',	'updetail',	'subop',	0,	'外勤详情',	'fa-eye',	103,	1014,	0,	1,	1436771561,	1436771561,	0)");
			}
			//修改签到为考勤
			$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_name='签到'");
			if ($row = $this->_db->fetch_row($q)) {
				$this->_db->query("UPDATE `oa_common_cpmenu` SET ccm_name = '考勤' WHERE ccm_name = '签到'");
			}
			//考勤列表
			$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_name='签到记录'");
			if ($row = $this->_db->fetch_row($q)) {
				$this->_db->query("UPDATE `oa_common_cpmenu` SET ccm_name = '公司考勤记录' WHERE ccm_name = '签到记录'");
			}
			//签到详情
			$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_name='签到详情'");
			if ($row = $this->_db->fetch_row($q)) {
				$this->_db->query("UPDATE `oa_common_cpmenu` SET ccm_name = '考勤详情' WHERE ccm_name = '签到详情'");
			}
			//外勤记录
			$q = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_name='地理位置上报记录'");
			if ($row = $this->_db->fetch_row($q)) {
				$this->_db->query("UPDATE `oa_common_cpmenu` SET ccm_name = '外出考勤记录' WHERE ccm_name = '地理位置上报记录'");
			}
			//plugin表考勤
			$q = $this->_db->query("SELECT * FROM oa_common_plugin WHERE cp_name='签到'");
			if ($row = $this->_db->fetch_row($q)) {
				$this->_db->query("UPDATE `oa_common_plugin` SET cp_name = '考勤', cp_description = '支持IP与经纬度双重定位，支持多部门分班次、多地点考勤设置；外勤人员必须现场拍照确认地理位置，可设置考勤提醒。' WHERE cp_name = '签到'");
			}
			// 清理缓存
			$this->_cache_clear();
			$this->_db->query("UPDATE oa_sign_record SET sr_sign = 1 WHERE sr_sign = 0");
			// 微信自定义菜单升级
			$this->_plugin_wxqymenu();
		}

		return true;
	}

	/**
	 * 更新微信企业号的自定义菜单
	 */
	protected function _plugin_wxqymenu() {

		$settings = voa_h_cache::get_instance()->get('setting', 'oa');
		$api_url = config::get(startup_env::get('app_name') . '.oa_http_scheme') . $settings['domain'] . '/api/common/post/updatewxqymenu/';

		$timestamp = startup_env::get('timestamp');
		$crypt_xxtea = new crypt_xxtea();
		$hash = rbase64_encode($crypt_xxtea->encrypt(md5($timestamp)));

		$result = array();
		$post = array('pluginid' => $this->__plugin['cp_pluginid'], 'time' => $timestamp, 'hash' => $hash);
		if (! $this->__get_json_by_post($result, $api_url, $post)) {
			logger::error($api_url . "||" . print_r($result, true));
		}

		return true;
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
		$result = $snoopy->submit($url, $post);

		/**
		 * 如果读取错误
		 */
		if (! $result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);
			return false;
		}

		/**
		 * 解析 json
		 */
		$data = (array)json_decode($snoopy->results, true);

		if (empty($data) || ! empty($data['errcode'])) {
			logger::error('$snoopy->submit error: ' . $url . '|' . $snoopy->results . '|' . $snoopy->status);
			return false;
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
