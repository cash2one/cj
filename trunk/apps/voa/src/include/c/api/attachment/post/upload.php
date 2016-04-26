<?php
/**
 * voa_c_api_attachment_post_upload
 * 附件上传，返回附件id数组
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_attachment_post_upload extends voa_c_api_attachment_base {

	protected function _before_action($action) {

		if ('demo.vchangyi.com' == $_SERVER['HTTP_HOST']) {
			// 用于demo服务器上临时解决前端js请求跨域问题的
			if (isset($_SERVER['HTTP_REFERER'])) {
				$url_parse = @parse_url($_SERVER['HTTP_REFERER']);
				$port = '';
				if (isset($url_parse['port']) && $url_parse['port'] != 80) {
					$port = ':'.$url_parse['port'];
				}

				@header("Access-Control-Allow-Origin: ".$url_parse['scheme'].'://'.$url_parse['host'].$port);
			}

			@header("Access-Control-Allow-Credentials: true");
			@header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
			//@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
			$this->_require_login = false;
		}

		return parent::_before_action($action);
	}

	public function execute() {
		// 请求的参数
		$fields = array(
			// 上传文件的数据
			'data' => array('type' => 'string', 'required' => false),
			// 上传文件的原始名称
			'filename' => array('type' => 'string_trim', 'required' => false),
			// 上传类型，可选值：upload、base64、base64compact，stream, 默认：upload
			'type' => array('type' => 'string', 'required' => false),
			// 返回大图的宽度，如果设置为0则不返回
			'bigsize' => array('type' => 'int', 'required' => false),
			// 返回小图的宽度，如果设置为0则不返回
			'thumbsize' => array('type' => 'int', 'required' => false),
			// 是否为附件{0:不是附件,1:是附件}
			'is_attach ' => array('type' => 'int', 'required' => false)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 可选的上传类型
		$upload_types = array('upload', 'base64', 'base64compact', 'stream');
		if (!$this->_params['type']) {
			$this->_params['type'] = 'upload';
		}
		if (!in_array($this->_params['type'], $upload_types)) {
			return $this->_set_errcode(voa_errcode_api_attachment::UPLOAD_TYPE_UNDEFINED);
		}

		// 文件名称
		if (empty($this->_params['filename'])) {
			$this->_params['filename'] = random(16);
		}

		// 大图尺寸
		if ($this->_params['bigsize'] <= 0) {
			$this->_params['bigsize'] = 0;
		}
		// 小图尺寸
		if ($this->_params['thumbsize'] <= 0) {
			$this->_params['thumbsize'] = 0;
		}

		// 定义上传文件的表单名
		$file_field = 'data';

		if ($this->_params['type'] == 'base64compact') {
			// 如果是 android 的非 wifi 下的压缩模式，base64

			// 上传类型
			$upload_type = 'base64';
			// 构造上传类需要的数据：图片的新名称
			$_POST['fileName'] = $this->_params['filename'];
			// 构造上传类需要的数据：图片数据流
			$_POST[$file_field] = substr($this->_params['data'], strpos($this->_params['data'], ",") + 1);

		} elseif ($this->_params['type'] == 'base64') {
			// 标准的 base64 上传

			// 上传类型
			$upload_type = 'base64';
			// 构造上传类需要的数据：图片新名称
			$_POST['fileName'] = $this->_params['filename'];
			// 构造上传类需要的数据：图片数据流
			$_POST[$file_field] = $this->_params['data'];

		} elseif ($this->_params['type'] == 'stream') {
			// 文件流上传

			// 上传类型
			$upload_type = 'base64';
			// 构造上传类需要的数据：图片新名称
			$_POST['fileName'] = $this->_params['filename'];
			// 构造上传类需要的数据：图片数据流
			$_POST[$file_field] = base64_encode($this->_params['data']);

		} else {
			// 普通上传

			// 上传类型
			$upload_type = 'upload';
			if (empty($_FILES['data'])) {
				return $this->_set_errcode(voa_errcode_api_attachment::UPLOAD_DATA_EMPTY);
			}
			if (empty($_FILES['data']['tmp_name'])) {
				return $this->_set_errcode(voa_errcode_api_attachment::UPLOAD_DATA_NULL);
			}
			$_POST['fileName'] = $_FILES['data']['name'];
		}

		// 处理上传并写入附件
		$uda = &uda::factory('voa_uda_frontend_attachment_insert');
		$attachment = array();
		if (!$uda->upload($attachment, $file_field, $upload_type)) {
			return $this->_set_errcode(voa_errcode_api_attachment::UPLOAD_UDA_ERROR, $uda->error);
		}

		// 返回结果
		$this->_result = array(
			'id' => (int)$attachment['at_id'],// 附件id
			'url' => voa_h_attach::attachment_url($attachment['at_id'], 0),// 原图
			'big' => $this->_params['bigsize'] > 0 ? voa_h_attach::attachment_url($attachment['at_id'], $this->_params['bigsize']) : '', // 大图
			'thumb' => $this->_params['thumbsize'] > 0 ? voa_h_attach::attachment_url($attachment['at_id'], $this->_params['thumbsize']) : '',// 缩略图
			'filename' => rhtmlspecialchars($attachment['at_filename']),// 原始文件名
			'filesize' => $attachment['at_filesize'],// 文件大小
			'isimage' => $attachment['at_isimage'],// 是否是图片文件
		);
		return true;
	}

}
