<?php
/**
 * wxqynotice.php
 * 发布企业微信通知（写入消息发送队列）
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_wxqynotice extends voa_uda_frontend_workorder_abstract {

	/** 工单主表信息 */
	protected $_workorder = array();
	/** 工单收单人列表信息 */
	protected $_receiver_list = array();
	/** 当前请求的工单ID */
	protected $_request_woid = 0;
	/** 当前请求的人员ID */
	protected $_request_uid = 0;
	/** 需要接收消息的uid列表 */
	protected $_receive_uids = array();
	/** 所有用户信息列表 */
	protected $_user_list = array();
	/** session操作对象 */
	private $__session = null;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 新建派单时发送给 接单人的通知
	 * @param number $woid 工单ID
	 * @param number $request_uid 请求人ID
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	public function create($woid, $request_uid, $session_obj) {

		// 初始化成员参数
		$this->_init($woid, $request_uid, $session_obj);

		// 待接收消息的人员列表
		$this->_receive_uids = array();
		foreach ($this->_receiver_list as $_op) {

			// 除当前人和派单人之外
			if ($this->_workorder['uid'] == $_op['uid'] || $request_uid == $_op['uid']) {
				continue;
			}
			$this->_receive_uids[] = $_op['uid'];
		}

		// 发送消息
		$this->_send_wxqy_notice('您有一个新的工单');
	}

	/**
	 * 收单人拒绝接单
	 * @param number $woid 工单ID
	 * @param number $request_uid 当前请求人ID
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	public function refuse($woid, $request_uid, $session_obj) {

		// 初始化成员参数
		$this->_init($woid, $request_uid, $session_obj);
		// 待接收消息的人员
		// 只发给派单人
		$this->_receive_uids = array($this->_workorder['uid']);
		// 发送消息
		$this->_send_wxqy_notice('您发起的工单已被拒绝');
	}

	/**
	 * 接单人确认收单
	 * @param number $woid 工单ID
	 * @param number $request_uid 当前请求人ID
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	public function confirm($woid, $request_uid, $session_obj) {

		// 初始化成员参数
		$this->_init($woid, $request_uid, $session_obj);
		// 待接收消息的人员
		// 只发给派单人
		$this->_receive_uids = array($this->_workorder['uid']);
		// 发送消息
		$this->_send_wxqy_notice('工单已被接受');
	}

	/**
	 * 完成派单
	 * @param number $woid 工单ID
	 * @param number $request_uid 当前请求人ID
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	public function complete($woid, $request_uid, $session_obj) {

		// 初始化成员参数
		$this->_init($woid, $request_uid, $session_obj);
		// 待接收消息的人员
		// 完成派单只发给派单人
		$this->_receive_uids = array($this->_workorder['uid']);
		// 发送消息
		$this->_send_wxqy_notice('工单已完成');
	}

	/**
	 * 派单人撤销派单
	 * @param number $woid
	 * @param number $request_uid
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	public function cancel($woid, $request_uid, $session_obj) {

		// 初始化成员参数
		$this->_init($woid, $request_uid, $session_obj);
		// 待接收消息的人员
		$this->_receive_uids = array();
		foreach ($this->_receiver_list as $_op) {

			// 撤销工单发给除派单人自己以及当前请求人之外的所有人
			if ($this->_workorder['uid'] == $_op['uid'] || $_op['uid'] == $request_uid) {
				continue;
			}
		}
		// 发送消息
		$this->_send_wxqy_notice('工单已被撤回');
	}

	/**
	 * 初始化环境成员变量
	 * @param number $woid 工单id
	 * @param number $request_uid 请求人uid
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	protected function _init($woid, $request_uid, $session_obj) {

		// 初始化待接收消息的人员列表为空
		$this->_receive_uids = array();

		// 初始化当前请求人ID
		$this->_request_uid = $request_uid;
		// 初始化当前请求的工单ID
		$this->_request_woid = $woid;
		// session 操作对象
		$this->__session = $session_obj;

		// 当前请求的工单主表信息
		$d_workorder = new voa_d_oa_workorder();
		$this->_workorder = $d_workorder->get($woid);

		// 当前请求的工单收单人列表
		$d_workorder_receiver = new voa_d_oa_workorder_receiver();
		$this->_receiver_list = $d_workorder_receiver->list_by_conds(array('woid' => $woid));

		// 当前请求的工单所有参与的人员uid
		$uids = array();
		// 派单人
		$uids[$this->_workorder['uid']] = $this->_workorder['uid'];
		// 整理收单人
		foreach ($this->_receiver_list as $_op) {
			if (!isset($uids[$_op['uid']])) {
				$uids[$_op['uid']] = $_op['uid'];
			}
		}

		// 整理用户信息
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($uids);
		// 将用户信息推入，便于调用
		voa_h_user::push($users);
	}

	/**
	 * 统一发送出口方法
	 * @return boolean
	 */
	protected function _send_wxqy_notice($subject) {

		// 没有需要接收消息的人
		if (empty($this->_receive_uids)) {
			return true;
		}

		// 需要接收消息的人员微信openid
		$openids = array();
		foreach ($this->_receive_uids as $_uid) {
			$user = voa_h_user::get($_uid);
			$openids[] = $user['m_openid'];
		}

		// 浏览详情的授权链接
		$view_url = '';
		$this->get_view_url($view_url, $this->_request_woid);

		// 微信消息内容，数组形式，便于后面组织排版，每个键名一行
		$content = array();
		$content[] = $subject;
		$content[] = '工单编号: '.$this->_request_woid;
		$content[] = '派单时间: '.rgmdate($this->_workorder['ordertime'], 'Y-m-d H:i');
		$content[] = '联系人员: '.rhtmlspecialchars($this->_workorder['contacter']);
		$content[] = '联系电话: '.rhtmlspecialchars($this->_workorder['phone']);
		$content[] = '联系地址: '.rhtmlspecialchars($this->_workorder['address']);
		$content[] = '';
		$content[] = '<a href="'.$view_url.'">点击查看详情</a>';

		// 构造微信消息发送需要的数据
		$data = array(
			'mq_touser' => implode('|', $openids),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->plugin_setting['agentid'],
			'mq_message' => implode("\r\n", $content)
		);
		// 推入待发送队列
		voa_h_qymsg::push_send_queue($data);
		// 将队列ID写入成员变量，便于调用时提取写入cookie
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $this->__session);

		return true;
	}

}
