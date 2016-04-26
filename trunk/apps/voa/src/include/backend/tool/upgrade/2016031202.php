<?php

/**
 * 2016031202.php
 * 考勤应用迭代 V2.1.0
 * php -q tool.php -n upgrade -version 2016031202 -epid vchangyi_oa
 * Create By anything
 * $Author$
 * $Id$
 */
class execute {

    //老的全公司 部门id=1
    const OLD_ALL_COMPANY_CDID = 1;

	//默认班次id
	const DEFULT_BATCH_ID = 1;

    //班次启用
    const SIGN_BATCH_ENABLE_ON = 1;

    //班次禁用
    const SIGN_BATCH_ENABLE_OFF = 0;

	//签到类型：上班
	const SIGN_TYPE_ON = 1;

	//签到类型：下班
	const SIGN_TYPE_OFF = 2;

	const URI_BAIDU = 'http://api.map.baidu.com/geoconv/v1/?coords=%s,%s&;&from=3&to=5&ak=ZgwqnK2tl1y2k6oRI8DCyZ2kjmOcsz22';

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
	 * 备份班次表
	 */
	protected function _copy_sign_batch_table(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if ($this->_db->fetch_row($query)) {
            $query = $this->_db->query("SHOW TABLES LIKE 'oa_tmp_sign_batch'");
            if (!$this->_db->fetch_row($query)) {
                $this->_db->query("CREATE TABLE oa_tmp_sign_batch SELECT * FROM oa_sign_batch");
            }
		}
	}

	/**
	 * 清空签到经纬度历史记录表
	 */
	protected function _clear_sign_location(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record_location'");
		if ($this->_db->fetch_row($query)) {
			$this->_db->query("TRUNCATE oa_sign_record_location");
		}
	}

	/**
	 * 记录升级的二级域名
	 */
	protected function _log_domains(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_common_setting'");
		if ($this->_db->fetch_row($query)) {
			$query = $this->_db->query("select * from oa_common_setting where cs_key='domain'");
			if($data = $this->_db->fetch_array($query)){
				$hosts = explode('.', $data['cs_value']);
				$domain = rawurlencode($hosts[0]);
				file_put_contents(APP_PATH . '/data/sign_domain.txt', $domain.',', FILE_APPEND|LOCK_EX);
			}
		}

	}

	/**
	 * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
	 * @return void
	 */
	public function run() {

		error_reporting(E_ALL);

		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if (!$this->_db->fetch_row($query)) {
			return true;
		}

		//备份班次表
		$this->_copy_sign_batch_table();

		//清空签到经纬度历史记录表
		$this->_clear_sign_location();

		//更新cpmenu
		$this->_update_plugin_cpmenu();

        //升级班次表结构
        $this->_modify_sign_batch();

        //升级考勤消息提醒表结构
        $this->_modify_sign_alert();

		//升级打卡记录表结构
		$this->_modify_sign_record();

        //创建考勤排班表
        $this->_create_sign_schedule();

        //创建考勤排班历史变更表
        $this->_create_sign_schedule_log();

		//插入默认排班数据
		$this->_insert_defult_schedule();

		//插入sign_setting
		$this->_insert_sign_setting();

		//升级考勤班次表数据
		$this->_update_sign_batch_data();

		//插入考勤排班表数据
		$this->__insert_sign_schedule();

		//升级打卡记录表
		$this->_update_sign_record();

		//记录升级二级域名
		$this->_log_domains();

		// 清理缓存
		$this->_cache_clear();

	}

	/**
	 * 把“微信菜单设置” 设置成隐藏
	 */
	protected function _update_plugin_cpmenu() {
//        logger::error('开始升级oa_common_cpmenu表的微信菜单设置');
		//判断该菜单是否存在
		$query = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_operation = 'sign' AND ccm_subop = 'wxcpmenu'");
		if ($this->_db->fetch_row($query)) {
			$this->_db->query("UPDATE oa_common_cpmenu SET ccm_subnavdisplay=0 where ccm_module='office' AND ccm_operation='sign' AND ccm_subop='wxcpmenu' ");

            $query2 = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_operation = 'sign' AND ccm_subop = 'config'");
            if (!$this->_db->fetch_row($query2)) {
                $this->_db->query("
                  INSERT INTO oa_common_cpmenu
                   (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`)
                   VALUES ('14', '0', 'office', 'sign', 'config', 'subop', '0', '设置', 'fa-gear', '110', '1035', '1', '1', '0', '0', '0');

                ");
            }

			$query3 = $this->_db->query("SELECT * FROM oa_common_cpmenu WHERE ccm_operation = 'sign' AND ccm_subop = 'schedule'");
			if (!$this->_db->fetch_row($query3)) {
				$this->_db->query("
                  INSERT INTO oa_common_cpmenu
                   (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`)
                   VALUES ('14', '0', 'office', 'sign', 'schedule', 'subop', '0', '人员排班', 'fa-list', '109', '1017', '1', '1', '0', '0', '0');

            	");
			}


			$this->_db->query("
                  update oa_common_cpmenu set ccm_name='班次管理' where ccm_module='office' AND ccm_operation='sign' AND ccm_subop='blist'
            ");


        }
	}

    /**
     * 插入sign_setting 默认全局签到/签退设置
     */
    protected function _insert_sign_setting(){
        // 判断应用表是否存在
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_setting'");
        if ($this->_db->fetch_row($query)) {

			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_late_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_late_range', '60', '0', '加班规则', '1', '0', '1456748109', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_end_rage'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_end_rage', '120', '0', '签退时间范围', '1', '0', '1456748109', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_come_late_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_come_late_range', '10', '0', '迟到规则', '1', '0', '1457505211', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_start_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_start_range', '100', '0', '签到时间范围', '1', '0', '1457505211', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_leave_early_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_leave_early_range', '10', '0', '早退规则', '1', '0', '1456748109', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_remind_on_rage'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_remind_on_rage', '5', '0', '签到时间点前XX分钟提醒', '1', '0', '1457505211', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_remind_off_rage'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_remind_off_rage', '5', '0', '签退时间点后XX分钟提醒', '1', '0', '1456748109', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_remind_off'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_remind_off', '下班了,快去签退吧!', '0', '签退提醒内容', '1', '0', '1456748109', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='rest_day_sign'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('rest_day_sign', '1', '0', '休息日是否允许考勤 1-关闭；2-开启', '1', '0', '1457518469', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='sign_remind_on'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('sign_remind_on', '上班时间快到了,快来签个到吧!', '0', '签到提醒内容', '1', '0', '1457505211', '0')
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='wxcpmenu'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('wxcpmenu', 'a:3:{i:0;a:4:{s:4:\"name\";s:12:\"公司考勤\";s:3:\"url\";s:53:\"{domain_url}/frontend/sign/index/?pluginid={pluginid}\";s:4:\"type\";s:4:\"view\";s:9:\"\$\$hashKey\";s:10:\"object:105\";}i:1;a:4:{s:4:\"name\";s:12:\"外出考勤\";s:3:\"url\";s:58:\"{domain_url}/frontend/sign/uplocation/?pluginid={pluginid}\";s:4:\"type\";s:4:\"view\";s:9:\"\$\$hashKey\";s:10:\"object:106\";}i:2;a:4:{s:4:\"name\";s:12:\"考勤记录\";s:3:\"url\";s:58:\"{domain_url}/frontend/sign/signsearch/?pluginid={pluginid}\";s:4:\"type\";s:4:\"view\";s:9:\"\$\$hashKey\";s:10:\"object:107\";}}', 1, '', 1, 1457580775, 1458247329, 0)
				");
			}
			$_q = $this->_db->query("select * from oa_sign_setting where ss_key='out_sign_upload_img'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("
					INSERT INTO `oa_sign_setting`
					(`ss_key`, `ss_value`, `ss_type`, `ss_comment`, `ss_status`, `ss_created`, `ss_updated`, `ss_deleted`)
					VALUES
                    ('out_sign_upload_img', '2', '0', '外出考勤是否必须上传图片 1-关闭；2-开启', '1', '0', '1457518469', '0')
				");
			}

        }
    }

    /**
     * 修改考勤班次表结构
     */
    protected function _modify_sign_batch(){
        // 判断应用表是否存在
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
        if ($this->_db->fetch_row($query)) {

			//验证表字段是否存在,修改字段
  			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'work_days'");
            if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `work_days`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '工作日' AFTER `work_end`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'start_begin'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `start_begin`  int(10) UNSIGNED NULL COMMENT '启用时间' AFTER `work_days`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'start_end'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `start_end`  int(10) UNSIGNED NULL COMMENT '截止时间' AFTER `start_begin`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'longitude'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `longitude`  double NULL COMMENT '经度' AFTER `start_end`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'latitude'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `latitude`  double NULL COMMENT '纬度' AFTER `longitude`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'address'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `address`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '考勤地点' AFTER `latitude`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'address_range'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `address_range`  int(10) UNSIGNED NULL COMMENT '考勤范围' AFTER `address`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'sb_set'");
			if ($row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` MODIFY COLUMN `sb_set`  int(10) UNSIGNED NULL COMMENT '上下班打卡设置,1上班，2下班，3上下班' AFTER `address_range`");
			}

			//新增字段
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'sign_start_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `sign_start_range`  int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上班时间点前XX分钟开始签到' AFTER `sign_off`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'sign_end_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `sign_end_range`  int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下班时间点后XX分钟结束签退' AFTER `sign_start_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'remind_on_rage'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `remind_on_rage`  int(10) UNSIGNED DEFAULT NULL COMMENT '上班时间点前XX分钟提醒' AFTER `sign_end_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'remind_off_rage'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `remind_off_rage`  int(10) UNSIGNED DEFAULT NULL COMMENT '下班时间点后XX分钟提醒' AFTER `remind_on_rage`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'late_work_time'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `late_work_time`  int(10) UNSIGNED DEFAULT NULL COMMENT '最晚上班时间' AFTER `remind_off_rage`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'absenteeism_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `absenteeism_range`  int(3) UNSIGNED DEFAULT NULL COMMENT '实际工作时长少于最小工作时长算旷工,50%、100%' AFTER `late_work_time`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'min_work_hours'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `min_work_hours`  decimal(3,1) DEFAULT NULL COMMENT '最小工作时长' AFTER `absenteeism_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'rule'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `rule`  tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '考勤规则 1默认，2=自定义' AFTER `min_work_hours`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'type'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `type`  tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '班次类型1:常规班次,2:弹性班次' AFTER `rule`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'late_range_on'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `late_range_on`  tinyint(3) unsigned DEFAULT '0' COMMENT '是否启用加班 0:禁用, 1:启用' AFTER `type`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'late_work_time_on'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `late_work_time_on`  tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '启用最晚上班时间0:禁用,1:启用' AFTER `late_range_on`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'come_late_range_on'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `come_late_range_on`  tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用迟到规则0:禁用,1:启用' AFTER `late_work_time_on`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'absenteeism_range_on'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `absenteeism_range_on`  tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用旷工规则0:禁用,1:启用' AFTER `come_late_range_on`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_batch LIKE 'flag'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_batch` ADD COLUMN `flag`  tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否是默认排版0:不是,1:是' AFTER `absenteeism_range_on`");
			}

        }
    }

	/**
	 * 升级考勤打卡记录表结构
	 */
	protected function _modify_sign_record(){
		// 判断应用表是否存在
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
		if ($this->_db->fetch_row($query)) {

			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_sign_start_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_sign_start_range` int(10) DEFAULT NULL COMMENT '上班时间点前XX分钟开始签到' AFTER `sr_addunusual`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_sign_end_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_sign_end_range` int(10) DEFAULT NULL COMMENT '下班时间点后XX分钟结束签退' AFTER `sr_sign_start_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_come_late_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_come_late_range` int(10) DEFAULT NULL COMMENT '迟到规则' AFTER `sr_sign_end_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_leave_early_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_leave_early_range`  int(10) DEFAULT NULL COMMENT '早退规则' AFTER `sr_come_late_range`");
			}


			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_late_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_late_range`  int(10) unsigned DEFAULT NULL COMMENT '加班规则' AFTER `sr_leave_early_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_late_work_time'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_late_work_time`  int(10) DEFAULT NULL COMMENT '最晚上班时间' AFTER `sr_late_range`");
			}


			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'ba_type'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `ba_type`  tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '班次类型1:常规班次,2:弹性班次' AFTER `sr_late_work_time`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_absenteeism_range'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_absenteeism_range`  int(3) DEFAULT NULL COMMENT '实际工作时长少于最小工作时长算旷工,50%、100%' AFTER `ba_type`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_min_work_hours'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_min_work_hours`  decimal(3,1) DEFAULT NULL COMMENT '最小工作时长' AFTER `sr_absenteeism_range`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_work_status'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_work_status` tinyint(2) unsigned DEFAULT NULL COMMENT '工作状态：1-休息(休息日签到时的状态，用于统计加班)' AFTER `sr_min_work_hours`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_work_begin'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_work_begin` int(10) unsigned DEFAULT NULL COMMENT '工作开始时间' AFTER `sr_work_status`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sr_work_end'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sr_work_end` int(10) unsigned DEFAULT NULL COMMENT '工作结束时间' AFTER `sr_work_begin`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'rep_late_time'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `rep_late_time` int(10) unsigned DEFAULT NULL COMMENT '迟到时长' AFTER `sr_work_end`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'rep_early_time'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `rep_early_time` int(10) unsigned DEFAULT NULL COMMENT '早退时长' AFTER `rep_late_time`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'rep_work_time'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `rep_work_time` int(10) unsigned DEFAULT NULL COMMENT '出勤时长' AFTER `rep_early_time`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'schedule_id'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `schedule_id` int(10) unsigned DEFAULT NULL COMMENT '排班ID' AFTER `rep_work_time`");
			}
			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_record LIKE 'sign_schedule_id'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_record` ADD COLUMN `sign_schedule_id` int(10) unsigned DEFAULT NULL COMMENT '签到的排班id' AFTER `schedule_id`");
			}

		}
	}

    /**
     * 修改考勤消息提醒表结构
     */
    protected function _modify_sign_alert(){
        // 判断应用表是否存在
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_alert'");
        if ($this->_db->fetch_row($query)) {

			$_q = $this->_db->query("SHOW FIELDS FROM oa_sign_alert LIKE 'schedule_id'");
			if (!$row = $this->_db->fetch_row($_q)) {
				$this->_db->query("ALTER TABLE `oa_sign_alert` ADD COLUMN `schedule_id`  int(10) UNSIGNED DEFAULT NULL AFTER `batch_id`");
			}
        }
    }

    /**
     * 创建考勤排班表
     */
    protected function _create_sign_schedule(){
        // 判断应用表是否存在
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_schedule'");
        if (!$this->_db->fetch_row($query)) {

            $this->_db->query("
                  CREATE TABLE `oa_sign_schedule`
                  (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `cd_id` int(10) DEFAULT NULL COMMENT '部门ID',
                      `sbid` text COMMENT '班次id',
                      `enabled` tinyint(2) NOT NULL COMMENT '1-禁用;2-启用,3-禁用中',
                      `schedule_begin_time` int(10) NOT NULL COMMENT '排班开始时间',
                      `schedule_end_time` int(10) DEFAULT NULL COMMENT '排班结束时间,为空到永久',
                      `cycle_unit` tinyint(2) NOT NULL COMMENT '周期单位 1-天,2-周,3-月',
                      `cycle_num` tinyint(2) DEFAULT NULL COMMENT '如果周期单位是天，才有效，1-7天',
                      `schedule_everyday_detail` text NOT NULL COMMENT '每天排班详情hash',
                      `add_work_day` text COMMENT '增加上班日期',
                      `remove_day` text COMMENT '排除节假日',
                      `range_on` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启考勤范围,0-关闭；1-开启',
                      `address` varchar(255) DEFAULT NULL COMMENT '考勤地点',
                      `address_range` int(10) unsigned DEFAULT NULL COMMENT '考勤范围',
                      `longitude` double DEFAULT NULL COMMENT '经度',
                      `latitude` double DEFAULT NULL COMMENT '纬度',
                      `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
                      `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
                      `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                      `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
                      PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='排班表';
            ");


        }
    }

	/**
	 * 插入默认排班数据
	 */
	protected function _insert_defult_schedule(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_schedule'");
		if ($this->_db->fetch_row($query)) {
			$_qy = $this->_db->query("select * from oa_sign_schedule where id=1;");
			if (!$this->_db->fetch_row($_qy)) {
				$this->_db->query("
					INSERT INTO `oa_sign_schedule`
					(`id`, `cd_id`, `sbid`, `enabled`, `schedule_begin_time`, `schedule_end_time`, `cycle_unit`, `cycle_num`, `schedule_everyday_detail`, `add_work_day`, `remove_day`, `range_on`, `address`, `address_range`, `longitude`, `latitude`, `status`, `created`, `updated`, `deleted`)
					VALUES
					(1, 0, ',1,1,1,1,1,', 1, 1451577600, 0, 2, NULL, 'a:7:{i:0;a:1:{i:0;a:4:{s:4:\"name\";s:12:\"默认班次\";s:4:\"type\";s:1:\"1\";s:2:\"id\";s:1:\"1\";s:4:\"time\";s:21:\"1457917200-1457949600\";}}i:1;a:1:{i:0;a:4:{s:4:\"name\";s:12:\"默认班次\";s:4:\"type\";s:1:\"1\";s:2:\"id\";s:1:\"1\";s:4:\"time\";s:21:\"1457917200-1457949600\";}}i:2;a:1:{i:0;a:4:{s:4:\"name\";s:12:\"默认班次\";s:4:\"type\";s:1:\"1\";s:2:\"id\";s:1:\"1\";s:4:\"time\";s:21:\"1457917200-1457949600\";}}i:3;a:1:{i:0;a:4:{s:4:\"name\";s:12:\"默认班次\";s:4:\"type\";s:1:\"1\";s:2:\"id\";s:1:\"1\";s:4:\"time\";s:21:\"1457917200-1457949600\";}}i:4;a:1:{i:0;a:4:{s:4:\"name\";s:12:\"默认班次\";s:4:\"type\";s:1:\"1\";s:2:\"id\";s:1:\"1\";s:4:\"time\";s:21:\"1457917200-1457949600\";}}i:5;a:1:{i:0;a:2:{s:4:\"name\";s:6:\"休息\";s:4:\"type\";s:1:\"2\";}}i:6;a:1:{i:0;a:2:{s:4:\"name\";s:6:\"休息\";s:4:\"type\";s:1:\"2\";}}}', '', '', 0, NULL, NULL, NULL, NULL, 1, 1457923010, 0, 0);
				");
			}
		}
	}

    /**
     * 创建考勤排班历史变更表
     */
    protected function _create_sign_schedule_log(){
        // 判断应用表是否存在
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_schedule_log'");
        if (!$this->_db->fetch_row($query)) {

            $this->_db->query("
                  CREATE TABLE `oa_sign_schedule_log`
                  (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `cd_id` int(10) NOT NULL COMMENT '部门ID',
                      `schedule_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排班id',
                      `begin_time` int(10) unsigned NOT NULL COMMENT '开始时间',
                      `end_time` int(10) unsigned NOT NULL COMMENT '结束时间',
                      `init_time` int(10) NOT NULL DEFAULT '0' COMMENT '排班启用时间',
                      `cycle_unit` tinyint(2) NOT NULL COMMENT '周期单位 1-天,2-周,3-月',
                      `cycle_num` tinyint(2) DEFAULT NULL COMMENT '如果周期单位是天，才有效，1-7天',
                      `add_work_day` text NULL COMMENT '增加上班日期',
                      `remove_day` text NULL COMMENT '排除节假日',
                      `schedule_everyday_detail` text NOT NULL COMMENT '每天排班详情,Array-班次规则',
                      `status` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1初始化，2=已更新, 3=已删除',
                      `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新建时间',
                      `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                      `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
                      PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='排班历史变更表';
            ");
        }
    }

	/**
	 * 百度地址转换接口
	 * @param $result
	 * @param $x
	 * @param $y
	 * @return bool
	 */
	protected function _get_by_baidu_map(&$map, $x, $y) {

		$url = sprintf(self::URI_BAIDU, $x, $y);

		if(!voa_h_func::get_json_by_post($data, $url)){
			logger::error('请求百度地图坐标转换接口失败');
			return false;
		}
		if($data['status'] != 0){
			logger::error('请求百度地图坐标转换接口失败');
			return false;
		}
		$obj = $data['result'][0];

		$map = array(
			'longitude' => $obj['x'],
			'latitude' => $obj['y']
		);

		return true;

//		echo $obj['x'];
//		echo $obj['y'];


	}

	/**
	 * 插入计划任务
	 * @param $taskid
	 * @param $runtime
	 * @param $domain
	 * @param $type
	 * @param $params
	 */
	protected function _add_task($taskid, $runtime, $domain , $type, $params){
		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');

		$rpc_crontab->add(array(
			'taskid' => $taskid,
			'domain' => $domain,
			'type' => $type,
			'ip' => '',
			'runtime' => $runtime,
			'endtime' => 0,
			'looptime' => 86400,
			'times' => 0,
			'runs' => 0,
			'params' => $params
		));
	}
    /**
     * 升级班次表数据
     */
    protected function _update_sign_batch_data(){
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
        if ($this->_db->fetch_row($query)) {

			$query = $this->_db->query("SELECT * FROM oa_common_setting where cs_key='domain';");
			$domain = $this->_db->fetch_array($query);

            //查询所有的班次数据
            $batch_query = $this->_db->query("select * from oa_sign_batch where `status` < 3 ORDER BY sbid ASC");
			while ($batch_data = $this->_db->fetch_array($batch_query)) {
				if(empty($batch_data)){
					continue;
				}
				$ymd = rgmdate(time(),'Y-m-d');
				$work_begin_timestamp = rstrtotime($ymd . ' ' . $this->__formattime($batch_data['work_begin']));

				$work_e = substr($batch_data['work_end'], 0, -2);
				//logger::error($work_e);
				if ($work_e - 24 >= 0) {
					$h = $work_e - 24;
					$i = substr($batch_data['work_end'], -2);
					//logger::error($h);
					//logger::error($i);
					$hi = $h . $i;
					$work_end_timestamp = rstrtotime($ymd . ' ' . $this->__formattime($hi)) + 86400;
				} else {
					$work_end_timestamp = rstrtotime($ymd . ' '  . $this->__formattime($batch_data['work_end']));
				}


				if($batch_data['sign_on'] == 1){
					$taskid = md5('sign_new'. $domain['cs_value']. $batch_data['sbid']);
					//logger::error('1----'.$work_begin_timestamp);
					$runtime = $work_begin_timestamp-300;
					//提醒时间不能超过当天凌晨
					if($this->__differ_days(rgmdate($runtime,'Y-m-d'), rgmdate($work_begin_timestamp,'Y-m-d')) > 0){
						$runtime = $work_begin_timestamp;
					}
//					logger::error('2-----'.$runtime);
//					logger::error(rgmdate($runtime, 'Y-m-d H:i:s'));
					if($runtime < time()){
						$runtime += 86400;
					}
					//logger::error('3----'.rgmdate($runtime, 'Y-m-d H:i:s'));
					$params = array(
						'batch_id' =>  $batch_data['sbid'],
						'content' => $batch_data['remind_on'],
						'type' => 'sign_on'
					);
					$this->_add_task($taskid, $runtime, $domain['cs_value'], 'sign_on', $params);
				}
				if($batch_data['sign_off'] == 1){
					$taskid = md5('sign_new'. $domain['cs_value']. $batch_data['sbid']);
					$runtime = $work_end_timestamp+300;
					//logger::error(rgmdate($runtime, 'Y-m-d H:i:s'));
					if($runtime < time()){
						$runtime += 86400;
					}
					$params = array(
						'batch_id' =>  $batch_data['sbid'],
						'content' => $batch_data['remind_off'],
						'type' => 'sign_off'
					);
					$this->_add_task($taskid, $runtime, $domain['cs_value'], 'sign_off', $params);
				}

				$longitude = $batch_data['longitude'];
				$latitude = $batch_data['latitude'];
				//开启考勤范围，调用百度地图api转换经纬度
				if($batch_data['range_on'] == 1){
					if($this->_get_by_baidu_map($map, $batch_data['longitude'], $batch_data['latitude'])){
						$longitude = $map['longitude'];
						$latitude = $map['latitude'];
					}
				}



				if($batch_data['sbid'] == self::DEFULT_BATCH_ID){
					$this->_db->query("
						UPDATE
							`oa_sign_batch`
						SET
							`work_begin`=$work_begin_timestamp,
							`work_end`=$work_end_timestamp,
							`sign_start_range`=180 * 60,
							`sign_end_range`=360 * 60,
							`remind_on_rage`=300,
							`remind_off_rage`=300,
							`rule`=2,
							`type`=1,
							`longitude`='{$longitude}',
							`latitude`='{$latitude}',
							`late_range_on`=1,
							`leave_early_range`={$batch_data['leave_early_range']} * 60,
							`come_late_range`={$batch_data['come_late_range']} * 60,
							`late_range`={$batch_data['late_range']} * 60,
							`late_work_time_on`=0,
							`come_late_range_on`=1,
							`absenteeism_range_on`=0,
							`flag`=1,
							`updated`=1457604000
						WHERE
							`sbid`='{$batch_data['sbid']}'
					");

					continue;
				}

				//更新班次表
				$this->_db->query("
					UPDATE
						`oa_sign_batch`
					SET
						`work_begin`=$work_begin_timestamp,
						`work_end`=$work_end_timestamp,
						`sign_start_range`=180 * 60,
						`sign_end_range`=360 * 60,
						`remind_on_rage`=300,
						`remind_off_rage`=300,
						`rule`=2,
						`type`=1,
						`longitude`='{$longitude}',
						`latitude`='{$latitude}',
						`late_range_on`=1,
						`leave_early_range`={$batch_data['leave_early_range']} * 60,
						`come_late_range`={$batch_data['come_late_range']} * 60,
						`late_range`={$batch_data['late_range']} * 60,
						`late_work_time_on`=0,
						`come_late_range_on`=1,
						`absenteeism_range_on`=0,
						`updated`=1457604000
					WHERE
						`sbid`='{$batch_data['sbid']}'
				");
			}

        }
    }

	/**
	 * 查询班次部门关联表
	 * @param $sbid 班次ID
	 */
	private function __select_batch_department($sbid){
		$qy = $this->_db->query("select * from oa_sign_department where sbid=$sbid and `status` < 3;");
		$result_data = array();
		while($result = $this->_db->fetch_array($qy)){
			$result_data[] = $result;
		}
		return $result_data;
	}

	/**
	 * 插入排班表
	 */
	private function __insert_sign_schedule(){

		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if ($this->_db->fetch_row($query)) {
			//查询所有启用的,没有过期的班次数据
			$batch_query = $this->_db->query("select * from oa_sign_batch where `enable`=1 and (start_end =0 or start_end > 1457884800) and `status` < 3 ORDER BY sbid ASC;");
			$batch_enabled_data = array();
			while($data = $this->_db->fetch_array($batch_query)){
				$batch_enabled_data[] = $data;
			}

			$defult_all_batch_on = false;

			//工作日7天
			$work_days = array(1,2,3,4,5,6,0);

			$ymd = rgmdate(time(),'Y-m-d');
			$now_time = 0;
			//遍历所有启用的班次数据
			foreach($batch_enabled_data as $bed){

				//一周每天的排班详情
				$schedule_everyday_detail = array();

				//工作日
				$wk_days = unserialize($bed['work_days']);
				foreach($work_days as $wd){
					$batch = array(
						'type' => '2',
						'name' => '休息'
					);
					//这天上班
					if(in_array($wd, $wk_days)){
						$batch['type'] = '1';
						$batch['name'] = $bed['name'];
						$batch['id'] = $bed['sbid'];

						//$work_begin_timestamp = rstrtotime($ymd . ' ' . $this->__formattime($bed['work_begin']));
						//$work_end_timestamp = rstrtotime($ymd. ' ' . $this->__formattime($bed['work_end']));

						$batch['time'] = $bed['work_begin'] . '-' . $bed['work_end'];
					}

					//每天的班次
					$everyday[] = $batch;
					$schedule_everyday_detail[] = $everyday;
					unset($everyday);
				}
				unset($everyday);
				unset($batch);

				$schedule_everyday = serialize($schedule_everyday_detail);

				//查询班次部门关联表
				$batch_department = $this->__select_batch_department($bed['sbid']);

				//如果是默认班次,部门id等于1全公司
				if ($bed['sbid'] == self::DEFULT_BATCH_ID && $batch_department[0]['department'] == self::OLD_ALL_COMPANY_CDID) {

					//$defult_all_batch_on = true;
					$now_time = time();
					$this->_db->query("
						UPDATE `oa_sign_schedule`
						SET
						`enabled`='2',
						`schedule_begin_time`={$bed['start_begin']},
						`schedule_end_time`={$bed['start_end']},
						`cycle_unit`='2',
						`schedule_everyday_detail`='{$schedule_everyday}',
						`range_on`={$bed['range_on']},
						`address`='{$bed['address']}',
						`address_range`={$bed['address_range']},
						`longitude`={$bed['longitude']},
						`latitude`={$bed['latitude']},
						`status`='2',
						`updated`=$now_time
						WHERE
						(`id`='1');
					");

					continue;
				}

//			if($defult_all_batch_on){
//				$enabled = 1;
//				$now_time = 0;
//			}else{
//				$enabled = 2;
//				$now_time = time();
//			}

				if($bed['enable'] == 1){//启用
					$enabled = 2;
				}else{
					$enabled = 1;
				}

				//遍历部门，插入排班表
				foreach($batch_department as $dept){
					//验证部门是否已存在排班
					$validate_query = $this->_db->query("select * from oa_sign_schedule where `cd_id`={$dept['department']} and `status` < 3;");

					if($this->_db->fetch_array($validate_query)){
						continue;
					}

					//插入此部门的排班数据
					$this->_db->query("
					INSERT INTO `oa_sign_schedule`
					(
						`cd_id`, `sbid`, `enabled`, `schedule_begin_time`,
						`schedule_end_time`, `cycle_unit`,
						`schedule_everyday_detail`, `add_work_day`, `remove_day`,
						`range_on`, `address`, `address_range`, `longitude`, `latitude`,
						`status`, `created`, `updated`, `deleted`
					)
					VALUES
					(
						{$dept['department']}, ',{$bed['sbid']},', {$enabled}, {$bed['start_begin']},
						{$bed['start_end']}, '2',
						'{$schedule_everyday}', '', '',
						{$bed['range_on']}, '{$bed['address']}', {$bed['address_range']}, {$bed['longitude']}, {$bed['latitude']},
						'1', $now_time, $now_time, '0'
					);
				");
				}
			}
		}

	}

	/**
	 * 升级考勤打卡记录表数据
	 */
	protected function _update_sign_record(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if ($this->_db->fetch_row($query)) {
			$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
			if ($this->_db->fetch_row($query)) {



				//先查出打卡记录表所有不重复的班次id
				$record_batch_auery =  $this->_db->query("select * from oa_sign_record where `sr_status` < 3 group by sr_batch;");
				$record_batch = array();
				while($record = $this->_db->fetch_array($record_batch_auery)){
					$record_batch[] = $record;
				}

				//查询班次规则，查不到的为空
				$batch_array = array();
				foreach($record_batch as $rb){
					$query = $this->_db->query("select * from oa_sign_batch where sbid='{$rb['sr_batch']}';");
					$res = $this->_db->fetch_array($query);

					$batch_array[$rb['sr_batch']] = $res;
				}

				if(empty($batch_array)){
					return true;
				}

				$sr_id = 0;
				do {

					unset($sign_record_data);
					//所有打卡数据
					$sign_record_query = $this->_db->query("select * from oa_sign_record where `sr_status` < 3 and sr_id>$sr_id order by sr_id asc limit 3000");

					while ($sr_data = $this->_db->fetch_array($sign_record_query)) {
						$sign_record_data[] = $sr_data;
					}
					if(empty($sign_record_data)){
						break;
					}
					$last = end($sign_record_data);
					//logger::error(var_export($last,true));
					$sr_id = $last['sr_id'];
					reset($sign_record_data);
					//遍历打卡数据
					foreach($sign_record_data as $srd){
						$batch = $batch_array[$srd['sr_batch']];
						$rep_late_time = 0;
						$rep_early_time = 0;
						$rep_work_time = 0;
						//有班次规则的使用规则计算迟到、早退、出勤时长
						if(!empty($batch)){

							$sign_time_seconds = $this->__to_seconds(rgmdate($srd['sr_signtime'],'H:i'));
							$work_begin_timestamp = $batch['work_begin'];
							$work_end_timestamp = $batch['work_end'];
							if($srd['sr_type'] == self::SIGN_TYPE_ON){//签到

								$work_begin_seconds = $this->__to_seconds(rgmdate($batch['work_begin'],'H:i'));
								$come_late_range = $batch['come_late_range'];

								if($sign_time_seconds - $work_begin_seconds > $come_late_range){
									$rep_late_time = $sign_time_seconds - $work_begin_seconds - $come_late_range;
								}

							}elseif($srd['sr_type'] == self::SIGN_TYPE_OFF){//签退
								$work_end_seconds = $this->__to_seconds(rgmdate($batch['work_end'],'H:i'));
								$leave_early_range = $batch['leave_early_range'];

								if($sign_time_seconds < ($work_end_seconds-$leave_early_range)){
									$rep_early_time = ($work_end_seconds-$leave_early_range) - $sign_time_seconds ;
								}

								$ymd = rgmdate($srd['sr_created'],'Y-m-d');
								$user_record_query = $this->_db->query("select * from oa_sign_record where m_uid='{$srd['m_uid']}' and FROM_UNIXTIME(sr_created, '%Y-%m-%d') = '{$ymd}' and `sr_status` < 3 ORDER BY sr_type asc;");
								$user_record = array();
								while($ur = $this->_db->fetch_array($user_record_query)){
									$user_record[] = $ur;
								}

								if(count($user_record) == 2){
									$rep_work_time = $user_record[1]['sr_created'] - $user_record[0]['sr_created'];
								}
							}
						}

						$this->_db->query("
							UPDATE `oa_sign_record`
							SET
							`sr_sign_start_range`=10800,
							`sr_sign_end_range`=21600,
							`sr_come_late_range`={$batch['come_late_range']},
							`sr_leave_early_range`={$batch['leave_early_range']},
							`sr_late_range`={$batch['late_range']},
							`ba_type`='1',
							`sr_work_begin`=$work_begin_timestamp,
							`sr_work_end`=$work_end_timestamp,
							`rep_late_time`=$rep_late_time,
							`rep_early_time`=$rep_early_time,
							`rep_work_time`=$rep_work_time,
							`sr_updated`=1457884800
							WHERE
							`sr_id`={$srd['sr_id']};
						");
					}


				} while (!empty($sign_record_data));


			}
		}
	}


	protected function _insert_sign_task_data(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if ($this->_db->fetch_row($query)) {

			$query = $this->_db->query("SELECT * FROM oa_common_setting where cs_key='domain';");
			$domain = $this->_db->fetch_array($query);

			//查询所有的班次数据
			$batch_query = $this->_db->query("select * from oa_sign_batch where `status` < 3 ORDER BY sbid ASC");
			while ($batch_data = $this->_db->fetch_array($batch_query)) {
				if(empty($batch_data)){
					continue;
				}
				$ymd = rgmdate(time(),'Y-m-d');
				$work_begin_timestamp = rstrtotime($ymd . ' ' . rgmdate($batch_data['work_begin'], 'H:i'));
				$work_end_timestamp = rstrtotime($ymd . ' '  . rgmdate($batch_data['work_end'], 'H:i'));

				if($batch_data['sign_on'] == 1){
					$taskid = md5('sign_new'. $domain['cs_value']. $batch_data['sbid']);
					$runtime = $work_begin_timestamp-300;
					logger::error(rgmdate($runtime, 'Y-m-d H:i:s'));
					if($runtime < time()){
						$runtime += 86400;
					}

					$params = array(
						'batch_id' =>  $batch_data['sbid'],
						'content' => $batch_data['remind_on'],
						'type' => 'sign_on'
					);
					$this->_add_task($taskid, $runtime, $domain['cs_value'], 'sign_on', $params);
				}
				if($batch_data['sign_off'] == 1){
					$taskid = md5('sign_new'. $domain['cs_value']. $batch_data['sbid']);
					$runtime = $work_end_timestamp+300;
					logger::error(rgmdate($runtime, 'Y-m-d H:i:s'));
					if($runtime < time()){
						$runtime += 86400;
					}
					$params = array(
						'batch_id' =>  $batch_data['sbid'],
						'content' => $batch_data['remind_off'],
						'type' => 'sign_off'
					);
					$this->_add_task($taskid, $runtime, $domain['cs_value'], 'sign_off', $params);
				}

			}

		}
	}

	/**
	 * 格式数字为时间
	 * @param unknown $num
	 * @return string
	 */
	private function __formattime($num) {

		if (strlen($num) == 0) {
			$time = '00:00';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '00:0' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00:' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . ':' . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . ':' . $min;

			return $time;
		}
	}

	/** 把时间转成对应的秒数 */
	private function __to_seconds($hi) {

		@list($h, $i) = explode(':', $hi);

		return $h * 3600 + $i * 60;
	}

	/**
	 * 获取两个日期相差的天数
	 * @param $str_s 开始日期 Y-m-d
	 * @param $str_e 结束日期 Y-m-d
	 */
	private function __differ_days($str_s, $str_e){
		$d1 = rstrtotime($str_s);
		$d2 = rstrtotime($str_e);
		$Days = round(($d2 - $d1) / 3600 / 24);
		return $Days;
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

}
