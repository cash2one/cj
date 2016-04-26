<?php
/**
 * 消息推送
 * $Author$
 * $Id$
 */

class voa_backend_cron_pushmsgbycname extends voa_backend_base {
	/** 外部传入的参数 */
	private $__opts = array();
	/** 每次获取的数据条数 */
	private $__perpage = 100;
	/** 模板消息表 service */
	private $__serv_mq;

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
		$this->__serv_mq = &service::factory('voa_s_oa_msg_queue', array('pluginid' => 0));
		$list = $this->__serv_mq->fetch_unsend_by_sendtime(startup_env::get('timestamp') + 60, $this->__perpage);

		/** 开始发送 */
		$serv_qy = voa_wxqy_service::instance();
		foreach ($list as $mq) {
			$result = false;
			switch ($mq['mq_msgtype']) {
				case voa_h_qymsg::MSGTYPE_TEXT:
					$result = $serv_qy->post_text($mq['mq_message'], $mq['mq_agentid'], $mq['mq_touser']);
					break;
				case voa_h_qymsg::MSGTYPE_NEWS:
					$result = $serv_qy->post_news(unserialize($mq['mq_message']), $mq['mq_agentid'], $mq['mq_touser']);
					break;
				default:break;
			}

			if ($result) {
				$succeed[] = $mq['mq_id'];
			} else {
				if (0 < $mq['mq_failtimes']) {
					$failed[] = $mq['mq_id'];
				} else {
					$retry[] = $mq['mq_id'];
				}
			}
		}

		/** 更新发送成功记录状态 */
		if (!empty($succeed)) {
			$this->__serv_mq->update_by_ids(array(
				'mq_status' => voa_d_oa_msg_queue::STATUS_SUCCEED
			), $succeed);
		}

		/** 更新失败记录状态 */
		if (!empty($failed)) {
			$this->__serv_mq->update_by_ids(array(
				'mq_status' => voa_d_oa_msg_queue::STATUS_FAILED
			), $failed);
		}

		/** 更新为重复状态 */
		if (!empty($retry)) {
			$this->__serv_mq->increase_times_by_ids($retry);
		}

		$count = count($list);
		return true;
	}
}
