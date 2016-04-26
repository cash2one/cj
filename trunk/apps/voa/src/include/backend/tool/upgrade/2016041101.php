<?php

/**
 * 2016041101.php
 * 考勤应用迭代 V2.1.0
 * php -q tool.php -n upgrade -version 2016031202 -epid vchangyi_oa
 * Create By anything
 * $Author$
 * $Id$
 */
class execute {

	//签到类型：上班
	const SIGN_TYPE_ON = 1;

	//签到类型：下班
	const SIGN_TYPE_OFF = 2;

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

		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if (!$this->_db->fetch_row($query)) {
			return true;
		}

		//修复04-07号上线之后的考勤打卡数据
		$this->_update_sign_record();

	}



	/**
	 * 修复考勤打卡记录表数据
	 */
	protected function _update_sign_record(){
		$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_batch'");
		if ($this->_db->fetch_row($query)) {
			$query = $this->_db->query("SHOW TABLES LIKE 'oa_sign_record'");
			if ($this->_db->fetch_row($query)) {

				//先查出打卡记录表所有不重复的班次id
				$record_batch_auery =  $this->_db->query("select * from oa_sign_record where `sr_created` >= 1459958400 and `sr_status` < 3 group by sr_batch");
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
					$sign_record_query = $this->_db->query("select * from oa_sign_record where `sr_created` >= 1459958400 and `sr_status` < 3 and sr_id>$sr_id order by sr_id asc limit 3000");

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
							`sr_sign_start_range`={$batch['sign_start_range']},
							`sr_sign_end_range`={$batch['sign_end_range']},
							`sr_come_late_range`={$batch['come_late_range']},
							`sr_leave_early_range`={$batch['leave_early_range']},
							`sr_late_range`={$batch['late_range']},
							`ba_type`={$batch['type']},
							`sr_work_begin`=$work_begin_timestamp,
							`sr_work_end`=$work_end_timestamp,
							`rep_late_time`=$rep_late_time,
							`rep_early_time`=$rep_early_time,
							`rep_work_time`=$rep_work_time,
							`sr_updated`=1460363568
							WHERE
							`sr_id`={$srd['sr_id']};
						");
					}


				} while (!empty($sign_record_data));


			}
		}
	}

	/** 把时间转成对应的秒数 */
	private function __to_seconds($hi) {

		@list($h, $i) = explode(':', $hi);

		return $h * 3600 + $i * 60;
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
