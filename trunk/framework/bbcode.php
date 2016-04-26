<?php
/**
 * 模板解析函数
 * $Author$
 * $Id$
 */

class bbcode {
	protected static $s_instance = NULL;
	protected $_global = array();
	protected $_smilie = array();
	protected $_bbcode = array();
	/** 代码块标识前缀 */
	protected $_wbs_code_block = 'wbs_bbcode';
	public $code = '';
	public $html = '';

	public static function instance() {
		if (is_null(self::$s_instance)) {
			self::$s_instance = new self();
		}

		return self::$s_instance;
	}

	/** 构造函数 */
	public function __construct() {
		$this->_global = array(
			'p_code_count' => -1,
			'code_count' => 0,
			'code_html' => array(),
			'search_array' => array(),
			'replace_array' => array()
		);

		$app_name = startup_env::get('app_name');
		$this->_wbs_code_block = md5(startup_env::get('timestamp').config::get($app_name.'.auth_key'));
	}

	/**
	 * 初始化自定义 bbcode
	 * @param array $smilie 表情代码
	 * @param array $bbcode bbcode代码
	 */
	public function init_bbcode($smilie = array(), $bbcode = array()) {
		$this->_smilie = (array)$smilie;
		$this->_bbcode = (array)$bbcode;
		/** 避免smilie['replace_array']未定义或者不是数组 */
		if ( !isset($this->_smilie['replace_array']) || !is_array($this->_smilie['replace_array']) ) {
			$this->_smilie['replace_array']	=	array();
		}

		foreach ($this->_smilie['replace_array'] as $key => $smiley) {
			$this->_smilie['replace_array'][$key] = '<img src="/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
		}
	}

	/**
	 * 解析表格;
	 * @param int $width	表格宽;
	 * @param string $message	字串;
	 * @return unknown;
	 */
	public  function _parse_table($width, $message) {
		$width = substr($width, -1) == '%' ? (substr($width, 0, -1) <= 98 ? $width : '98%') : ($width <= 560 ? $width : '98%');
		return '<table '.($width == '' ? NULL : 'width="'.$width.'" ').'align="center" class="t_table">'.str_replace(
			array('[tr]', '[td]', '[/td]', '[/tr]', '\\"'),
			array('<tr>', '<td>', '</td>', '</tr>', '"'),
			rpreg_replace("/\[td=(\d{1,2}),(\d{1,2})(,(\d{1,3}%?))?\]/is", '<td colspan="\\1" rowspan="\\2" width="\\4">', $message)
		).'</table>';
	}

	/**
	 * 整理代码块;
	 * @param string $code	代码块字串;
	 */
	public function _codedisp($code) {
		$this->_global['p_code_count'] ++;
		$code = rhtmlspecialchars(str_replace('\\"', '"', rpreg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", "\\1", $code)));
		$this->_global['code_html'][$this->_global['p_code_count']] = "<br><br><div class=\"msgHeader\"><div class=\"right\"><a href=\"###\" class=\"smallTxt\" onclick=\"copyCode($('code".$this->_global['code_count']."'));\">[Copy to clipboard]</a></div>CODE:</div><div class=\"msgBorder\" id=\"code".$this->_global['code_count']."\">$code</div><br>";
		$this->_global['code_count'] ++;
		return "[\t{$this->_wbs_code_block}_".$this->_global[p_code_count]."\t]";
	}

	public function _htmldisp($code) {
		$this->_global['p_code_count'] ++;
		$this->_global['code_html'][$this->_global['p_code_count']] = "<br><br><div class=\"msgBorder\" id=\"code".$this->_global['code_count']."\">$code</div><br>";
		$this->_global['code_count'] ++;
		return "[\t{$this->_wbs_code_block}_".$this->_global[p_code_count]."\t]";
	}

	/**
	 * BBCODE 到 HTML 的转换;
	 * @param string $message	字串;
	 */
	public function bbcode2html($message) {
		$message = rpreg_replace("/\s*\[code\](.+?)\[\/code\]\s*/is", array('callback' => array($this, '_codedisp'), 'params' => array(1)), $message);
		$message = rpreg_replace("/\s*\[html\](.*?)\[\/html\]\s*/is", array('callback' => array($this, '_htmldisp'), 'params' => array(1)), $message);

		/** 判断smilie的两个键名是否存在 */
		if ($this->_smilie && !empty($this->_smilie['search_array']) && !empty($this->_smilie['replace_array'])) {
			$message = rpreg_replace($this->_smilie['search_array'], $this->_smilie['replace_array'], $message);
		}

		/** 过滤 js 代码 */
		$message = rpreg_replace("/<script[^>]*>(.*?)<\/script>/i", "", $message);
		if (empty($this->_global['search_array'])) {
			$this->_global['search_array']['bbcode_regexp'] = array(
				"/\[url\]\s*(www.|https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/i",
				"/\[url=www.([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[email\]\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*\[\/email\]/i",
				"/\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[\/email\]/is",
				"/\[color=([^\[\<]+?)\]/i",
				"/\[size=(\d+?)\]/i",
				"/\[size=(\d+(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
				"/\[font=([^\[\<]+?)\]/i",
				"/\[align=([^\[\<]+?)\]/i"
			);
			$this->_global['replace_array']['bbcode_regexp'] = array(
				"\$this->_cut_url('\\1\\2')",
				"<a href=\"http://www.\\1\" target=\"_blank\">\\2</a>",
				"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>",
				"<a href=\"mailto:\\1@\\2\">\\1@\\2</a>",
				"<a href=\"mailto:\\1@\\2\">\\3</a>",
				"<font color=\"\\1\">",
				"<font size=\"\\1\">",
				"<font style=\"font-size: \\1\">",
				"<font face=\"\\1\">",
				"<p align=\"\\1\">"
			);

			$this->_global['search_array']['bbcode_regexp'][] = "/\s*\[table(=(\d{1,3}%?))?\][\n\r]*(.+?)[\n\r]*\[\/table\]\s*/is";
			$this->_global['replace_array']['bbcode_regexp'][] = array('callback' => array($this, '_parse_table'), 'params' => array(1));
			$this->_global['search_array']['bbcode_regexp'][] = "/\s*\[table(=(\d{1,3}%?))?\][\n\r]*(.+?)[\n\r]*\[\/table\]\s*/is";
			$this->_global['replace_array']['bbcode_regexp'][] = array('callback' => array($this, '_parse_table'), 'params' => array(2, 3));

			$this->_global['search_array']['bbcode_regexp'][] = "/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is";
			$this->_global['search_array']['bbcode_regexp'][] = "/\s*\[php\][\n\r]*(.+?)[\n\r]*\[\/php\]\s*/is";
			$this->_global['search_array']['bbcode_regexp'][] = "/\s*\[code\][\n\r]*(.+?)[\n\r]*\[\/code\]\s*/is";
			$this->_global['replace_array']['bbcode_regexp'][] = "<br><br><div class=\"msgHeader\">QUOTE:</div><div class=\"msgBorder\">\\1</div><br>";
			$this->_global['replace_array']['bbcode_regexp'][] = "<br><br><div class=\"msgHeader\">PHP:</div><div class=\"msgBorder\">\\1</div><br>";
			$this->_global['replace_array']['bbcode_regexp'][] = "<br><br><div class=\"msgHeader\">CODE:</div><div class=\"msgBorder\">\\1</div><br>";

			$this->_global['search_array']['bbcode_regexp'] = array_merge($this->_global['search_array']['bbcode_regexp'], $this->_global['search_array']['bbcode_regexp']);
			$this->_global['replace_array']['bbcode_regexp'] = array_merge($this->_global['replace_array']['bbcode_regexp'], $this->_global['replace_array']['bbcode_regexp']);

			$this->_global['search_array']['bbcodeStr'] = array(
				'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]',
				'[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
				'[list=A]', '[*]', '[/list]', '[indent]', '[/indent]'
			);

			$this->_global['replace_array']['bbcodeStr'] = array(
				'</font>', '</font>', '</font>', '</p>', '<b>', '</b>', '<i>',
				'</i>', '<u>', '</u>', '<ul>', '<ol type=1>', '<ol type=a>',
				'<ol type=A>', '<li>', '</ul></ol>', '<blockquote>', '</blockquote>'
			);
		}

		@$message = str_replace(
			$this->_global['search_array']['bbcodeStr'], $this->_global['replace_array']['bbcodeStr'],
			rpreg_replace(
				($this->_bbcode ? array_merge($this->_global['search_array']['bbcode_regexp'], $this->_bbcode['search_array']) : $this->_global['search_array']['bbcode_regexp']),
				($this->_bbcode ? array_merge($this->_global['replace_array']['bbcode_regexp'], $this->_bbcode['replace_array']) : $this->_global['replace_array']['bbcode_regexp']),
				$message
			)
		);

		if (empty($this->_global['search_array']['imgCode'])) {
			$this->_global['search_array']['imgCode'] = array(
				"/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/is",
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is",
				"/\[img=(\d{1,3})[x|\,](\d{1,3})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is"
			);

			$this->_global['replace_array']['imgCode'] = array(
					array('callback' => array($this, '_bbcode_url'), 'params' => array(1, ' <img src=\"images/attachicons/flash.gif\" align=\"absmiddle\" alt=\"\" /> <a href=\"%s\" target=\"_blank\">Flash: %s</a> ')),
				    array('callback' => array($this, '_bbcode_url'), 'params' => array(1, ' <img src=\"%s\" border=\"0\" onload=\"javascript:checkImageWidth(this);\" onmouseover=\"javascript:checkImagePointer(this);\" onclick=\"javascript:openImageInNewWindow(this);\" onmousewheel=\"return imgzoom(this);\" alt=\"\" /> ')),
					array('callback' => array($this, '_bbcode_url'), 'params' => array(3, ' <img width=\"\\1\" height=\"\\2\" src=\"%s\" border=\"0\" alt=\"\" /> ')),
			);
		}
		$message = rpreg_replace($this->_global['search_array']['imgCode'], $this->_global['replace_array']['imgCode'], $message);

		for($i = 0; $i <= $this->_global['p_code_count']; $i++) {
			$message = str_replace("[\t{$this->_wbs_code_block}_$i\t]", rstripslashes($this->_global['code_html'][$i]), $message);
		}

		$this->html = str_replace(array("\t", '   ', '  ', "\r", "\n"), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;', '', '<br />'), $message);
		return $this->html;
	}

	/**
	 * 解析图片及 flash url；
	 * @param string $url	url 字串；
	 * @param string $tags	链接字串；
	 */
	public function _bbcode_url($url, $tags) {
		if (!preg_match("/<.+?>/s", $url)) {
			if (!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'ftp://', 'rtsp:/', 'mms://'))) {
				$url = 'http://'.$url;
			}

			return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
		} else {
			return '&nbsp;'.$url;
		}
	}

	/**
	 * 剪切掉过长的 url 字串;
	 * @param string $url	url字串;
	 * @return unknown
	 */
	public  function _cut_url($url) {
		$length = 65;
		$urllink = "<a href=\"".(substr(strtolower($url), 0, 4) == 'www.' ? "http://$url" : $url).'" target="_blank">';
		if (strlen($url) > $length) {
			$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}

		$urllink .= $url.'</a>';
		return $urllink;
	}

	/**
	 * HTML => bbcode;
	 * @param string $message 代转的字串
	 * @return unknown
	 */
	public function html2bbcode($message) {
		$message = trim($message);
		$message = rpreg_replace("/<style.*?>[\s\S]*?<\/style>/is", '', $message);
		$message = rpreg_replace("/<script.*?>[\s\S]*?<\/script>/is", '', $message);
		$message = rpreg_replace("/<noscript.*?>[\s\S]*?<\/noscript>/is", '', $message);
		$message = rpreg_replace("/<select.*?>[\s\S]*?<\/select>/is", '', $message);
		$message = rpreg_replace("/<object.*?>[\s\S]*?<\/object>/is", '', $message);
		$message = rpreg_replace("/<!--[\s\S]*?-->/is", '', $message);
		$message = rpreg_replace("/on[a-zA-Z]+\s?=\s?([\"\'])[\s\S]*?\\1/is", '', $message);
		$message = rpreg_replace("/(\r\n|\n|\r)/is", '', $message);
		$message = strip_tags($message, '<table><tr><td><b><strong><i><em><u><a><div><span><p><strike><blockquote><ol><ul><li><font><img><br><br/><h1><h2><h3><h4><h5><h6>');

		$message = rpreg_replace("/<table([^>]*width[^>]*)>/is", array('callback' => array($this, '_table_tag'), 'params' => array(1)), $message);
		$message = rpreg_replace("/<table[^>]*>/is", '[table]', $message);
		$message = rpreg_replace("/<tr[^>]*>/is", '[tr]', $message);
		$message = rpreg_replace("/<td>/is", '[td]', $message);
		$message = rpreg_replace("/<td([^>]+)>/is", array('callback' => array($this, '_td_tag'), 'params' => array(1)), $message);

		$message = rpreg_replace("/<\/td>/is", '[/td]', $message);
		$message = rpreg_replace("/<\/tr>/is", '[/tr]', $message);
		$message = rpreg_replace("/<\/table>/is", '[/table]', $message);

		$message = rpreg_replace("/<h([0-9]+)[^>]*>(.*)<\/h\\1>/is", "[size=\\1]\\2[/size]\n\n", $message);
		$message = rpreg_replace("/<img([^>]*src[^>]*)>/is", array('callback' => array($this, '_img_tag'), 'params' => array(1)), $message);

		$message = rpreg_replace("/<a\s+?name=([\"\']?)(.+?)(\\1)[\s\S]*?>([\s\S]*?)<\/a>/is", '\\4', $message);
		$message = rpreg_replace("/<br[^\>]*>/is", "\n", $message);

		$message = $this->_recursion('b', $message, '_simple_tag', 'b');
		$message = $this->_recursion('strong', $message, '_simple_tag', 'b');
		$message = $this->_recursion('i', $message, '_simple_tag', 'i');
		$message = $this->_recursion('em', $message, '_simple_tag', 'i');
		$message = $this->_recursion('u', $message, '_simple_tag', 'u');
		$message = $this->_recursion('blockquote', $message, '_simple_tag', 'indent');
		$message = $this->_recursion('a', $message, '_a_tag', '');
		$message = $this->_recursion('font', $message, '_font_tag', '');
		$message = $this->_recursion('ol', $message, '_list_tag', '');
		$message = $this->_recursion('ul', $message, '_list_tag', '');
		$message = $this->_recursion('div', $message, '_dsp_tag', '');
		$message = $this->_recursion('p', $message, '_dsp_tag', '');
		$message = $this->_recursion('span', $message, '_dsp_tag', '');

		/** 过滤不能识别的标签 */
		$message = rpreg_replace("/<[\/\!]*?[^<>]*?>/i", '', $message);
		$this->code = rhtmlspecialchars($message);
		return $this->code;
	}

	/**
	 * 解析 table 标签;
	 * @param string $attributes Table标签属性;
	 * @return unknown
	 */
	public  function _table_tag($attributes) {
		$width = '';
		$attributes = rstripslashes($attributes);
		preg_match_all("/width=([\"\']?)(\d+\%?)(\\1)/i", $attributes, $matches);
		if (!empty($matches[2][0])) {
			$width = $matches[2][0];
		} else {
			preg_match_all("/width\s?:\s?(\d+)([px|%])/i", $attributes, $matches);
			if (!empty($matches[1][0])) {
				$width = $matches[1][0];
			}
		}

		if (!empty($width)) {
			$width = '%' == substr($width, -1) ? (98 >= substr($width, 0, -1) ? $width : '98%') : (560 >= $width ? $width : '98%');
		}

		return empty($width) ? '[table]' : "[table={$width}]";
	}

	/**
	 * 解析 td 标签;
	 * @param string $attributes Td 标签的属性
	 * @return unknown
	 */
	public  function _td_tag($attributes) {
		$colspan = 1;
		$rowspan = 1;
		$width = '';
		$attributes = rstripslashes($attributes);
		preg_match_all("/colspan=([\"\']?)(\d+)(\\1)/i", $attributes, $matches);
		if (!empty($matches[2][0])) {
			$colspan = max(1, intval($matches[2][0]));
		}

		preg_match_all("/rowspan=([\"\']?)(\d+)(\\1)/i", $attributes, $matches);
		if (!empty($matches[2][0])) {
			$rowspan = max(1, intval($matches[2][0]));
		}

		preg_match_all("/width=([\"\']?)(\d+%?)(\\1)/i", $attributes, $matches);
		if (!empty($matches[2][0])) {
			$width = $matches[2][0];
		}

		return in_array($width, array('', '0', '100%')) ? ($colspan == 1 && $rowspan == 1 ? '[td]' : "[td={$colspan},{$rowspan}]") : "[td={$colspan},{$rowspan},{$width}]";
	}

	/**
	 * 解析 img 标签;
	 * @param string $attributes Img 属性
	 * @return unknown
	 */
	public  function _img_tag($attributes) {
		$width = $height = 0;
		preg_match_all("/src=([\"\']?)([\s\S]*?)(\\1)/i", $attributes, $matches);
		if (empty($matches[2][0])) {
			return '';
		}

		$src = $matches[2][0];
		preg_match_all("/width=([\"\']?)(\d+)(\\1)/i", $attributes, $matches);
		if (!empty($matches[2][0])) {
			$width = intval($matches[2][0]);
		}

		preg_match_all("/height=([\"\']?)(\d+)(\\1)/i", $attributes, $matches);
		if (!empty($matches[2][0])) {
			$height = intval($matches[2][0]);
		}

		$imgtag = 'img';
		/**bbcodeRe = /aid=([\"\']?)attach_(\d+)(\1)/i;
		var matches = bbcodeRe.exec(attributes);
		if (matches != null) {
			imgtag = 'localimg';
			src = matches[2];
		}

		bbcodeRe = /aid=([\"\']?)attachimg_(\d+)(\1)/i;
		var matches = bbcodeRe.exec(attributes);
		if (matches != null) {
			return '[attachimg]' + matches[2] + '[/attachimg]';
		}*/

		return $width > 0 && $height > 0 ? "[{$imgtag}={$width},{$height}]{$src}[/{$imgtag}]" : "[img]{$src}[/img]";
	}

	/**
	 * 解析普通标签;
	 * @param unknown_type $tagname 标签名称;
	 * @param unknown_type $text 文本;
	 * @param unknown_type $func 处理函数;
	 * @param unknown_type $parse_to 需要把标签解析成该标签;
	 * @return unknown
	 */
	public  function _recursion($tagname, $text, $func, $parse_to) {
		$tagname = strtolower($tagname);
		$open_tag = "<{$tagname}";
		$open_tagLen = strlen($open_tag);
		$close_tag = "</{$tagname}>";
		$close_tag_len = strlen($close_tag);
		/** 寻找标签开始的位置 */
		$begins = array();
		$text_lower = strtolower($text);
		/** 如果连结束的标签都没有，则肯定不是配对的 */
		$tag_begin = strpos($text_lower, $open_tag);
		$tag_end = strpos($text_lower, $close_tag);
		if (false === $tag_end || false === $tag_begin) {
			return $text;
		}

		$strlen = strlen($text);
		$tagname_end = 0;
		$char = $inquote = '';
		$option_end = $tag_begin;
		while($option_end < $strlen) {
			$char = $text{$option_end};
			if (('"' == $char || "'" == $char) && empty($inquote)) {
				$inquote = $char;
			} elseif (('"' == $char || "'" == $char) && $inquote == $char) {
				$inquote = '';
			} elseif (empty($inquote) && '>' == $char) {
				$tagname_end = empty($tagname_end) ? $option_end : $tagname_end;
				/** 获取标签 */
				$curTagname = substr($text_lower, $tag_begin + 1, $tagname_end - $tag_begin - 1);
				if ($curTagname == $tagname) {
					array_unshift($begins, array($tag_begin, $tagname_end, $option_end));
				}

				/** 继续从下一个标签开始的地方开始寻找 */
				$option_end = strpos($text_lower, $open_tag, $option_end);
				/** 如果没有找到，则退出 */
				if (false === $option_end) {
					break;
				}

				/** 重新初始化 */
				$tag_begin = $option_end;
				$tagname_end = 0;
			} elseif ((' ' == $char || '=' == $char) && empty($tagname_end)) {
				$tagname_end = $option_end;
			}

			$option_end ++;
		}

		/** 处理标签 */
		foreach ($begins as $key => $value) {
			$tag_end = strpos($text_lower, $close_tag, $value[1]);
			if (false === $tag_end) {
				$text = substr_replace($text, "&lt;{$tagname}", $value[0], strlen("<{$tagname}"));
			} else {
				$bbcode = $this->$func($tagname, substr($text, $value[1], $value[2] - $value[1]), substr($text, $value[2] + 1, $tag_end - $value[2] - 1), $parse_to);
				$text = substr_replace($text, $bbcode, $value[0], $tag_end - $value[0] + strlen("</{$tagname}>"));
			}
		}
		return $text;
	}

	/**
	 * 普通的简单标签;
	 * @param unknown_type $tagname
	 * @param unknown_type $options
	 * @param unknown_type $text
	 * @param unknown_type $parse_to
	 * @return unknown
	 */
	public  function _simple_tag($tagname, $options, $text, $parse_to = '') {
		if (empty($text)) {
			return '';
		}

		return "[{$parse_to}]{$text}[/{$parse_to}]";
	}

	/**
	 * 解析A标签;
	 * @param unknown_type $tagname
	 * @param unknown_type $aoptions
	 * @param unknown_type $text
	 * @param unknown_type $parse_to
	 * @return unknown
	 */
	public  function _a_tag($tagname, $aoptions, $text, $parse_to = '') {
		$text = trim($text);
		if (empty($text)) {
			return '';
		}

		$href = $this->_get_option_value('href', $aoptions);
		$tag;
		if ('javascript:' == substr($href, 0, 11)) {
			return $text;
		} else if ('mailto:' == substr($href, 0, 7)) {
			$tag = 'email';
			$href = substr($href, 7);
		} else {
			$tag = 'url';
		}

		return "[{$tag}={$href}]{$text}[/{$tag}]";
	}

	/**
	 * 获取指定属性值;
	 * @param unknown_type $option
	 * @param unknown_type $text
	 * @return unknown
	 */
	public  function _get_option_value($option, $text) {
		preg_match_all("/{$option}(\s+?)?\=(\s+?)?[\"']?(.+?)([\"']|$|>)/i", $text, $matches);
		if (!empty($matches[3][0])) {
			return trim($matches[3][0]);
		}

		return '';
	}

	/**
	 * 解析 font 标签;
	 * @param unknown_type $tagname
	 * @param unknown_type $fontopts
	 * @param unknown_type $text
	 * @param unknown_type $parse_to
	 * @return unknown
	 */
	public  function _font_tag($tagname, $fontopts, $text, $parse_to = '') {
		$tags = array('font' => 'face=', 'size' => 'size=', 'color' => 'color=');
		$pre = $suffix = '';
		foreach ($tags as $key => $value) {
			preg_match("/{$value}([\"\']?)(.*?)\\1/i", $text, $matches);
			if (!empty($matches[2][0])) {
				$pre .= "[{$key}]";
				$suffix = "[/{$key}]{$suffix}";
			}
		}

		return "{$pre}{$text}{$suffix}";
	}

	/**
	 * 解析 ol, ul 标签;
	 * @param unknown_type $tagname
	 * @param unknown_type $listoptions
	 * @param unknown_type $text
	 * @param unknown_type $parse_to
	 * @return unknown
	 */
	public  function _list_tag($tagname, $listoptions, $text, $parse_to = '') {
		if (empty($text)) {
			return '';
		}

		$olType = '';
		if ('ol' == $tagname) {
			preg_match_all("/type=([\"\']?)(.*?)\\1/i", $listoptions, $matches);
			if (!empty($matches[2][0])) {
				$olType = trim($matches[2][0]);
			}
		}

		$text = rpreg_replace("/<li([^]*)>(.*?)<\/li>/is", array('callback' => array($this, '_li_tag'), 'params' => array(2)), $text);

		return empty($text) ? '' : "[list".(empty($olType) ? '' : "type={$olType}")."]{$text}[/list]";
	}

	/**
	 * 解析 li 标签;
	 * @param unknown_type $text
	 * @return unknown
	 */
	public  function _li_tag($text) {
		return "[*]".trim($text);
	}

	/**
	 * 解析 div, p, span 标签;
	 * @param unknown_type $tagname
	 * @param unknown_type $options
	 * @param unknown_type $text
	 * @param unknown_type $parse_to
	 * @return unknown
	 */
	public  function _dsp_tag($tagname, $options, $text, $parse_to = '') {
		if (empty($text)) {
			return '';
		}

		$pend = $this->_parse_style($options);
		$prepend = $pend['prepend'];
		$append = $pend['append'];
		if (in_array($tagname, array('div', 'p'))) {
			$align = $this->_get_option_value('align', $options);
			if (in_array($align, array('left', 'center', 'right'))) {
				$prepend = "[align={$align}]{$prepend}";
				$append .= '[/align]';
			} else {
				$append .= "\n";
			}
		}

		return "{$prepend}{$text}{$append}";
	}

	/**
	 * 解析标签的style属性;
	 * @param unknown_type $options
	 * @return unknown
	 */
	public  function _parse_style($options) {
		$styles = array(
			'align' => "text-align:\s*(left|center|right);?",
			'color' => "[^-]color:\s*([^;]+);?",
			'font' => "font-family:\s*([^;]+);?",
			'size' => "font-size:\s*(\d+(px|pt|in|cm|mm|pc|em|ex|%|));?",
			'b' => "font-weight:\s*(bold);?",
			'i' => "font-style:\s*(italic);?",
			'u' => "text-decoration:\s*(underline);?",
		);

		$return = array();
		foreach ($styles as $key => $value) {
			preg_match_all("/{$value}/i", $options, $matches);
			if (!empty($matches[1][0])) {
				$return['prepend'] .= "[{$key}".(in_array($key, array('align', 'color', 'font', 'size')) ? "={$value}" : '')."]";
				$return['append'] = "[/{$key}]{$return['append']}";
			}
		}

		return $return;
	}
}
