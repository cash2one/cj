<?php
/**
 * ChatController.class.php
 * $author$
 */

namespace QyApi\Controller\Api;
use Com\ApiSig;

class ChatController extends AbstractController {

	public function before_action() {

		try {

			// 初始化套件接口
			$this->_serv_suite = \Common\Common\WxqySuite\QyChat::instance();
			// 如果签名验证失败
			if (!$this->_serv_suite->check_signature()) {
				$this->_response($this->_serv_suite->retstr);
				return false;
			}

			// 取消息内容
			$this->_wxmsg = $this->_serv_suite->recv();
			if (empty($this->_wxmsg)) {
				$this->_response();
				return false;
			}

		} catch (\Think\Exception $e) {
			$this->_response($e);
			return false;
		} catch (\Exception $e) {
			// 记录日志
			\Think\Log::record($e->getMessage() . ':' . $e->getCode());
			$this->_response($e);
			return false;
		}

		return true;
	}

	// 套件消息处理入口
	public function Index() {

		// 回复微信的消息
		$response = '';
		if ($this->_serv_suite->is_unique) { // 如果消息重复了, 则
			$response = $this->_wxmsg['package_id'];
		} elseif ('chat' == $this->_wxmsg['agent_type']) { // 如果是聊天消息
			$this->_chat();
			$response = $this->_wxmsg['package_id'];
		}

		return $this->_response($response);
	}

	// 处理聊天消息
	protected function _chat() {

		// 读取企业信息
		$url = cfg('CYADMIN_RPC_HOST') . '/UcRpc/Rpc/EnterpriseProfile';
		$enterprise = array();
		if (!\Com\Rpc::query($enterprise, $url, 'get_by_corpid', $this->_wxmsg['to_user_name'])) {
			$enterprise = array();
			return false;
		}

		// 用户域名
		$domain = $enterprise['ep_domain'];
		// 如果只有 1 条数据
		if (1 == $this->_wxmsg['item_count']) {
			$this->_wxmsg['item'] = array($this->_wxmsg['item']);
		}

		// 遍历所有消息
		$url = cfg('PROTOCAL') . $domain . '/UcRpc/Rpc/Chat';
		$data = array();
		foreach ($this->_wxmsg['item'] as $_item) {
			if ('text' == $_item['msg_type']) { // 聊天文本消息
				\Com\Rpc::query($data, $url, 'sendmsg', $_item);
			} elseif ('image' == $_item['msg_type']) {
				\Com\Rpc::query($data, $url, 'sendimg', $_item);
			} elseif ('voice' == $_item['msg_type']) { //聊天语音消息
				\Com\Rpc::query($data, $url, 'sendvoice', $_item);
			} elseif ('event' == $_item['msg_type']) { // 会话事件
				if ('create_chat' == $_item['event']) {
					\Com\Rpc::query($data, $url, 'create_chat', $_item);
				} elseif ('update_chat' == $_item['event']) {
					\Com\Rpc::query($data, $url, 'update_chat', $_item);
				} elseif ('quit_chat' == $_item['event']) {
					\Com\Rpc::query($data, $url, 'quit_chat', $_item);
				}
			}
		}

		return true;
	}

}
