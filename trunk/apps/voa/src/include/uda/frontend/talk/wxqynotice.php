<?php
/**
 * voa_uda_frontend_talk_wxqynotice
 * 发送微信信息
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_wxqynotice extends voa_uda_frontend_talk_abstract {
	// session 对象
	protected $_session = null;
	// 接收人uid
	protected $_receive_uid = 0;
	// 商品id
	protected $_goods_id = 0;
	// 客户id
	protected $_tv_uid = 0;


	public function __construct() {

		parent::__construct();
	}

	/**
	 * 执行
	 * @param array $in 输入
	 * @param array $out 输出
	 * @return boolean
	 */
	public function execute($in, &$out = null) {

		$this->_receive_uid = $in['uid'];
		$this->_goods_id = $in['goods_id'];
		$this->_tv_uid = $in['tv_uid'];
		$this->_send_wxqy_notice();

		return true;
	}

	public function set_session($session) {

		$this->_session = $session;
	}

	/**
	 * 统一发送出口方法
	 * @return boolean
	 */
	protected function _send_wxqy_notice() {

		// 没有需要接收消息的人
		if (empty($this->_receive_uid)) {
			return true;
		}

		// 需要接收消息的人员微信openid
		$user = voa_h_user::get($this->_receive_uid);

		// 浏览详情的授权链接
		$view_url = '';
		$this->get_view_url($view_url, $this->_goods_id, $this->_tv_uid);

		// 微信消息内容，数组形式，便于后面组织排版，每个键名一行
		$content = array();
		$content[] = '消息提醒:';
		$content[] = rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i');
		$content[] = '您有一条未读的咨询消息';
		$content[] = '<a href="'.$view_url.'">点击进入对话</a>';

		// 构造微信消息发送需要的数据
		$data = array(
			'mq_touser' => $user['m_openid'],
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin_setting['agentid'],
			'mq_message' => implode("\r\n", $content)
		);
		// 推入待发送队列
		voa_h_qymsg::push_send_queue($data);
		// 将队列ID写入成员变量，便于调用时提取写入cookie
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $this->_session);

		return true;
	}

}
