<?php
/**
 * Chat.php
 * 消息服务
 */

namespace Com;

class Chat {

	// 创建会话url
	const CREATE_CHAT_URL='https://qyapi.weixin.qq.com/cgi-bin/chat/create?access_token=%s';
	// 获取会话
	const GET_CHAT_URL='https://qyapi.weixin.qq.com/cgi-bin/chat/get?access_token=%s&chatid=%s';
	// 修改会话信息url
	const UPDATE_CHAT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/chat/update?access_token=%s';
	// 退出会话信息
	const QUIT_CHAT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/chat/quit?access_token=%s';
	// 用户新消息免打扰
	const SETMUTE_CHAT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/chat/setmute?access_token=%s';
	// 清除消息未读状态
	const CLEARNOTIFY_URL = 'https://qyapi.weixin.qq.com/cgi-bin/chat/clearnotify?access_token=%s';
	// 发消息
	const SEND_URL = 'https://qyapi.weixin.qq.com/cgi-bin/chat/send?access_token=%s';

	// 服务号/企业号 service
	protected $_serv;

	// 群组类型
	const CHAT_TYPE_SINGLE = 'single';
	const CHAT_TYPE_GROUP = 'group';

	// 消息类型
	const MSG_TYPE_TEXT = 'text';
	const MSG_TYPE_IMG = 'image';
	const MSG_TYPE_FILE = 'file';


	/**
	 * 初始化
	 * @param class $serv 服务号/企业号 service
	 */
	public function __construct($serv) {

		$this->_serv = $serv;
	}

	public function get_chat_type_single() {

		return self::CHAT_TYPE_SINGLE;
	}

	public function get_chat_type_group() {

		return self::CHAT_TYPE_GROUP;
	}

	public function get_msg_type_text() {

		return self::MSG_TYPE_TEXT;
	}

	public function get_msg_type_img() {

		return self::MSG_TYPE_IMG;
	}

	public function get_msg_type_file() {

		return self::MSG_TYPE_FILE;
	}

	public static function create_chatid(&$chatid, $id) {

		$id = (int)$id;
		$chatid = 'vcy_' + (100000 + $id);
		return true;
	}

	/**
	 * 创建会话
	 * @param array $params 参数
	 * + string chatid 会话id
	 * + string name 会话标题
	 * + string owner 管理员 userid
	 * + array userlist userid 列表
	 * @return boolean
	 */
	public function create($params) {

		// 接口url
		$url=self::CREATE_CHAT_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		/**
		 * 会话id不能为空
		 * 会话标题不能为空
		 * 管理员 userid 不能为空
		 * 会话成员列表 不能为空
		 */
		if(empty($params['chatid']) || empty($params['name']) || empty($params['owner'])
				|| empty($params['userlist'])) {
			\Think\Log::record('post create-chat url: '.$url.'|'.http_build_query($params));
			return false;
		}

		// 强制转成数组
		$params['userlist'] = (array)$params['userlist'];

		// 获取json数据
		$data = array();
		if (!$this->_serv->post($data, $url, $params, '', 'post')) {
			\Think\Log::record('post create-chat url: '.$url);
			return false;
		}

		return true;
	}

	/**
	 * 获取会话
	 *
	 * @param array $chatinfo 会话详情
	 * @param string $chatid 会话id
	 * @return array
	 */
	public function get(&$chatinfo, $chatid) {

		// 接口 URL
		$url = self::GET_CHAT_URL;
		if (!$this->_serv->create_token_url($url, $chatid)) {
			return false;
		}

		// 获取json数据
		$data = array();
		if (! $this->_serv->post($data, $url)) {
			\Think\Log::record('get get-chat url: ' . $url);
			return false;
		}

		// 获取会话id出错
		$chatinfo = $data['chat_info'];
		if (! isset($chatinfo['chatid']) || empty($chatinfo['chatid'])) {
			\Think\Log::record('get get-chat url: ' . $url . '|' . var_export($data, true));
			return false;
		}

		return true;
	}

	/**
	 * 修改会话
	 * @param array $params 传递提交参数
	 * + string chatid 会话id
	 * + string op_user 操作人 userid
	 * + string name 会话标题
	 * + string owner 管理员 userid
	 * + array add_user_list 新增人员 userid 列表
	 * + array del_user_list 删除人员 userid 列表
	 * @return array
	 */
	public function update($params) {

		// 接口 URL
		$url = self::UPDATE_CHAT_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		/**
		 * 会话id不能为空
		 * 操作人id不能为空
		 */
		if (empty($params['chatid']) || empty($params['op_user'])) {
			\Think\Log::record('post update-chat url: '.$url.'|'.http_build_query($params));
			return false;
		}

		// 如果新增人员数据存在
		if (!empty($params['add_user_list'])) {
			$params['add_user_list'] = (array)$params['add_user_list'];
		}

		// 如果删除人员数据存在
		if (!empty($params['del_user_list'])) {
			$params['del_user_list'] = (array)$params['del_user_list'];
		}

		// 获取json数据
		$data = array ();
		if (!$this->_serv->post($data, $url, $params, '', 'POST')) {
			\Think\Log::record('post update-chat url: '.$url);
			return false;
		}

		return true;
	}

	/**
	 * 退出会话
	 * @param array $params 传递提交参数
	 * + string chatid 会话id
	 * + string op_user 操作人 userid
	 * @return array
	 */
	public function quit($params) {

		// 接口 URL
		$url = self::QUIT_CHAT_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		/**
		 * 会话id不能为空
		 * 操作人id不能为空
		 */
		if (empty($params['chatid']) || empty($params['op_user'])) {
			\Think\Log::record('post quit-chat url: '.$url.'|'.http_build_query($params));
			return false;
		}

		// 获取json数据
		$data = array ();
		if (!$this->_serv->post($data, $url, $params, '', 'POST')) {
			\Think\Log::record('post quit-chat url: '.$url);
			return false;
		}

		return true;
	}

	/**
	 * 设置用户新消息免打扰
	 * @param array $params 传入参数
	 * + array user_mute_list 免打扰用户列表
	 * + + string userid 用户 userid
	 * + + int stat 免打扰状态, 0 表示关闭; 1 表示打开
	 * @return bool
	 */
	public function setmute($params) {

		// 微信接口URL
		$url = self::SETMUTE_CHAT_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 参数验证
		if (empty($params['user_mute_list'])) {
			return false;
		}

		// 获取json数据
		$data = array ();
		if (!$this->_serv->post($data, $url, $params, '', 'POST')) {
			\Think\Log::record('post setmute-chat url: '.$url);
			return false;
		}

		return true;
	}

	/**
	 * 清除消息未读状态
	 * @param array $params POST传过来的数据
	 * + string op_user 会话所有者 userid
	 * + array chat 会话
	 * + + string type 会话类型
	 * + + string id 会话id或者用户 userid
	 * @return boolean
	 */
	public function clearnotify($params) {

		// 接口 URL
		$url = self::CLEARNOTIFY_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 如果返回了错误
		if (empty($params['op_user']) || empty($params['chat'])
				|| empty($params['chat']['type']) || empty($params['chat']['id'])) {
			\Think\Log::record('url:'.$url.'\tclearnotify error:'.http_build_query($data));
			return false;
		}

		// 获取json数据
		$data = array ();
		if (!$this->_serv->post($data, $url, $params, '', 'POST')) {
			\Think\Log::record('clearnotify url: '.$url);
			return false;
		}

		return true;
	}

	/**
	 * 发送文本消息
	 * @param string $text 消息内容
	 * @param string $to 接收者 userid
	 * @param string $from 发送者 userid
	 * @param string $chattype 群组类型, single: 单聊; group: 群聊;
	 * @param string $msgtype 消息类型, file: 文件; image: 图片; text: 文本;
	 * @return Ambigous <boolean, string>
	 */
	public function send($text, $to, $from, $chattype, $msgtype = '') {

		// 文本消息
		$params = array(
			'receiver' => array(
				'type' => $chattype,
				'id' => $to
			),
			'sender' => $from,
			'msgtype' => $msgtype
		);

		// 判断消息类型是否正确
		if (self::CHAT_TYPE_GROUP != $chattype && self::CHAT_TYPE_SINGLE != $chattype) {
			\Think\Log::record('chat type error');
			return false;
		}

		// 如果 $msgtype 为空, 则默认为文本
		if (empty($msgtype)) {
			$msgtype = self::MSG_TYPE_TEXT;
		}

		// 判断消息类型
		switch ($msgtype) {
			case self::MSG_TYPE_FILE: // 文件类型
				$params['file'] = array('media_id' => $text);
				break;
			case self::MSG_TYPE_IMG: // 图片
				$params['image'] = array('media_id' => $text);
				break;
			case self::MSG_TYPE_TEXT: // 文本
				$params['text'] = array('content' => $text);
				break;
			default: return true; // 忽略其他消息类型
		}

		// 接口 URL
		$url = self::SEND_URL;
		if (!$this->_serv->create_token_url($url)) {
			return false;
		}

		// 获取json数据
		$data = array ();
		if (!$this->_serv->post($data, $url, $params, '', 'POST')) {
			\Think\Log::record('send url: '.$url);
			return false;
		}

		return true;
	}

}
