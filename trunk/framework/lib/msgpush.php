<?php
/**
 * 消息推送
 *
 */

class msgpush {

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/** XingeApp 实例 */
	protected $_xinge = null;

	/** batch tag 最大值 */
	protected $_batch_tag_max = 20;

	/**
	 * &get_instance
	 * 获取一个短信发送类的实例
	 *
	 * @return object
	 */
	public static function &get_instance() {

		if (!self::$_instance) {
			self::$_instance = new msgpush();
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 *
	 * @param  mixed $group
	 * @return void
	 */
	public function __construct() {

		// do nothing.
	}

	/**
	 * 初始化
	 * @param array $cfg
	 * @return boolean
	 */
	public function init($cfg) {

		if (isset($cfg['batch_tag_max'])) {
			$this->_batch_tag_max = $cfg['batch_tag_max'];
		}

		if (!isset($cfg['xg_access_id']) || !isset($cfg['xg_secret_key'])) {
			return false;
		}

		$this->_xinge = new XingeApp($cfg['xg_access_id'], $cfg['xg_secret_key']);
	}

	public function send_to_ios_android($data, $device) {

		echo "tosuer: {$data['touser']} ===msg: {$data['message']} ====title:{$data['title']}======device: $device\n";

		if ($device == XingeApp::DEVICE_ANDROID) {
			$android_conf = array('xg_access_id'=>'2100062847', 'xg_secret_key'=>'5d11fffd90c43b6724b4f3355df6b81b');
			$this->init($android_conf);
			$android_result = $this->send($data, XingeApp::DEVICE_ANDROID);
			echo "android\n";

			if ($android_result['ret_code'] == 0) {
				echo "succeed\n";

				return true;
			} else {
				echo "faild! code:{$android_result['red_code']}, msg: {$android_result['err_msg']}\n";

				return false;
			}
		} elseif ($device == XingeApp::DEVICE_IOS) {

			$ios_conf = array('xg_access_id'=>'2200062848', 'xg_secret_key'=>'514b6da1d361cf6607bee1baaa53d34a');
			$this->init($ios_conf);
			$ios_result = $this->send($data, XingeApp::DEVICE_IOS);
			echo "ios\n";
			//var_dump($ios_result);
			if ($ios_result) {
				echo "succeed\n";

				return true;
			} else {
				echo "faild! code:{$ios_result['red_code']}, msg: {$ios_result['err_msg']}\n";

				return false;
			}

		}
		return false;
	}

	/**
	 * 发送消息
	 * @param string $account 接口消息的账号
	 * @param string $msg 消息内容
	 * @param string $title 消息标题
	 * @return Ambigous <multitype:number string , mixed>
	 */
	public function send($data, $device = XingeApp::DEVICE_ANDROID) {
		$account = $data['touser'];
		unset($data['touser']);
		if ($device == XingeApp::DEVICE_IOS) {

			$mess = new MessageIOS();
			$mess->setAlert($data['title']);
			//unset($data['title']);
			//$mess->setAlert(array('title'=>$title, 'content'=>$msg));
			$mess->setBadge($data['notificationtotal']);
			unset($data['notificationtotal']);

			$mess->setSound("beep.wav");

			$mess->setCustom($data);
			$acceptTime1 = new TimeInterval(0, 0, 23, 59);
			$mess->addAcceptTime($acceptTime1);

			//$ret = $this->_xinge->PushAllDevices($device, $mess, XingeApp::IOSENV_DEV);
			/*
			$mess = new Message();
			$mess->setTitle($data['title']);
			$mess->setContent($data['message']);
			unset($data['title']);
			unset($data['message']);
			$mess->setCustom($data);
			$mess->setType(Message::TYPE_MESSAGE);*/

			//$ret = $this->_xinge->PushSingleAccount($device, $account, $mess, XingeApp::IOSENV_DEV);
			//$ret = $this->_xinge->PushAllDevices($device, $mess, XingeApp::IOSENV_DEV);
			$ret = $this->_xinge->PushTags($device, array('vt.vchangyi.com'), 'OR', $mess, XingeApp::IOSENV_DEV);
		} else {
			//$ret = $this->_xinge->PushAllDevices($device, $mess);
			$mess = new Message();
			$mess->setTitle($data['title']);
			$mess->setContent($data['message']);
			unset($data['title']);
			unset($data['message']);
			$mess->setCustom($data);
			$mess->setType(Message::TYPE_NOTIFICATION);
			#含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
			$style = new Style(2,0,0,1,0);
			$action = new ClickAction();
			$action->setActionType(ClickAction::TYPE_ACTIVITY);
			//$action->setUrl("http://xg.qq.com");
			$action->setActivity('com.changyi.cyoa.activity.MainTabActivity');
			#打开url需要用户确认
			//$action->setComfirmOnUrl(1);
			$mess->setStyle($style);
			$mess->setAction($action);
			//$ret = $this->_xinge->PushSingleAccount($device, $account, $mess);
			//$ret = $this->_xinge->PushAllDevices($device, $mess);
			$ret = $this->_xinge->PushTags($device, array('vt.vchangyi.com'), 'OR', $mess, 0);
		}
		return $ret;
	}

	/**
	 * 发送消息给所有人
	 * @param string $msg 消息内容
	 * @param string $title 消息标题
	 * @return Ambigous <multitype:number string , mixed>
	 */
	function send_to_all($msg, $title = '') {

		$mess = new Message();
		$mess->setTitle($title);
		$mess->setContent($msg);
		$mess->setType(Message::TYPE_MESSAGE);
		$ret = $this->_xinge->PushAllDevices(XingeApp::DEVICE_ANDROID, $mess);
		return $ret;
	}

	/**
	 * 发送消息给 tag 相关的用户
	 * @param array $tags tag 数组
	 * @param string $msg 消息内容
	 * @param string $title 消息标题
	 * @return Ambigous <multitype:number string , mixed>
	 */
	function send_by_tags($tags, $msg, $title = '') {

		$mess = new Message();
		$mess->setTitle($title);
		$mess->setContent($msg);
		$mess->setType(Message::TYPE_MESSAGE);
		$ret = $this->_xinge->PushTags(XingeApp::DEVICE_ANDROID, $tags, 'OR', $mess);
		return $ret;
	}

	/**
	 * 设置 token 对应的标签
	 * @param $token2tag tag 和 token 对应关系, 每次最多 20 个
	 */
	function batch_set_tag($token2tag) {
		if (count($token2tag) > $this->_batch_tag_max) {
			return false;
		}

		// 切记把这里的示例tag和示例token修改为你的真实tag和真实token
		$pairs = array();
		foreach ($token2tag as $token => $tag) {
			array_push($pairs, new TagTokenPair($tag, $token));
		}

		$ret = $this->_xinge->BatchSetTag($pairs);
		return $ret;
	}

	/**
	 * 删除 token 对应的标签
	 * @param $token2tag tag 和 token 对应关系, 每次最多 20 个
	 * @return Ambigous <multitype:number string , mixed>
	 */
	function batch_del_tag($token2tag) {
		if (count($token2tag) > $this->_batch_tag_max) {
			return false;
		}

		// 切记把这里的示例tag和示例token修改为你的真实tag和真实token
	    $pairs = array();
	    foreach ($token2tag as $token => $tag) {
	    	array_push($pairs, new TagTokenPair($tag, $token));
	    }

	    $ret = $this->_xinge->BatchDelTag($pairs);
	    return $ret;
	}

}
