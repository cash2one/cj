<?php
/**
 * 微信企业聊天套件接口
 * $Author$
 * $Id$
 */

namespace Common\Common\WxqySuite;
use Think\Log;

class QyChat extends Service {

	// 是否为重复消息
	public $is_unique = false;

	static function &instance() {

		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	public function __construct() {

	}

	/**
	 * 获取加密配置信息
	 * @param array 加密配置信息
	 * @param string 套件ID
	 * @return boolean
	 */
	public function get_sets(&$sets, $suiteid) {

		// 聊天消息套件
		$chat_suiteid = cfg('CHAT_SUITEID');
		parent::get_sets($sets, $chat_suiteid);
		$sets['corp_id'] = $suiteid;
		return true;
	}

	/**
	 * 接收消息
	 * @param bool $force 是否强制重新读取数据
	 */
	public function recv($force = false) {

		// 如果已经取过信息了, 则
		if (!empty($this->msg) && !$force) {
			return $this->msg;
		}

		// 接收并把 xml 解析成数组
		if (!$this->recv_msg($this->msg, true)) {
			return false;
		}

		// 如果数组为空, 则
		if (!$this->msg) {
			Log::record('_ERR_WX_MSG_IS_EMPTY');
			return false;
		}

		// 消息入库
		$this->is_unique = $this->is_unique();
		return $this->msg;
	}

	// 判断消息是否重复
	public function is_unique() {

		$serv_wm = D('Common/WeixinMsg', 'Service');
		// 如果消息中有 package_id, 则
		if (isset($this->msg['package_id'])) {
			// 根据 msg_id 读取数据
			if ($result = $serv_wm->get_by_packageid($this->msg['package_id'])) {
				return true;
			}

			$this->insert_wxqy_msg();
			return false;
		}

		return false;
	}

}
