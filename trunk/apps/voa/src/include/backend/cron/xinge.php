<?php
/**
 * 消息推送
 * $Author$
 * $Id$
 */

class voa_backend_cron_xinge extends voa_backend_base {
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
		$uda = &uda::factory('voa_uda_uc_xinge');
		$this->__serv = &service::factory('voa_s_oa_xinge_queue', array('pluginid' => 0));

		$list = $uda->get_unsend_list(startup_env::get('timestamp') + 60, $this->__perpage);
		/** 开始发送 */
		$msgpush = msgpush::get_instance();

		foreach ($list as $item) {
			$result = false;
			$data = array();
			$data['touser'] = $item['xgq_touser'];
			$data['message'] = $item['xgq_message'];
			$data['title'] = $item['xgq_title'];
			//$sets = voa_h_cache::get_instance()->get('setting', 'oa');$sets['sitename']
			$data['fromuser'] = $item['xgq_fromuser'];
			$data['msgtype'] = $item['xgq_msgtype'];
			$data['pluginid'] = $item['xgq_pluginid'];
			$data['itemid'] = $item['xgq_itemid'];
			$data['notificationtotal'] = $item['xgq_notificationtotal'];
			$data['sendtime'] = time();//$item['xgq_sendtime'];

			$result = $msgpush->send_to_ios_android($data, $item['xgq_devicetype']);

			if ($result) {
				$this->_log('--- 成功发送 --------------'.$item['xgq_title']);

				$succeed[] = $item['xgq_id'];
			} else {
				$this->_log('--- 失败发送 --------------'.$item['xgq_title']);

				if (0 < $item['xgq_failtimes']) {
					$failed[] = $item['xgq_id'];
				} else {
					$retry[] = $item['xgq_id'];
				}
			}
		}

		/** 更新发送成功记录状态 */
		if (!empty($succeed)) {
			$this->__serv->update_by_ids(array(
				'xgq_status' => voa_d_oa_xinge_queue::STATUS_SUCCEED
			), $succeed);
		}

		/** 更新失败记录状态 */
		if (!empty($failed)) {
			$this->__serv->update_by_ids(array(
				'xgq_status' => voa_d_oa_xinge_queue::STATUS_FAILED
			), $failed);
		}

		/** 更新为重复状态 */
		if (!empty($retry)) {
			$this->__serv->increase_times_by_ids($retry);
		}

		$count = count($list);
		return true;
	}
}
