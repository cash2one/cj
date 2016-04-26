<?php
/**
 * CommonAttachmentService.class.php
 * $author$
 */

namespace PubApi\Service;
use Org\Net\UploadFile;

class CommonAttachmentService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("PubApi/CommonAttachment");
	}

	/**
	 * 附件上传
	 * @param $attachment 待上传附件信息
	 * @param array $extend
	 * @return bool|string
	 */
	public function upload(&$attachment, $extend = array()) {

		// 实例化上传类
		$upload = new UploadFile();

		// 设置上传文件大小,默认为-1，不限制上传大小
		$upload->maxSize = cfg('UPLOAD_MAXSIZE');

		// 允许上传的文件后缀（留空为不限制），使用数组设置，默认为空数组
		$upload->allowExts = array();
		// 允许上传的mime文件类型（留空为不限制），使用数组设置，默认为空数组
		$upload->allowTypes = array();

		// 设置附件上传目录
		$hostarr = explode('.', $_SERVER['HTTP_HOST']);
		$domain = rawurlencode($hostarr[0]);
		$md5 = md5($domain);

		// 文件缓存路径
		$sitedir = '..'.cfg('STATICDIR').substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.date("Y").'/'.date("m").'/';

		// 目录不存在，创建目录
		if (!file_exists($sitedir)) {
			rmkdir($sitedir);
		}
		$upload->savePath = $sitedir;

		// 设置是否开启需要生成缩略图，仅对图像文件有效
		$upload->thumb = true;
		// 设置引用图片类库包路径
		$upload->imageClassPath = '@.ORG.Image';
		// 生产2张缩略图，设置需要生成缩略图的文件前缀
		$upload->thumbPrefix = cfg('THUMB.PREFIX');
		// 设置缩略图最大宽度
		$upload->thumbMaxWidth = cfg('THUMB.MAX_WIDTH');
		// 设置缩略图最大高度
		$upload->thumbMaxHeight = cfg('THUMB.MAX_HEIGHT');

		// 上传文件的文件名保存规则
		$upload->saveRule = 'uniqid';

		// 如果存在同名文件是否进行覆盖
		$upload->uploadReplace = true;
		// 如果生成缩略图，是否删除原图
		$upload->thumbRemoveOrigin = false;

		// 上传失败
		if (!$upload->upload()) {
			// 捕获上传异常
			$this->_set_error($upload->getErrorMsg());
			return false;
		}

		// 取得成功上传的文件信息
		$uploadList = $upload->getUploadFileInfo();

		// 组装入库数据
		$attachment['m_uid'] = $extend['m_uid'];
		$attachment['m_username'] = $extend['m_username'];
		$attachment['at_filename'] = $uploadList[0]['name'];
		$attachment['at_filesize'] = $uploadList[0]['size'];
		$attachment['at_description'] = $uploadList[0]['extension'].' || '.$uploadList[0]['type'];
		$attachment['at_attachment'] = rgmdate(NOW_TIME, "Y/m").'/'.$uploadList[0]['savename'];
		$attachment['at_status'] = $this->_d->get_st_create();
		$attachment['at_created'] = NOW_TIME;

		// 入库失败
		if (!$id = $this->_d->insert($attachment)) {
			E(L('_ERR_INSERT_ERROR'));
			return false;
		}

		// 拼接附件id
		$attachment['at_id'] = $id;
		unset($attachment['at_status']);

		// 附件访问地址
		$cache = \Common\Common\Cache::instance();
		$setting = $cache->get('Common.setting');
		$attachment['url'] = cfg('PROTOCAL') . $setting['domain'] . '/attachment/read/' . $id;

		return true;
	}

	/**
	 * [params_check 检测参数合法性]
	 *
	 * @param [array] &$params [传递的参数]
	 * @return [bool] [返回值]
	 */
	public function params_check(&$params) {

		if (! isset($params['serverid']) || empty($params['serverid'])) {
			$this->_set_error('_ERR_SERVICEID_NOT_EXISTS');
			return false;
		}

		$params['serverid'] = trim($params['serverid']);
		return true;
	}

	/**
	 * [get_by_serverid description]
	 *
	 * @param [type] $params [description]
	 * @return [type] [description]
	 */
	public function get_by_serverid($params, $extend) {

		// 媒体文件内容
		$file = array();
		$serv = &\Common\Common\Wxqy\Service::instance();

		if (! $serv->get_media($file, $params['serverid'])) {
			E($serv->errcode . ':' . $serv->errmsg);
			return false;
		}

		// 写入本地附件文件
		$re = $this->save_local($file, $extend, $params);
		return $re;
	}

	/**
	 * [save_local 写入本地附件文件]
	 *
	 * @param [array] $file [传入的文件]
	 * @param [array] $extend [用户的信息]
	 * @param [array] $extend [前端传递过来的参数]
	 * @return [bool] [返回值]
	 */
	private function save_local($file, $extend, $params) {

		// base64编码后的文件内容
		$file_field = $file['file_data'];
		// 暂时中转一下存储的路径
		$z_path = get_sitedir();
		$e_path = str_replace('/thinkphp/Apps/Runtime/Temp', '\apps\voa/data/attachments', $z_path);

		$config = array(
			'save_dir_path' => $e_path,
			'allow_files' => cfg('FILE_TYPE'),
			'file_name_format' => 'auto',
			'max_size' => 20480000,
			'source_name' => $file['file_name']
		);
		// 上传类型
		$type = 'local';
		// 自 content-type 获取文件扩展名
		$file_ext = $this->__get_ext($file['content_type']);
		if ($file_ext && strpos($config['source_name'], '.') === false) {
			$config['source_name'] .= '.' . $file_ext;
		}

		// 写入文件到附件目录
		$upload = new \Com\Upload($file_field, $config, $type);
		$att = $upload->get_file_info();
		// 如果发生错误
		if (empty($att['error']) || rstrtolower($att['error']) != 'success') {
			if (empty($att['error'])) E('_ERR_FILE_UP');
			else E('5050:' . $att['error']);
			return false;
		}

		// 待写入附件表的数据
		$attachment = array(
			'm_uid' => $extend['uid'],
			'm_username' => $extend['username'],
			'at_filename' => $att['source_name'],
			'at_filesize' => $att['file_size'],
			'at_mediatype' => $att['media_type'],
			'at_attachment' => $att['save_path'],
			'at_remote' => 0,
			'at_description' => '',
			'at_isimage' => $att['is_image'] ? 1 : 0,
			'at_width' => $att['width'],
			'at_thumb' => empty($att['thumb']) ? 0 : 1
		);
		// 插入数据
		$at_id = $this->_d->insert($attachment);

		if (! $at_id) {
			E('_ERR_FILE_UP');
			return false;
		}

		$end_result = array(
			'id' => (int)$at_id, // 附件id
			'url' => attachment_url($at_id, 0), // 原图
			'mediatype' => $attachment['at_mediatype'], // 媒体类型
			'big' => $params['bigsize'] >= 0 ? attachment_url($at_id, $params['bigsize']) : '', // 大图
			'thumb' => $params['thumbsize'] >= 0 ? attachment_url($at_id, $params['thumbsize']) : '', // 缩略图
			'filename' => rhtmlspecialchars($attachment['at_filename']), // 原始文件名
			'filesize' => $attachment['at_filesize'], // 文件大小
			'isimage' => $attachment['at_isimage'] // 是否是图片文件
		);

		return $end_result;
	}

	/**
	 * 尝试自mime-content-type获取文件扩展名
	 * @param string $mime_type
	 * @return string
	 */
	private function __get_ext($mime_type) {

		if (!$mime_type) {
			return '';
		}

		$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = array_search($mime_type, $mime_types);
		if ($ext === false) {
			return '';
		}

		return $ext;
	}

}
