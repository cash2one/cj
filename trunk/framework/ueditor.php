<?php
/**
 * ueditor.php
 * 编辑器的基类（使用UEditor）
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class ueditor {

	/**
	 * 编辑器构造时的错误信息
	 * @var string
	 */
	public $ueditor_error = '';

	/**
	 * 上传文件时的错误信息
	 * @var string
	 */
	public $upload_error = '';

	/**
	 * 编辑器配置：编辑器资源文件（即dialog等文件夹）的路径
	 * @var string
	 */
	public $ueditor_home_url = '';

	/**
	 * 编辑器配置：服务器统一请求的接口路径，用于处理类似上传等操作
	 * @var string
	 */
	public $server_url = '';

	/**
	 * 编辑器配置：编辑器配置，常用默认的见 init_ueditor_config() 方法
	 * 更多参见：http://fex.baidu.com/ueditor/#start-config
	 * @
	 * @var array
	 */
	public $ueditor_config = array();

	/**
	 * 编辑器配置：定义编辑器实例名，一个页面只使用一个编辑器时，可忽略此设置
	 * @var string
	 */
	public $ueditor_object_name = 'ue';

	/**
	 * 编辑器构造的结果html
	 * @var string
	 */
	public $ueditor_html = '';

	/**
	 * 上传：上传配置，具体设置见 _upload_default_config() 方法<br />
	 * 默认的设置在 _upload_default_config() 方法内配置<br />
	 * 默认的设置 参照 http://fex.baidu.com/ueditor/#server-server_config<br />
	 * _upload_default_config() 方法定义的是全局的，可通过 $this->upload_config 成员来进行覆盖
	 * @var array
	 */
	public $upload_config = array();

	/**
	 * 上传：上传文件的储存根目录绝对路径，一般指向到APP_PATH.'./data/attachments/'<br />
	 * 项目中可使用：项目名_h_func::get_attachdir(startup_env::get('domain')) 来获取
	 * @var string
	 */
	public $upload_save_dir_root = '';

	/**
	 * 上传结果
	 * @var array
	 */
	public $upload_result = array();

	// 编辑器js文件是否已加载完毕
	private $_js_loaded = false;

	/**
	 * 构造编辑器，返回编辑器的html代码<br />
	 * 需要预先定义：<br />
	 * ueditor->ueditor_home_url = '';<br />
	 * ueditor->server_url = '';<br />
	 * ueditor->ueditor_config = '';<br />
	 * ueditor->ueditor_object_name = '';// 可选<br />
	 * 结果：ueditor->ueditor_html
	 * @param string $ueditor_container_id 编辑器容器id
	 * @param string $ueditor_default_content 编辑器默认显示内容
	 * @param array $plugins 插件数组
	 * @return boolean
	 */
	public function create_editor($ueditor_container_id = 'content', $ueditor_default_content = '', $plugins = array()) {

		// 载入编辑器配置
		if (!$this->_init_ueditor_config()) {
			return false;
		}

		// 编辑器输出的html
		$ueditor_html = '';

		// 如果编辑器js未加载则载入
		if (!$this->_js_loaded) {

			// 标记js已载入
			$this->_js_loaded = true;

			$ueditor_html .= <<<EOF
<script type="text/javascript" src="{$this->ueditor_home_url}ueditor.config.js"></script>
<script type="text/javascript" src="{$this->ueditor_home_url}ueditor.all.min.js"></script>
<script type="text/javascript" charset="utf-8" src="{$this->ueditor_home_url}lang/zh-cn/zh-cn.js"></script>
EOF;
		}

		// 插件信息
		foreach ($plugins as $_p) {
			$ueditor_html .= <<<EOF
<script type="text/javascript" src="{$this->ueditor_home_url}{$_p}.js"></script>
EOF;
		}

		$ueditor_config = rjson_encode($this->ueditor_config);

		$ueditor_html .= <<<EOF
<script id="{$ueditor_container_id}" type="text/plain"></script>
<script type="text/javascript">
var {$this->ueditor_object_name} = UE.getEditor('{$ueditor_container_id}', {$ueditor_config});
</script>
EOF;

		$this->ueditor_html = $ueditor_html;

		return true;
	}

	/**
	 * 编辑器文件上传<br />
	 * 需预先定义：<br />
	 * upload->upload_config = array()<br />
	 * upload->upload_save_dir_root = ''<br />
	 * 结果：upload->upload_result = array()
	 * @param string|array $field_name
	 * @param string $upload_action_type
	 * @return boolean
	 */
	public function uploader($field_name, $upload_action_type) {

		// 上传错误信息
		$this->upload_error = '';
		// 上传结果
		$this->upload_result = array('state' => '上传文件发生未知错误');

		if (!$this->_init_upload_config()) {
			/* 配置错误 */
			return false;
		}

		if (!$field_name) {
			$this->upload_error = '未定义上传表单控件名';
			return false;
		}

		// 上传类upload需要的配置信息
		$current_config = array(
				'save_dir_path' => $this->upload_save_dir_root,
		);

		$upload_action_type = rstrtolower($upload_action_type);

		if ($upload_action_type == 'catchimage') {
			/* 远程抓取图片 */

			// 抓取图片的配置信息
			$current_config['allow_files'] = !empty($this->upload_config['catcherAllowFiles']) ? $this->upload_config['catcherAllowFiles'] : array('png', 'jpg', 'jpeg', 'gif', 'bmp');
			$current_config['file_name_format'] = !empty($this->upload_config['catcherPathFormat']) ? $this->upload_config['catcherPathFormat'] : 'auto';
			$current_config['max_size'] = !empty($this->upload_config['catcherMaxSize']) ? $this->upload_config['catcherMaxSize'] : 0;
			$current_config['source_name'] = 'remote.png';


			// 获取到待抓取的图片url列表
			$request = controller_request::get_instance();
			$source_list = $request->get($field_name);

			// 抓取结果信息列表
			$result_list = array();
			foreach ($source_list as $_img_url) {
				$item = new upload($_img_url, $current_config, 'remote');
				$result_list[] = $item->get_file_info();
			}

			$this->upload_result = $result_list;

			if (empty($result_list)) {
				$this->upload_error = '无已抓取的图片数据';
				return false;
			} else {
				return true;
			}
		} elseif ($upload_action_type == 'uploadscrawl') {
			/** 上传涂鸦 */

			$current_config['allow_files'] = !empty($this->upload_config['scrawlAllowFiles']) ? $this->upload_config['scrawlAllowFiles'] : array('png', 'jpg', 'jpeg', 'gif', 'bmp');
			$current_config['file_name_format'] = !empty($this->upload_config['scrawlPathFormat']) ? $this->upload_config['scrawlPathFormat'] : 'auto';
			$current_config['max_size'] = !empty($this->upload_config['scrawlMaxSize']) ? $this->upload_config['scrawlMaxSize'] : 0;
			$current_config['source_name'] = 'scrawl.png';

			$up = new upload($field_name, $current_config, 'base64');
			$this->upload_result = $up->get_file_info();
		} elseif (in_array($upload_action_type, array('uploadimage', 'uploadvideo', 'uploadfile'))) {
			/** 上传 图片、视频、文件 */

			$key_prefix = '';
			switch ($upload_action_type) {
				case 'uploadimage':// 上传图片
					$key_prefix = 'image';
				break;
				case 'uploadvideo':// 上传视频
					$key_prefix = 'video';
				break;
				case 'uploadfile':// 上传文件
					$key_prefix = 'file';
				break;
			}

			$current_config['allow_files'] = !empty($this->upload_config[$key_prefix.'AllowFiles']) ? $this->upload_config[$key_prefix.'AllowFiles'] : array('png', 'jpg', 'jpeg', 'gif', 'bmp');
			$current_config['file_name_format'] = !empty($this->upload_config[$key_prefix.'PathFormat']) ? $this->upload_config[$key_prefix.'PathFormat'] : 'auto';
			$current_config['max_size'] = !empty($this->upload_config[$key_prefix.'MaxSize']) ? $this->upload_config[$key_prefix.'MaxSize'] : 0;

			$up = new upload($field_name, $current_config, 'upload');
			$this->upload_result = $up->get_file_info();
		} else {
			/** 未定义的动作 */

			$this->upload_error = '未知的上传动作';
			return false;
		}

		// 此处判断只针对非远程抓取图片动作有效（因为远程抓取图片会直接输出结果）
		if (!empty($this->upload_result['error']) && $this->upload_result['error'] == 'SUCCESS') {
			return true;
		} else {
			$this->upload_error = $this->upload_result['error'];
			return false;
		}
	}

	/**
	 * 输出json格式的上传配置参数给前台<br />
	 * 使用前必须定义：<br />
	 * upload->upload_config = array();<br />
	 * upload->upload_save_dir_root = '';<br />
	 * @param string &$output
	 * @return boolean
	 */
	public function get_upload_config(&$output) {
		// 配置错误
		if (!$this->_init_upload_config()) {
			return false;
		}

		$output = rjson_encode($this->upload_config);

		return true;
	}

	/**
	 * 转换内容里的附件路径为bbcode代码标签
	 * @param string $content <strong style="color:red">(引用结果)</strong>内容
	 * @param string $attach_url_base 附件的访问基本路径
	 * @param array $at_ids <strong style="color:red">(应用结果)</strong>附件id
	 * @return array
	 */
	public function attachment_url_to_bbcode(&$content, $attach_url_base, &$at_ids) {
		$at_ids = array();
		if (preg_match_all('/'.preg_quote($attach_url_base, '/').'(\d+)/', $content, $matchs)) {
			/*
			$attach_tags = array();
			foreach ($matchs[1] as $k => $_at_id) {
				$attach_tags[$k] = '[attach]'.$_at_id.'[/attach]';
			}
			$content = str_replace($matchs[0], $attach_tags, $content);
			*/
			$at_ids = $matchs[1];
		}
	}

	/**
	 * 格式化错误信息供前端使用
	 * @param string $msg
	 * @return null
	 */
	public function upload_error_to_ueditor($msg) {
		$this->upload_error = rjson_encode(array('state' => $msg));
		return null;
	}

	/**
	 * 格式化上传结果供前端使用
	 * @param string|json $result
	 * @return string
	 */
	public function upload_result_to_ueditor($upload_action_type, $result, $attach_view_url, $attach_id) {

		if ($upload_action_type == 'catchimage') {
			/** 远程获取图片 */
			$list = array();
			foreach ($result as $k => $r) {
				if (isset($attach_view_url[$k])) {
					$list[] = $this->_upload_result_format($result, $attach_view_url[$k], $attach_id[$k]);
				}
			}
			$result = array(
					'state'=> count($list) ? 'SUCCESS' : 'ERROR',
					'list'=> $list
			);
		} else {
			/** 其他上传动作 */
			$result = $this->_upload_result_format($result, $attach_view_url, $attach_id);
		}

		$result = rjson_encode($result);
		if (isset($_GET['callback']) && is_string($_GET['callback']) && preg_match('/^\w+$/', $_GET['callback'])) {
			$result = $_GET['callback'] . '(' . $result . ')';
		}
		return $result;
	}

	/**
	 * 格式化上传结果信息
	 * @param array|string $result
	 * @param string $attach_view_url
	 * @return array
	 */
	protected function _upload_result_format($result, $attach_view_url, $attach_id) {
		if (is_string($result) || isset($result['error'])) {
			if (is_string($result)) {
				$result = array('error' => $result);
			}
			$url = $attach_view_url;
			if (isset($result['url'])) {
				$url = $result['url'];
			}
			return array(
					'state' => $result['error'],//上传状态，上传成功时必须返回'SUCCESS'
					'url' => $url,//返回的地址
					'title' => isset($result['file_name']) ? $result['file_name'] : '',//新文件名
					'original' => isset($result['source_name']) ? $result['source_name'] : '',//原始文件名
					'type' => isset($result['file_type']) ? $result['file_type'] : '',//文件类型
					'size' => isset($result['file_size']) ? $result['file_size'] : 0,//文件大小
					'attach_id' => $attach_id
			);
		} else {

			foreach ($result as &$data) {
				$data = $this->_upload_result_format($data, $attach_view_url, $attach_id);
			}
			return $result;
		}
	}

	/**
	 * 编辑器配置参数整理
	 * @return boolean
	 */
	private function _init_ueditor_config() {

		if (!$this->server_url) {
			$this->ueditor_error = '服务器接口地址未指定';
			return false;
		}

		if (!$this->ueditor_home_url) {
			$this->ueditor_error = '编辑器资源文件路径未指定';
			return false;
		}

		// 定义常用的默认配置
		$defaults = array(
				'serverUrl' => $this->server_url,// 服务端接口
				'toolbars' => '_normal',// 工具栏上的所有的功能按钮和下拉框，_all、_min、_normal，使用本类自定义方式
				'charset' => 'utf-8',// 字符集
				'lang' => 'zh-cn',// 语言
				'initialContent' => '',// 编辑区的默认内容
				'autoClearinitialContent' => false,// 加载后是否清除默认内容
				'emotionLocalization' => true,// 是否表情本地化
				'pageBreakTag' => 'ueditor_page_break_tag',// 分页标识符
				'initialFrameHeight' => '300',// 初始化编辑器高度
				'textarea' => 'contents',// 内容表单控件名
				'elementPathEnabled' => false
		);

		// 载入默认的配置
		foreach ($defaults as $var => $val) {
			if (!isset($this->ueditor_config[$var])) {
				$this->ueditor_config[$var] = $val;
			}
		}

		// 设置工具栏
		if (is_scalar($this->ueditor_config['toolbars'])) {
			switch (rstrtolower($this->ueditor_config['toolbars'])) {
				case '_all':
					$this->ueditor_config['toolbars'] = array(
					'fullscreen', 'source', '|', 'undo', 'redo', '|',
					'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
					'rowspacingtop', 'rowspacingbottom', 'lineheight', '|','customstyle', 'paragraph', 'fontfamily', 'fontsize', '|', 'directionalityltr', 'directionalityrtl', 'indent', '|',
					'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
					'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
					'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
					'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
					'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
					'print', 'preview', 'searchreplace', 'help', 'drafts'
					);
					break;
				case '_min':
					$this->ueditor_config['toolbars'] = array(
					'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
					'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
					);
					break;
				case '_normal':
					$this->ueditor_config['toolbars'] = array(
					'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
					'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
					);
					break;
				case '_mobile':
					$this->ueditor_config['toolbars'] = array(
					 'source', '|', 'bold', 'italic', 'underline', 'removeformat', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'fontfamily', 'fontsize',
					 '|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify',  '|', 'link', 'unlink', 'insertimage', 'insertvideo',
					);
					break;
				case '_cyadmin':
					$this->ueditor_config['toolbars'] = array(
						'source', '|', 'bold', 'italic', 'underline', 'removeformat', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'fontfamily', 'fontsize',
						'|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify',  '|', 'link', 'unlink', 'insertimage', 'insertvideo',
						);
				break;
				case '_map':
					$this->ueditor_config['toolbars'] = array(
						'source','map'
					);
				break;
				case '_jobtrain':
					$this->ueditor_config['toolbars'] = array(
						'source', '|', 'bold', 'italic', 'underline', 'removeformat', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'fontfamily', 'fontsize',
						'|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify',  '|', 'link', 'unlink', 'insertimage', 'fullscreen', 'inserttable', 'deletetabe',
					);
					break;
			}
		}
		$this->ueditor_config['toolbars'] = array($this->ueditor_config['toolbars']);

		return true;
	}

	/**
	 * 上传配置参数整理
	 * @return boolean
	 */
	private function _init_upload_config() {

		if (!$this->upload_save_dir_root) {
			$this->upload_error = '储存根目录未配置';
			return false;
		}
		$this->upload_save_dir_root = $this->_format_path($this->upload_save_dir_root);
		if (!$this->upload_save_dir_root || !is_dir($this->upload_save_dir_root) || !is_writable($this->upload_save_dir_root)) {
			$this->upload_error = '储存根目录不存在 或 无法写入';
			return false;
		}

		// 默认的上传配置信息
		$config = $this->_upload_default_config();
		// 自定义的上传配置
		if (!empty($this->upload_config)) {
			foreach ($this->upload_config as $key => $value) {
				$config[$key] = $value;
			}
		}

		// 当前环境的上传配置
		$this->upload_config = $config;

		return true;
	}

	/**
	 * 格式化路径分隔符号为系统符号
	 * @param string $path
	 * @return string
	 */
	private function _format_path($path) {
		return str_replace('.'.DIRECTORY_SEPARATOR, '', preg_replace(array('/\/+/','/\\\+/'), DIRECTORY_SEPARATOR, $path));
	}

	/**
	 * 上传的全局默认配置
	 */
	private function _upload_default_config() {
		$config = array(
				/* 上传图片配置 */
				'imageActionName' => 'uploadimage',// 不要更改！
				'imageFieldName' => 'upfile',// 不要更改！
				'imageMaxSize' => 2048000,
				'imageAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp',),
				'imageCompressEnable' => true,
				'imageCompressBorder' => 1600,
				'imageInsertAlign' => 'none',
				'imageUrlPrefix' => '',
				'imagePathFormat' => '{yyyy}/{mm}/{dd}{hh}{ii}{ss}{rand:8}',

				/* 涂鸦图片上传配置 */
				'scrawlActionName' => 'uploadscrawl',// 不要更改！
				'scrawlFieldName' => 'upfile',// 不要更改！
				'scrawlPathFormat' => '{yyyy}/{mm}/{dd}{hh}{ii}{ss}{rand:8}',
				'scrawlMaxSize' => 2048000,
				'scrawlUrlPrefix' => '',
				'scrawlInsertAlign' => 'none',

				/* 截图工具上传配置 */
				'snapscreenActionName' => 'uploadimage',// 不要更改！
				'snapscreenPathFormat' => '{yyyy}/{mm}/{dd}{hh}{ii}{ss}{rand:8}',
				'snapscreenUrlPrefix' => '',
				'snapscreenInsertAlign' => 'none',

				/* 抓取远程图片配置 */
				'catcherLocalDomain' => array('127.0.0.1', 'localhost', 'img.baidu.com',),
				'catcherActionName' => 'catchimage',// 不要更改！
				'catcherFieldName' => 'source',
				'catcherPathFormat' => '{yyyy}/{mm}/{dd}{hh}{ii}{ss}{rand:8}',
				'catcherUrlPrefix' => '',
				'catcherMaxSize' => 2048000,
				'catcherAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp',),

				/* 上传视频配置 */
				'videoActionName' => 'uploadvideo',// 不要更改！
				'videoFieldName' => 'upfile',// 不要更改！
				'videoPathFormat' => '{yyyy}/{mm}/{dd}{hh}{ii}{ss}{rand:8}',
				'videoUrlPrefix' => '',
				'videoMaxSize' => 102400000,
				'videoAllowFiles' => array(
						'.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg', '.ogg',
						'.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid',
				),

				/* 上传文件配置 */
				'fileActionName' => 'uploadfile',// 不要更改！
				'fileFieldName' => 'upfile',// 不要更改！
				'filePathFormat' => '{yyyy}/{mm}/{dd}{hh}{ii}{ss}{rand:8}',
				'fileUrlPrefix' => '',
				'fileMaxSize' => 51200000,
				'fileAllowFiles' => array(
						'.png', '.jpg', '.jpeg', '.gif', '.bmp', '.flv', '.swf', '.mkv', '.avi',
						'.rm', '.rmvb', '.mpeg', '.mpg', '.ogg', '.ogv', '.mov', '.wmv', '.mp4',
						'.webm', '.mp3', '.wav', '.mid', '.rar', '.zip', '.tar', '.gz', '.7z',
						'.bz2', '.cab', '.iso', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx',
						'.pdf', '.txt', '.md', '.xml',
				),

				/* 列出指定目录下图片 */
				'imageManagerActionName' => 'listimage',// 不要更改！
				'imageManagerListPath' => '/ueditor/php/upload/image/',
				'imageManagerListSize' => 20,
				'imageManagerUrlPrefix' => '',
				'imageManagerInsertAlign' => 'none',
				'imageManagerAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp',),

				/* 列出指定目录下的文件配置 */
				'fileManagerActionName' => 'listfile',// 不要更改！
				'fileManagerListPath' => '/ueditor/php/upload/file/',
				'fileManagerUrlPrefix' => '',
				'fileManagerListSize' => 20,
				'fileManagerAllowFiles' => array(
						'.png', '.jpg', '.jpeg', '.gif', '.bmp', '.flv', '.swf', '.mkv', '.avi',
						'.rm', '.rmvb', '.mpeg', '.mpg', '.ogg', '.ogv', '.mov', '.wmv', '.mp4',
						'.webm', '.mp3', '.wav', '.mid', '.rar', '.zip', '.tar', '.gz', '.7z',
						'.bz2', '.cab', '.iso', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx',
						'.pdf', '.txt', '.md', '.xml',
				),
		);

		return $config;
	}


	/**
	 * ueditor 上传的默认配置<br />
	 * 来自原包/ueditor/php/config.json 不建议修改<br />
	 * http://fex.baidu.com/ueditor/#server-server_config
	 * @return array
	 */
	private function _ueditor_default_upload_config() {
		$data = <<<EOF
/* 前后端通信相关的配置,注释只允许使用多行方式 */
{
    /* 上传图片配置项 */
    "imageActionName": "uploadimage", /* 执行上传图片的action名称 */
    "imageFieldName": "upfile", /* 提交的图片表单名称 */
    "imageMaxSize": 2048000, /* 上传大小限制，单位B */
    "imageAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
    "imageCompressEnable": true, /* 是否压缩图片,默认是true */
    "imageCompressBorder": 1600, /* 图片压缩最长边限制 */
    "imageInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageUrlPrefix": "", /* 图片访问路径前缀 */
    "imagePathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                                /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
                                /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
                                /* {time} 会替换成时间戳 */
                                /* {yyyy} 会替换成四位年份 */
                                /* {yy} 会替换成两位年份 */
                                /* {mm} 会替换成两位月份 */
                                /* {dd} 会替换成两位日期 */
                                /* {hh} 会替换成两位小时 */
                                /* {ii} 会替换成两位分钟 */
                                /* {ss} 会替换成两位秒 */
                                /* 非法字符 \ : * ? " < > | */
                                /* 具请体看线上文档: fex.baidu.com/ueditor/#use-format_upload_filename */

    /* 涂鸦图片上传配置项 */
    "scrawlActionName": "uploadscrawl", /* 执行上传涂鸦的action名称 */
    "scrawlFieldName": "upfile", /* 提交的图片表单名称 */
    "scrawlPathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
    "scrawlMaxSize": 2048000, /* 上传大小限制，单位B */
    "scrawlUrlPrefix": "", /* 图片访问路径前缀 */
    "scrawlInsertAlign": "none",

    /* 截图工具上传 */
    "snapscreenActionName": "uploadimage", /* 执行上传截图的action名称 */
    "snapscreenPathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
    "snapscreenUrlPrefix": "", /* 图片访问路径前缀 */
    "snapscreenInsertAlign": "none", /* 插入的图片浮动方式 */

    /* 抓取远程图片配置 */
    "catcherLocalDomain": ["127.0.0.1", "localhost", "img.baidu.com"],
    "catcherActionName": "catchimage", /* 执行抓取远程图片的action名称 */
    "catcherFieldName": "source", /* 提交的图片列表表单名称 */
    "catcherPathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
    "catcherUrlPrefix": "", /* 图片访问路径前缀 */
    "catcherMaxSize": 2048000, /* 上传大小限制，单位B */
    "catcherAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */

    /* 上传视频配置 */
    "videoActionName": "uploadvideo", /* 执行上传视频的action名称 */
    "videoFieldName": "upfile", /* 提交的视频表单名称 */
    "videoPathFormat": "/ueditor/php/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
    "videoUrlPrefix": "", /* 视频访问路径前缀 */
    "videoMaxSize": 102400000, /* 上传大小限制，单位B，默认100MB */
    "videoAllowFiles": [
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */

    /* 上传文件配置 */
    "fileActionName": "uploadfile", /* controller里,执行上传视频的action名称 */
    "fileFieldName": "upfile", /* 提交的文件表单名称 */
    "filePathFormat": "/ueditor/php/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
    "fileUrlPrefix": "", /* 文件访问路径前缀 */
    "fileMaxSize": 51200000, /* 上传大小限制，单位B，默认50MB */
    "fileAllowFiles": [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ], /* 上传文件格式显示 */

    /* 列出指定目录下的图片 */
    "imageManagerActionName": "listimage", /* 执行图片管理的action名称 */
    "imageManagerListPath": "/ueditor/php/upload/image/", /* 指定要列出图片的目录 */
    "imageManagerListSize": 20, /* 每次列出文件数量 */
    "imageManagerUrlPrefix": "", /* 图片访问路径前缀 */
    "imageManagerInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageManagerAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */

    /* 列出指定目录下的文件 */
    "fileManagerActionName": "listfile", /* 执行文件管理的action名称 */
    "fileManagerListPath": "/ueditor/php/upload/file/", /* 指定要列出文件的目录 */
    "fileManagerUrlPrefix": "", /* 文件访问路径前缀 */
    "fileManagerListSize": 20, /* 每次列出文件数量 */
    "fileManagerAllowFiles": [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ] /* 列出的文件类型 */

}
EOF;
		$data = json_decode(preg_replace('/\/\*[\s\S]+?\*\//', '', trim($data)), true);

		return $data;
	}

}
