<?php
/**
 * Appmsg.class.php
 * 微信企业状态可改变的消息
 * $Author$
 * $Id$
 */

namespace Common\Common\Wxqy;
use Think\Log;

class Appmsg {

	// 消息类型
	const MSG_TYPE = 'appmsg';
	// 接口地址
	const POST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';
	// 更新状态地址
	const UPDATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/updatestatus?access_token=%s';
	// service 方法
	protected $_serv;

	public function __construct(&$serv) {

		E('Appmsg is not exist.');
		$this->_serv = $serv;
	}

	/**
	 * 主动给用户发消息
	 * @param array $appmsg 图文混排消息
	 * @param array $to_users 用户的 appid
	 * @param string $token access token 值
	 * @param string $agentid 应用型代理id
	 * @param int $permanent 是否永久存储, 0:否; 1:是
	 */
	public function post($appmsg, $to_users, $token, $agentid, $permanent = 1) {

		$permanent = intval($permanent);
		$agentid = intval($agentid);

		// 接触应用消息的 sid
		$sid = $appmsg['sid'];
		unset($appmsg['sid']);

		$data = array(
			'touser' => implode('|', $to_users),
			'msgtype' => self::MSG_TYPE,
			'sid' => $sid,
			'agentid' => $agentid,
			'appmsg' => $appmsg,
			'permanent' => $permanent
		);
		// 发送请求
		$result = array();
		if (!rfopen($result, sprintf(self::POST_URL, $token), rjson_encode($data), array(), 'POST')) {
			return false;
		}

		return true;
	}

	/**
	 * 更新 appmsg 状态
	 * @param array $appmsg
	 * @param array $to_users
	 * @param string $token
	 * @param string $agentid
	 * @param int $permanent
	 */
	public function update($appmsg, $to_users, $token, $agentid, $permanent = 1) {

		$permanent = intval($permanent);
		$agentid = intval($agentid);

		$data = array(
			'touser' => implode('|', $to_users),
			'msgtype' => self::MSG_TYPE,
			'sid' => $appmsg['sid'],
			'agentid' => $agentid,
			'status' => $appmsg['status'],
			'permanent' => $permanent
		);
		// 发送请求
		if (!rfopen($result, sprintf(self::UPDATE_URL, $token), rjson_encode($data))) {
			return false;
		}

		return true;
	}
}
