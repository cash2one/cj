<?php
/**
 * 消息推送
 * $Author$
 * $Id$
 */

class voa_backend_cron_productive extends voa_backend_base {
	/** 外部传入的参数 */
	private $__opts = array();
	/** 每次获取的数据条数 */
	private $__perpage = 100;
	/** 模板消息表 service */
	private $__serv;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	/**
	 * 入口函数
	 *
	 * @access public
	 * @return void
	 */
	public function main() {
		$this->_lock();
		$this->_log('--- begin 模板消息发送 --------------');

		try {
			/** 初始化数据库配置 */
			startup_env::set('domain', $this->__opts['cname']);
			if (!voa_h_conf::init_db()) {
				throw new Exception('db config init error.', 100);
			}

			/** 循环发送 */
			$count = 0;
			do {
				$this->__send($count);
			} while ($count >= $this->__perpage);

		} catch (Exception $e) {
			$this->_log($e->getMessage());
		}

		$this->_log('--- end 模板消息发送 --------------');
		$this->_unlock();
	}

	/** 重写 lock 方法 */
	protected function _lock() {

		$md5 = md5(startup_env::get('domain'));
		$this->_log_dir = APP_PATH.'/logs/'.$this->_backend_type.'/'.substr($md5, 2).'/'.$this->_class_name;
		if (!is_dir($this->_log_dir)) {
			mkdir($this->_log_dir, 0777, true);
		}

		parent::_lock();
	}

	/** 消息发送 */
	private function __send(&$count) {
		/** 读取队列 */
		$tasks = &service::factory('voa_s_oa_productive_tasks', array('pluginid' => 0));
		// 读取执行中的， 本时间段需要提醒的。
		$condi = array('ptt_execution_status'=>'2',
				'ptt_alert_time'=>rgmdate(startup_env::get('timestamp')+120, 'H:00'),
				'ptt_end_date'=>array(startup_env::get('timestamp'), '>'),
		);

		$data = $tasks->fetch_by_conditions($condi, $count, $this->__perpage);
		foreach ($data as $item) {
			if ($item['ptt_repeat_frequency'] != 'no') {
				$current_date = rgmdate(startup_env::get('timestamp'), 'Y-m-d');
				$last_execution_date = rgmdate($item['ptt_last_execution_time'], 'Y-m-d');
				// 上次执行日期不是当前日期则下一步
				if ($last_execution_date != $current_date) {
					$repeat_type = explode('_', $item['ptt_repeat_frequency']);
					// 重复执行计划的类型
					if ($repeat_type[0] == 'day') {// 每天
						if ($repeat_type[1] == 1) {
							$this->_execution($item);
						} elseif ($repeat_type[1] > 1){
							$next_execution_date = rgmdate($item['ptt_last_execution_time'] + 86400 * $repeat_type[1], 'Y-m-d');
							//下一次执行日期等于当前日期则执行
							if ($next_execution_date == $current_date) {
								$this->_execution($item);
							}

						}
					}elseif ($repeat_type[0] == 'week') {// 每周
						$current_week = rgmdate(startup_env::get('timestamp'), 'w');
						if ($current_week == $repeat_type[1]) {
							$this->_execution($item);
						}
					} elseif ($repeat_type[0] == 'mon') {// 每月
						$current_day = rgmdate(startup_env::get('timestamp'), 'd');
						if ($current_day == $repeat_type[1]) {
							$this->_execution($item);
						}
					}
				}
			}
		}
		$count = count($data);
		return true;
	}

	private function _execution($item) {
		$item['ptt_parent_id'] = $item['ptt_id'];
		$item['ptt_last_execution_time'] = $item['ptt_last_execution_time'];
		$item['ptt_repeat_frequency'] = 'no';
		unset($item['ptt_id']);
		unset($item['ptt_alert_time']);
		$id = $this->_service_single('productive_tasks', $this->_module_plugin_id, 'insert', $item, true);
		$item['ptt_id'] = $id;
		$ins_insert = &uda::factory('voa_uda_frontend_productive_insert');
		$ins_insert->run_task($item);
	}
}
