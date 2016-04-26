<?php
/**
 * 微信企业图片/视频/语音/普通文件消息
 * $Author$
 * $Id$
 */

namespace Common\Common\Wxqy;
use Think\Log;

class Media {

	// 消息类型
	const MSG_TYPE = 'image';
	// 获取：接口地址
	const GET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s';
	// 上传：接口地址
	const UPLOAD_URL = 'https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s';
	// 接口地址
	const POST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';
	// 文件类型：图片
	const TYPE_IMAGE = 'image';
	// 文件类型：语音
	const TYPE_VOICE = 'voice';
	// 文件类型：视频
	const TYPE_VIDEO = 'voideo';
	// 文件类型：普通文件
	const TYPE_FILE = 'file';

	// service 方法
	protected $_serv;
	// 上传文件时本地文件路径
	protected $_file_path = '';
	// 上传的文件类型
	protected $_file_type = '';

	public function __construct(&$serv) {

		$this->_serv = $serv;
	}

	/**
	 * 主动给用户发消息
	 * @param array $data 文件消息
	 * @param array $to_users 用户的 appid
	 * @param string $agentid 应用型代理id
	 */
	public function post($data, $agentid, $to_users = array(), $to_partys = array()) {

		$agentid = intval($agentid);
		// 数组转成 json 字串
		$json = self::pack($data, $agentid, $to_users, $to_partys);
		$result = array();
		// 接口 URL
		$url = self::POST_URL;
		$this->_serv->create_token_url($url);
		// 读取数据
		if (!rfopen($result, $url, $json, array(), 'POST')) {
			return false;
		}

		// 如果出错了, 则记录日志
		if (0 < (int)$result['errcode']) {
			Log::write('post error: '.var_export($result, true)."\nurl: ".$url."\nparams: ".$json);
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
	 */
	public function get(&$data, $media_id) {

		// 真实接口url
		$url = self::GET_URL;
		$this->_serv->create_token_url($url, $media_id);

		// 读取附件
		$snoopy = null;
		if (!rfopen($snoopy, $url, null, null, 'GET', true, false)) {
			return false;
		}

		// 获取文件名
		$data['file_name'] = '';
		parse_headers($header, $cookies, $snoopy->headers);

		$snoopy->headers = $header;
		if ($snoopy->headers['content-disposition']) {
			if (preg_match("/filename=\"(.*?)\"/i", $snoopy->headers['content-disposition'], $match)) {
				$data['file_name'] = $match[1];
			}
		}

		// 文件类型
		$data['content_type'] = $snoopy->headers['content-type'];
		// 文件大小
		$data['file_size'] = strlen($snoopy->results);
		// 文件数据
		$data['file_data'] = base64_encode($snoopy->results);

		return true;
	}

	/**
	 * 上传图片
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_image(&$file, $file_path) {

		// 初始化
		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_IMAGE;

		// 上传文件
		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 上传音频
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_voice(&$file, $file_path) {

		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_VOICE;

		// 上传文件
		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 上传视频
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_video(&$file, $file_path) {

		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_VIDEO;

		// 上传文件
		if (!$this->_upload($file)) {
			return false;
		}

		return true;
	}

	/**
	 * 上传普通文件
	 * @param string $file (引用结果)上传返回的结果
	 * @param string $file_path 待上传的文件物理路径
	 * @return boolean
	 */
	public function upload_file(&$file, $file_path) {

		$this->_file_path = $file_path;
		$this->_file_type = self::TYPE_FILE;

		// 上传文件
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
		$url = self::UPLOAD_URL;
		$this->_serv->create_token_url($url, $this->_file_type);

		// 检查文件是否存在
		if (!$this->_check_file()) {
			return false;
		}

		// 载入 Snoopy 类
		$snoopy = new \Org\Net\Snoopy();
		// 文件信息
		$file_data = array('media' => array($this->_file_path));
		// 上传文件
		if (!rfopen($file_media, $url, null, null, 'POST', false, true, $file_data)) {
			return false;
		}

		// 进一步检查 media_id 是否存在
		if (empty($file_media['media_id'])) {
			E('_ERR_MEDIA_ID_IS_EMPTY');
			Log::record('$snoopy['.$method.'] error meida error: '.$url.'|'.print_r($file_media, true));
			return false;
		}

		return true;
	}

	/**
	 * 检查本地文件有效性
	 * @return boolean
	 */
	protected function _check_file() {

		$file = is_array($this->_file_path) && isset($this->_file_path['path']) ? $this->_file_path['path'] : $this->_file_path;
		// 检查本地文件是否存在
		if (!is_file($file)) {
			E('_ERR_FILE_IS_NOT_EXIST');
			return false;
		}

		return true;
	}

	/**
	 * 把需要发送的数据进行打包
	 * @param string $data 消息内容
	 * @param string $agentid 应用id
	 * @param string|array $to_users 接收人uesrid
	 * @param string|array $to_partys 接收部门id
	 * @return string
	 */
	public static function pack($data, $agentid, $to_users = array(), $to_partys = array()) {

		$data['touser'] = is_array($to_users) ? implode('|', (array)$to_users) : $to_users;
		$data['toparty'] = is_array($to_partys) ? implode('|', (array)$to_partys) : $to_partys;
		$data['agentid'] = $agentid;
		$data['safe'] = '0';
		// 数组转成json字串
		return rjson_encode($data);
	}

}
