<?php
/**
 * 获取二维码 ticket
 * $Author$
 * $Id$
 */

class voa_weixin_qrcode {
	/** 获取 ticket 接口地址 */
	const TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s';
	/** 获取 ticket 所需的参数 */
	public $params;
	/** ticket 值 */
	public $ticket;
	/** 有效时间 */
	public $expire_seconds;
	/** 二维码地址 */
	const QRCODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s';
	/** service 方法 */
	protected $_serv;

	public function __construct(&$serv) {
		$this->_serv = $serv;
	}

	/**
	 * 设置获取永久二维码所需的参数
	 * @param array $data 获取 ticket 所需的参数
	 * + scene_id 场景ID值
	 */
	public function set_limit_scene_params($data) {
		$this->params = array(
			'action_name' => 'QR_LIMIT_SCENE',
			'action_info' => array(
				'scene' => array('scene_id' => $data['scene_id'])
			)
		);
	}

	/**
	 * 设置获取临时二维码所需的参数
	 * @param array $sceneid 获取 ticket 所需的参数
	 * + expire_seconds 二维码有效时间, 默认 1800, 单位(s)
	 * + scene_id 场景ID值
	 */
	public function set_scene_params($data) {
		$this->params = array(
			'expire_seconds' => empty($data['expire_seconds']) ? 1800 : $data['expire_seconds'],
			'action_name' => 'QR_SCENE',
			'action_info' => array(
				'scene' => array('scene_id' => $data['scene_id'])
			)
		);
	}

	/**
	 * 获取 ticket 值
	 * @param string $token access token
	 */
	function get_ticket($token) {
		/** 判断用户是否设置了对应参数 */
		if (empty($this->params)) {
			return false;
		}

		$url = sprintf(self::TICKET_URL, $token);
		$data = array();
		if (!$this->_serv->post($data, $url, rjson_encode($this->params))) {
			return false;
		}

		/** 如果返回的数据错误, 则 */
		if (!isset($data['ticket'])) {
			logger::error('url:'.$url."\tticket error:".http_build_query($data));
			return false;
		}

		$this->ticket = $data['ticket'];
		$this->expire_seconds = $data['expire_seconds'];
		return true;
	}

	/** 获取二维码地址 */
	public function get_qrcode_url(&$url, $scene_id, $token) {
		$this->set_scene_params(array('scene_id' => $scene_id));
		if (!$this->get_ticket($token)) {
			return false;
		}

		$url = sprintf(self::QRCODE_URL, urlencode($this->ticket));
		return true;
	}
}
