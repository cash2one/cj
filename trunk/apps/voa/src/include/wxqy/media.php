<?php
/**
 * 微信企业图片/视频/语音/普通文件消息
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_wxqy_media extends voa_wxqy_base {

	/** 消息类型 */
	const MSG_TYPE = 'image';
	/** 获取：接口地址 */
	const GET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s';
	/** 上传：接口地址 */
	const UPLOAD_URL = 'https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s';
	/** 接口地址 */
	const POST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';

	/** 文件类型：图片 */
	const TYPE_IMAGE = 'image';
	/** 文件类型：语音 */
	const TYPE_VOICE = 'voice';
	/** 文件类型：视频 */
	const TYPE_VIDEO = 'voideo';
	/** 文件类型：普通文件 */
	const TYPE_FILE = 'file';

	/** 错误编码 */
	public $errcode = 0;
	/** 错误信息 */
	public $errmsg = '';

	/** service 方法 */
	protected $_serv;
	/** token 值 */
	protected $_token = '';
	/** 上传文件时本地文件路径 */
	protected $_file_path = '';
	/** 上传的文件类型 */
	protected $_file_type = '';

	public function __construct(&$serv) {
		$this->_serv = $serv;
	}

	/**
	 * 主动给用户发消息
	 * @param array $msg 文件消息
	 * @param array $to_users 用户的 appid
	 * @param string $token access token 值
	 * @param string $agentid 应用型代理id
	 */
	public function post($msg, $agentid, $token, $to_users = array(), $to_partys = array()) {

		$agentid = intval($agentid);
		/** 数组转成 json 字串 */
		$json = self::pack($msg, $agentid, $to_users, $to_partys);
		$res = array();
		if (!voa_h_func::get_json_by_post($res, sprintf(self::POST_URL, $token), $json)) {
			return false;
		}

		if (0 < (int)$res['errcode']) {
			logger::error('post error: '.var_export($res, true));
			return false;
		}

		return true;
	}

	/**
	 * 获取指定 media_id 的附件
	 * @param string $data (引用结果)文件内容信息
	 * <pre>
	 * + file_name 文件名
	 * + content_type 文件类型
	 * + file_data 文件数据流（经base64_encode）
	 * </pre>
	 * @param string $media_id id值
	 * @param string $token access token 值
	 */
	public function get(&$data, $media_id, $token) {

		// 真实接口url
		$api_url = self::GET_URL;
		$this->_api_url($api_url, array($token, $media_id));

		// 读取附件
		$snoopy = new snoopy();
		if (!$snoopy->fetch($api_url)) {
			$this->errcode = 101;
			$this->errmsg = '读取远程附件发生错误';
			logger::error($api_url.'|'.$media_id.'|'.$token);
			return false;
		}

		// 获取文件名
		$data['file_name'] = '';
		if ($snoopy->headers['content-disposition']) {
			if (preg_match("/filename=\"(.*?)\"/i", $snoopy->headers['content-disposition'], $match)) {
				$data['file_name'] = $match[1];
			}
		}

		// 文件类型
		$data['content_type'] = $snoopy->headers['content-type'];
		// 文件数据
		$data['file_data'] = base64_encode($snoopy->results);

		return true;
	}

	/**
	 * 上传图片
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $token token 值
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_image(&$file, $token, $file_path) {

		$this->_token = $token;
		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_IMAGE;

		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 上传音频
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $token token 值
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_voice(&$file, $token, $file_path) {

		$this->_token = $token;
		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_VOICE;

		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 上传视频
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $token token 值
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_video(&$file, $token, $file_path) {

		$this->_token = $token;
		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_VIDEO;

		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 上传普通文件
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $token token 值
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_file(&$file, $token, $file_path) {

		$this->_token = $token;
		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_FILE;

		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 公共内部上传方法
	 * @param array $file_media (引用结果)上传文件返回的结果
	 * @return boolean
	 */
	protected function _upload(&$file_media) {

		// 接口地址
		$api_url = self::UPLOAD_URL;
		$this->_api_url($api_url, array($this->_token, $this->_file_type));

		if (!$this->_check_file()) {
			return false;
		}

		// 载入snoopy
		$snoopy = new snoopy();
		$snoopy->_submit_type = 'multipart/form-data';

		// 文件信息
		$file_data = array();
		$file_data['media'] = $this->_file_path;
		// 提交数据
		$result = $snoopy->submit($api_url, array(), $file_data);
		if (!$result || 200 != $snoopy->status) {
			$this->errcode = '2002';
			$this->errmsg = '请求微信企业媒体接口发生错误';
			logger::error('$snoopy->submit error connect error: '.$api_url.'|'.$result.'|'.$snoopy->status.'|'.$this->errmsg);
			return false;
		}

		// 解析结果json
		$data = json_decode($snoopy->results, true);
		if (empty($data)) {
			$this->errcode = '2003';
			$this->errmsg = '解析微信企业媒体信息发生错误';
			logger::error('$snoopy->submit error parse json: '.$api_url.'|'.$snoopy->results.'|'.$snoopy->status.'|'.$this->errmsg);
			return false;
		}

		// 进一步检查media_id是否存在
		if (empty($data['media_id'])) {
			$this->errcode = '2004';
			$this->errmsg = '无法获取微信企业媒体文件ID';
			logger::error('$snoopy->submit error meida error: '.$api_url.'|'.$snoopy->results.'|'.$snoopy->status.'|'.$this->errmsg.'|'.print_r($data, true));
			return false;
		}

		// 输出文件结果
		$file_media = array(
			'type' => $data['type'],
			'media_id' => $data['media_id'],
			'created_at' => $data['created_at']
		);

		return true;
	}

	/**
	 * 构造访问接口的Url
	 * @param string $url_string 返回真实的url
	 * @param array $params url变量值
	 * @return string
	 */
	protected function _api_url(&$url_string, $params = array()) {

		// 将 url 第一个变量值
		array_unshift($params, $url_string);

		// 转义并输出
		$url_string = call_user_func_array('sprintf', $params);

		return true;
	}

	/**
	 * 检查本地文件有效性
	 * @return boolean
	 */
	protected function _check_file() {

		// 检查本地文件是否存在
		if (!is_file($this->_file_path)) {
			$this->errcode = '2001';
			$this->errmsg = '待上传的文件不存在';
			return false;
		}

		return true;
	}

	/**
	 * 把需要发送的数据进行打包
	 * @param string $msg
	 * @param string $agentid
	 * @param string|array $to_users
	 * @param string|array $to_partys
	 * @return string
	 */
	public static function pack($msg, $agentid, $to_users = array(), $to_partys = array()) {

		$data['touser'] = is_array($to_users) ? implode('|', (array)$to_users) : $to_users;
		$data['toparty'] = is_array($to_partys) ? implode('|', (array)$to_partys) : $to_partys;
		$data['agentid'] = $agentid;
		$data['safe'] = '0';
		/** 数组转成json字串 */
		return rjson_encode($data);
	}

}
