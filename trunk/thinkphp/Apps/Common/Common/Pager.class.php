<?php
/**
 * 通用分页程序
 *
 * $Author$
 * $Id$
 */
/**
 *
 * @example
 * $pagerOptions	=	array(
 * 		'total_items'			=>	1000,//数据总数
 * 		'per_page'				=>	8,//每页显示数据量
 * 		'current_page'			=>	$_GET['page'],//当前页码
 * 		'show_total_items'		=>	true,//默认不显示。是否显示数据总数 total_items，可选值为：true|false
 * 		'wrapper_class_string'	=>	'',//分页区域的样式名。默认为：pagination，可选值为：
 * 					//pagination | pagination pagination-lg | pagination pagination-sm
 * 					//分别代表分页尺码：中|大|小
 * 	);
 * echo pager::make_links($pagerOptions);
 * 默认的样式表定义：见页尾
 */
namespace Common\Common;
use Common\Common\Controller_request;
class Pager {

	/** 地址的前缀 */
	private $_prefix_url = '';
	/** 地址的后缀 */
	private $_suffix_url = '';
	/** 所有数据 */
	private $_item_data = null;
	/** 总的数据条数 */
	private $_total_items = 0;
	/** 文件名 */
	private $_file_name = '';
	/** 每页显示数据的条数 */
	private $_per_page = 10;
	/** 当前页两边的显示的页数 */
	private $_delta = 2;
	/** 当面页号 */
	private $_current_page = 1;
	/** 链接属性 */
	private $_link_attribute = "";
	/** 连接的样式 */
	private $_link_class = '';
	/** 页号变量名 */
	private $_url_var = 'page';
	/** 上一页文字用于链接的提示（title属性值） */
	private $_alt_prev = '上一页';
	/** 上一页链接内的文字 */
	private $_alt_prev_icon	=	'&laquo;';
	/** 下一页文字用于链接的提示（title属性值） */
	/** 下一页链接内的文字 */
	private $_alt_next = '下一页';
	/** 分页链接区域的总样式，默认可以：pagination，pagination pagination-lg，pagination pagination-sm */
	private $_wrapper_class_string = 'pagination';
	/***/
	private $_alt_next_icon	=	'&raquo;';
	private $_alt_prev_class = '';
	private $_alt_next_class = '';
	private $_dot_class = '';
	/** 上一分页<< */
	private $_first_img = '1';
	/** 下一分页>> */
	private $_last_img = '';
	private $_expanded = true;
	private $_cur_page_class= '';
	/** 需要忽略的字段 */
	private $_ignore_query = array();
	/** 如果没有分页是是否显示分页 */
	private $_clear_if_void = true;
	/** 把所有的参数赋给这个变量 */
	private $_assign_query = '';
	/** 是否显示总页数 */
	private $_show_total_items = false;
	/** 每个URL中添加总记录数 */
	private $_url_add_total_items = false;
	/** 显示更多链接 */
	private $_show_more_page = false;
	/** 显示上一页、下一页链接 */
	private $_show_pre_and_next_page= false;
	private $_prev_page_html = '';
	private $_next_page_html = '';
	/** 链接后的锚点字符串 */
	private $_fragment = '';
	public $range = array();
	/** 标识出是否打印了第一页 */
	private $_is_print_first_page = false;

	/** 链接参数分隔符号 */
	private $_url_comma	=	'&amp;';

	public $links = '';

	public function __construct($options = array()) {

		$this->_set_options($options);
		$this->_generate_page_data();

		if ($this->_url_add_total_items) {
			$this->_ignore_query[] = 'total_items';
		}

		$this->links .= $this->_get_wrapper_start_tag();

		if ($this->_show_total_items == true && $this->_total_items) {
			$this->links .= $this->_get_total_items();
		}

		/** 打印上一页 */
		if ($this->_show_pre_and_next_page) {
			if ($this->_total_pages > 1) {
				$this->links .= $this->_get_back_link();
			}
		} else {
			if ($this->_current_page > 1) {
				$this->links .= $this->_get_back_link();
			}
		}
		/** 打印第一页 */
		if ($this->_current_page >= 2 * $this->_delta - 1 && $this->_total_pages > 2 * $this->_delta + 1) {
			$this->links .= $this->_print_first_page();
			$this->_is_print_first_page = true;
		}

		/** 打印省略 */
		if ($this->_total_pages > 2 * $this->_delta + 1 && $this->_current_page > 2 * $this->_delta - 1) {
			/** 如果current_page距delta为第二页的话，是不需要打印省略的 */
			if ($this->_current_page - $this->_delta != 2) {
				$this->links .= $this->get_page_url(null);
			}
		}

		$this->links .= $this->_get_page_links();
		if ($this->_total_pages > (2 * $this->_delta + 1)) {
			if ($this->_current_page + $this->_delta + 1 < $this->_total_pages) {
				$this->links .= $this->get_page_url(null);
			}
			$this->links .= $this->_print_last_page();
		}

		/** 下一页链接 */
		if ($this->_show_pre_and_next_page) {
			if ($this->_total_pages > 1 && $this->get_current_page_id() < $this->_total_pages) {
				$this->links .= $this->_get_next_link();
			}
		} else {
			if ($this->_current_page < $this->_total_pages) {
				$this->links .= $this->_get_next_link();
			}
		}

		if ($this->_show_more_page && $this->_total_pages > 1) {
			$this->links .= $this->_get_more_link();
		}

		$this->links .= $this->_get_wrapper_end_tag();

	}

	public static function make_links($options) {

		$pager = new self($options);
		return $pager->get_links();
	}

	public function get_back_link_by_html($html) {

		$link = $this->get_page_url($this->get_previous_page_id(), '', true);
		return sprintf($html, $link);
	}

	public function get_next_link_by_html($html) {

		$link = $this->get_page_url($this->get_next_page_id(), '', true);
		return sprintf($html, $link);
	}

	public function get_links() {

		return $this->links;
	}

	public function get_page_data($page_id = null) {

		if ($page_id !== null) {
			if (!empty($this->_page_data[$page_id])) {
				return $this->_page_data[$page_id];
			}
		} else {
			return false;
		}

		if (!isset($this->_page_data)) {
			$this->_generate_page_data();
		}

		return $this->get_page_data($this->_current_page);
	}

	public function num_pages() {

		return (int)$this->_total_pages;
	}

	public function get_offset_by_page_id($page_id = null) {

		$page_id = ($page_id) ? $page_id : $this->_current_page;

		if (isset($this->_page_data)) {
			$this->_generate_page_data();
		}
		if ($this->_total_pages > 1) {
			return array(
						 max($page_id - $this->_delta , 1),
						 min($page_id + $this->_delta , $this->num_pages())
						);
		} else {
			return array(0, 0);
		}
	}

	public function get_current_page_id() {

		return $this->_current_page;
	}

	public function get_next_page_id() {

		return ($this->get_current_page_id() == $this->num_pages()) ?
			false : $this->get_current_page_id() + 1;
	}

	public function get_previous_page_id() {

		return $this->is_first_page() ? false : $this->get_current_page_id() - 1;
	}

	public function get_total_items() {

		return $this->_total_items;
	}

	public function get_total_pages() {

		return (int)$this->_total_pages;
	}

	public function is_first_page() {
		return ($this->_current_page == 1);
	}

	public function is_last_page() {
		return ($this->_current_page == $this->_total_pages);
	}

	private function _get_back_link() {

		if ($this->_current_page > 1) {
			$back = $this->get_page_url($this->get_previous_page_id(), $this->_alt_prev);
		} else {
			$back = '';
		}
		return $back;
	}

	private function _get_next_link() {

		if ($this->_current_page < $this->_total_pages) {
			$next = $this->get_page_url($this->get_next_page_id(), $this->_alt_next);
		} else {
			$next = '';
		}
		return $next;
	}

	private function _get_more_link() {

		$page_link = $this->_prefix_url.$this->_suffix_url ;
		/** 更多. */
		$page_id = $this->_total_pages + 1;
		if ($this->_assign_query) {
			$special_url = $this->_assign_query.'='.urlencode($this->_get_page_link()).$page_id;
		} else {
			$special_url = $this->_get_page_link().$page_id;
		}
		if ($page_link) {
			$delim = strpos($page_link, '?') ? $this->_url_comma : '?';
		} else {
			$delim = '';
		}
		$page_link .= $delim.$special_url;

		if ($this->_fragment) {
			$page_link .= '#'.$this->_fragment;
		}

		$page_name = 'more';
		$page_url = sprintf('<a%s href="%s"%s>%s</a>', $this->_link_class_string, $page_link, $this->_link_attribute, $page_name);

		return $page_url;
	}

	private function _get_page_links() {

		$links = '';
		if ($this->_total_pages > (2 * $this->_delta + 1)) {
			if ($this->_expanded) {
				if ($this->_total_pages - $this->_delta <= $this->_current_page) {
					$_expansion_before = $this->_current_page - ($this->_total_pages - $this->_delta);
					if ($this->_current_page != $this->_total_pages)  $_expansion_before++;
				} else {
					$_expansion_before = 0;
				}
				/** 从第一页到距当前页_delta页 */
				for($i = $this->_current_page - $this->_delta - $_expansion_before; $_expansion_before; $_expansion_before--, $i++) {
					/** 打算分隔符，暂时不用先留着 */
					if (($i != $this->_current_page + $this->_delta) && ($i != $this->_total_pages - 1)) {
						$_print_separator_flag = true;
					} else {
						$_print_separator_flag = false;
					}

					$this->range[$i] = false;
					$links .= $this->get_page_url($i);
				}
			}
			$_expansion_after = 0;

			/** 距当前页_delta左右两边的页 */
			for($i = $this->_current_page - $this->_delta;
				($i <= $this->_current_page + $this->_delta) && ($i < $this->_total_pages);
				$i++) {

				if ($i < 1 && $i != $this->_current_page) {
					$_expansion_after ++;
					continue;
				}

				/** 打算分隔符，暂时不用先留着 */
				if (($i != $this->_current_page + $this->_delta) && ($i != $this->_total_pages - 1)) {
					$_print_separator_flag = true;
				} else {
					$_print_separator_flag = false;
				}

				if ($i == $this->_current_page) {
					$this->range[$i] = true;
					$links .= $this->get_page_url($i);
				} else {
					$this->range[$i] = false;

					/** 如果打印了第一页，就不再打印 */
					if ($this->_is_print_first_page) {
						if ($i == 1) {
							continue;
						}
					}

					$links .= $this->get_page_url($i);
				}
			}

			/** 当前页是最后一页 */
			if ($this->_current_page == $this->_total_pages) {
				$this->range[$this->_current_page] = true;
				$links .= $this->get_page_url($this->_total_pages);
			}

			/** 从距当前_delta页到最后 */
			if ($this->_expanded && $_expansion_after) {
				for($i = $this->_current_page + $this->_delta + 1 ; $_expansion_after; $_expansion_after--, $i++) {
					if(($_expansion_after != 1)) {
						$_print_separator_flag = true;
					} else {
						$_print_separator_flag = false;
					}

					$this->range[$i] = false;
					$links .= $this->get_page_url($i);
				}
			}
		} else {
			for ($i = 1; $i <= $this->_total_pages; $i++) {
				if ($i != $this->_current_page) {
					$this->range[$i] = false;
					$links .= $this->get_page_url($i);
				} else {
					$this->range[$i] = true;
					$links .= $this->get_page_url($i);
				}
			}
		}
		if ($this->_clear_if_void) {
			if ($this->num_pages() < 2) $links = '';
		}
		return $links;
	}

	private function _print_first_page() {

		if ($this->is_first_page()) {
			return '';
		} else {
			return $this->get_page_url(1, $this->_first_img);
		}
	}

	private function _print_last_page() {

		if ($this->is_last_page()) {
			return '';
		} else {
			return $this->get_page_url($this->num_pages(), $this->num_pages());
		}
	}

	private function _generate_page_data() {

		if ($this->_item_data !== null) {
			$this->_total_items = count($this->_item_data);
		}
		$this->_total_pages = ceil((float)$this->_total_items / (float) $this->_per_page);

		/** 如果显示更多时，有可能出现不存在的页码 */
		if ($this->_url_add_total_items && $this->_current_page > $this->_total_pages) {
			$this->_total_pages += 1;
		} else {
			$this->_current_page= ($this->_current_page > $this->_total_pages) ? $this->_total_pages : $this->_current_page;
		}

		$i = 0;
		if (!empty($this->_item_data)) {
			foreach ($this->_item_data as $key => $value) {
				$this->_page_data[$i][$key] = $value;
				if (count($this->_page_data[$i]) >= $this->_per_page) {
					$i++;
				}
			}
		} else {
			$this->_page_data = array();
		}
	}

	public function get_page_url($page_id, $page_name = null, $is_return_link = false) {

		$page_link = $this->_prefix_url.$this->_suffix_url ;
		if ($this->_assign_query) {
			$special_url = $this->_assign_query.'='.urlencode($this->_get_page_link()).$page_id;
		} else {
			$special_url = $this->_get_page_link().$page_id;
		}
		if ($page_link) {
			$delim = strpos($page_link, '?') === false ? '?' : $this->_url_comma;
		} else {
			$delim = '';
		}
		$page_link .= $delim.$special_url;

		/** 分页URL中添加总记录数显示 */
		if ($this->_url_add_total_items) {
			$page_link .= $this->_url_comma.'total_items='.$this->_total_items;
		}

		if ($this->_fragment) {
			$page_link .= '#'.$this->_fragment;
		}

		if ($is_return_link) {
			return $page_link;
		}

		if ($page_id === null) {
			$page_url = sprintf('<li%s><a>...</a></li>', $this->_dot_class_string);
			return $page_url;
		}
		if ($page_name === null) {
			if ($this->_current_page == $page_id) {
				$page_url = sprintf('<li%s><a href="javascript:;">%d <span class="sr-only">(current)</span></a></li>', $this->_cur_page_class_string, $page_id);
			} else {
				$page_url = sprintf(
						'<li%s><a href="%s"%s title="%d">%d</a></li>',
						$this->_link_class_string, $page_link, $this->_link_attribute, $page_id, $page_id
				);
			}
		} else {
			if ($this->_current_page == $page_id) {
				$page_url = sprintf('<li%s><a href="javascript:;">%d <span class="sr-only">(current)</span></a></li>', $this->_cur_page_class_string, $page_id);
			} else {
				if ($page_name == $this->_alt_prev) {
					$page_url = sprintf(
							'<li><a%s href="%s"%s title="%s">%s</a></li>',
							$this->_alt_prev_class_string, $page_link, $this->_link_attribute, $page_name, $this->_alt_prev_icon
					);
				} else if ($page_name == $this->_alt_next) {
					$page_url = sprintf(
							'<li><a%s href="%s"%s title="%s">%s</a></li>',
							$this->_alt_next_class_string, $page_link, $this->_link_attribute, $page_name, $this->_alt_next_icon
					);
				} else {
					$page_url = sprintf(
							'<li%s><a href="%s"%s title="%d">%d</a></li>',
							$this->_link_class_string, $page_link, $this->_link_attribute, $page_id, $page_id
					);
				}
			}

		}

		$page_url .= "\r\n";

		return $page_url;
	}

	protected function _get_wrapper_start_tag() {
		return sprintf('<ul%s>', $this->_wrapper_class_string);
	}

	protected function _get_wrapper_end_tag() {
		return '</ul>';
	}

	/** 到当前页面的链接，可能并不是真正到达这个页面的链接地址 */
	protected function _get_page_link() {

		$link_query = array();
		$link_query_string = '';
		$this->_ignore_query[] = $this->_url_var;
		$get_params = controller_request::get_instance()->getx();

		$this->_file_name = preg_replace('/("|\')/' , '', strip_tags($this->_file_name));

		if ($get_params) {
			/**foreach ($get_params as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $kk => $vv) {
						if (is_array($vv)) {
							continue;
						}

						$get_params[$k.'['.$kk.']'] = strip_tags($vv);
					}
					unset($get_params[$k]);
				} else {
					$get_params[$k] = strip_tags($v);
				}
			}*/
			$ignore_key = array_flip($this->_ignore_query);
			$link_query = array_diff_key($get_params, $ignore_key);
			if ($link_query && is_array($link_query)) {
				$link_query_string = $this->_file_name.'?'.http_build_query($link_query, null, $this->_url_comma);
			} else {
				$link_query_string = $this->_file_name ;
			}
		}
		$delim = (strpos($link_query_string, '?') !== false) ? $this->_url_comma : '?';
		$link_query_string .= $delim.$this->_url_var.'=';

		return $link_query_string;
	}

	protected function _get_total_items() {

		$page_url = sprintf('<li class="disabled"><a>共 %d 条</a></li>', trim($this->_total_items));
		return $page_url;
	}

	private function _set_options($options) {

		$allowed_options = array(
			'prefix_url',
			'suffix_url',
			'item_data',
			'total_items',
			'file_name',
			'per_page',
			'delta',
			'current_page',
			'link_class',
			'fragment',
			'url_var',
			'alt_prev',
			'altNext',
			'alt_prev_class',
			'alt_next_class',
			'dot_class',
			'wrapper_class',
			'prev_img',
			'next_img',
			'expended',
			'cur_page_class',
			'ignore_query',
			'link_attribute',
			'assign_query',
			'show_total_items',
			'url_add_total_items',
			'show_more_page',
			'show_pre_and_next_page',
			'url_comma',
			'alt_prev_icon',
			'alt_next_icon',
			'wrapper_class_string'
		);
		foreach ($options as $key => $value) {
			if (in_array($key, $allowed_options) && ($value !== null)) {
				$this->{'_'.$key} = $value;
			}
		}
		$this->_prefix_url = rtrim($this->_prefix_url, '/');
		$this->_suffix_url = rtrim($this->_suffix_url, '/');

		if (strlen($this->_link_class)) {
			$this->_link_class_string = ' class="'.$this->_link_class.'"';
		} else {
			$this->_link_class_string = '';
		}

		if (strlen($this->_alt_prev_class)) {
			$this->_alt_prev_class_string = ' class="'.$this->_alt_prev_class.'"';
		} else {
			$this->_alt_prev_class_string = '';
		}
		if ( !$this->_alt_prev_icon ) {
			$this->_alt_prev_icon	=	$this->_alt_prev;
		}

		if (strlen($this->_alt_next_class)) {
			$this->_alt_next_class_string = ' class="'.$this->_alt_next_class.'"';
		} else {
			$this->_alt_next_class_string = '';
		}
		if ( !$this->_alt_next_icon ) {
			$this->_alt_next_icon	=	$this->_alt_next;
		}

		if (strlen($this->_cur_page_class)) {
			$this->_cur_page_class_string = ' class="'.$this->_cur_page_class.'"';
		} else {
			$this->_cur_page_class_string = ' class="active"';
		}

		if (strlen($this->_dot_class)) {
			$this->_dot_class_string = ' class="'.$this->_dot_class.'"';
		} else {
			$this->_dot_class_string = ' class="disabled"';
		}

		if (isset($this->_wrapper_class) && strlen($this->_wrapper_class)) {
			$this->_wrapper_class_string = ' class="'.$this->_wrapper_class.'"';
		} else {
			$this->_wrapper_class_string = ' class="pagination"';
		}

		if ($this->_per_page < 1) {
			$this->_per_page = 1;
		}

		$this->_current_page = max($this->_current_page, 1);
	}

	/**
	 * resolve_options
	 * 解决Page参数关系
	 *
	 * @param  integer $pageOption 页面数据参数
	 *  + per_page 每页显示数
	 *  + current_page 当前页数
	 *  + start 开始记录数
	 * @return array
	 */
	public static function resolve_options(&$options) {

		if (!$options) {
			return array();
		}

		if (empty($options['per_page'])) {
			$options['per_page'] = 20;
		}

		if (empty($options['current_page'])) {
			$options['current_page'] = controller_request::get_instance()->get('page', 1);
		}

		if (isset($options['total_items']) && $options['total_items'] === null) {
			unset($options['total_items']);
		}

		if (isset($options['total_items']) && $options['total_items'] > 0) {
			$page_num = ceil($options['total_items'] / $options['per_page']);
			if ($options['current_page'] > $page_num) {
				$options['current_page'] = $page_num;
			}
		}

		/** 显示分页处理 中MORE 设置 */
		$interval = 1000;
		if (isset($options['total_items'])) {
			$n = intval(($options['total_items'] - 1) / $interval);
			if ($n * $interval + 1 == $options['total_items']) {
				$options['show_more_page'] = true;
			} else {
				$options['show_more_page'] = false;
			}
		}

		$options['start'] = ($options['current_page'] - 1) * $options['per_page'];
		$options['to'] = $options['start'] + $options['per_page'] - 1;

		return $options;
	}

	public function get_wap_link() {

		$link = "";
		$totalText = "共".$this->_total_pages."页";
		$page_text = "第".$this->_current_page."/".$this->_total_pages."页";
		if ($this->_total_pages <= 1) {
			return $link;
		}

		if ($this->_total_pages == 2) {
			if ($this->_current_page == 1) {
				$link .= $this->_get_wap_page_url(2, $this->_alt_next);
			} if ($this->_current_page == 2) {
				$link .= $this->_get_wap_page_url(1, $this->_alt_prev);
			}
			$link .= "<br />".$page_text;
		} elseif ($this->_total_pages >= 3 && $this->_total_pages <= 8) {
			if ($this->_current_page != $this->_total_pages) {
				$link .= $this->_get_wap_page_url($this->get_next_page_id(), $this->_alt_next)." ";
			}
			if ($this->_current_page > 1) {
				$link .= $this->_get_wap_page_url($this->get_previous_page_id(), $this->_alt_prev)." ";
			}
			$link .= $totalText."<br />";
			for ($i = 1; $i <= $this->_total_pages; $i ++) {
				$link .= $this->_get_wap_page_url($i, $i)." ";
			}
		} else {
			if ($this->_current_page != $this->_total_pages) {
				$link .= $this->_get_wap_page_url($this->get_next_page_id(), $this->_alt_next)." ";
			}
			if ($this->_current_page > 1) {
				$link .= $this->_get_wap_page_url($this->get_previous_page_id(), $this->_alt_prev)." ";
			}
			if ($this->_current_page > 1) {
				$link .= $this->_get_wap_page_url(1, "首页")." ";
			}
			if ($this->_current_page != $this->_total_pages) {
				$link .= $this->_get_wap_page_url($this->_total_pages, "末页");
			}
			$link .= "<br /><form method='get'>";
			preg_match('/[0-9a-zA-Z-_]+/', trim($_GET['sid']), $match);
			$sid = $match[0];
			if ($sid) {
				$link .= '<input type="hidden" name="sid" value="'.$sid.'" />';
			}
			$link .= $page_text." ";
			$link .= '到<input type="text" name="p" class="ipt" />页 <input type="submit" class="sub" value="跳 转" /></form>';
		}

		return $link;
	}

	private function _get_wap_page_url($page_id, $page_name) {

		$page_link = $this->_get_page_link().$page_id;
		if ($this->_current_page != $page_id) {
			$page_url = sprintf('<a href="%s" title="%s">%s</a>', $page_link, $page_name, $page_name);
		} else {
			$page_url = "[$page_id]";
		}

		return $page_url;
	}
}

/** 默认样式定义：

.sr-only{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0, 0, 0, 0);border:0;}
.pagination{display:inline-block;padding-left:0;margin:20px 0;border-radius:4px;}
.pagination>li{display:inline;}
.pagination>li>a
	,.pagination>li>span{position:relative;float:left;padding:6px 12px;line-height:1.428571429;text-decoration:none;background-color:#ffffff;border:1px solid #dddddd;margin-left:-1px;}
.pagination>li:first-child>a
	,.pagination>li:first-child>span{margin-left:0;border-bottom-left-radius:4px;border-top-left-radius:4px;}
.pagination>li:last-child>a
	,.pagination>li:last-child>span{border-bottom-right-radius:4px;border-top-right-radius:4px;}
.pagination>li>a:hover
	,.pagination>li>span:hover
	,.pagination>li>a:focus
	,.pagination>li>span:focus{background-color:#eeeeee;}
.pagination>.active>a
	,.pagination>.active>span
	,.pagination>.active>a:hover
	,.pagination>.active>span:hover
	,.pagination>.active>a:focus
	,.pagination>.active>span:focus{z-index:2;color:#ffffff;background-color:#428bca;border-color:#428bca;cursor:default;}
.pagination>.disabled>span
	,.pagination>.disabled>span:hover
	,.pagination>.disabled>span:focus
	,.pagination>.disabled>a
	,.pagination>.disabled>a:hover
	,.pagination>.disabled>a:focus{color:#999999;background-color:#ffffff;border-color:#dddddd;cursor:not-allowed;}
.pagination-lg>li>a
	,.pagination-lg>li>span{padding:10px 16px;font-size:18px;}
.pagination-lg>li:first-child>a
	,.pagination-lg>li:first-child>span{border-bottom-left-radius:6px;border-top-left-radius:6px;}
.pagination-lg>li:last-child>a
	,.pagination-lg>li:last-child>span{border-bottom-right-radius:6px;border-top-right-radius:6px;}
.pagination-sm>li>a
	,.pagination-sm>li>span{padding:5px 10px;font-size:12px;}
.pagination-sm>li:first-child>a
	,.pagination-sm>li:first-child>span{border-bottom-left-radius:3px;border-top-left-radius:3px;}
.pagination-sm>li:last-child>a
	,.pagination-sm>li:last-child>span{border-bottom-right-radius:3px;border-top-right-radius:3px;}

 */
