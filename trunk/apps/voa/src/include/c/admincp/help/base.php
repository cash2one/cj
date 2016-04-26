<?php
/**
 * base.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_help_base extends voa_c_admincp_base {

	/** 当前动作 list or view */
	protected $_category = 'faq';

	/** 标题栏 */
	protected $_navtitle = '帮助中心';

	/** 当前分类的文档储存目录 */
	protected $_depot_dir = '';

	/** 文档扩展名 */
	protected $_doc_ext = '.htm';

	/** 文档列表索引文件路径 */
	protected $_list_file = '';

	/** 文档内的图片文件url目录 */
	protected $_help_img_dir = '/admincp/static/help/';

	/** 当前类型文档列表数组 */
	protected $_category_doc_list = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 文档类型映射
		$categorys = array('faq' => '常见问题', 'guide' => '新手入门');

		// 当前类型
		$this->_category = (string)$this->request->get('category');
		if (!isset($categorys[$this->_category])) {
			$this->_category = 'faq';
		}

		// 文档储存目录
		$this->_depot_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$this->_category.DIRECTORY_SEPARATOR;
		// 索引文件路径
		$this->_list_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'list.php';

		// 检查文档索引文件是否存在
		if (!is_file($this->_list_file)) {
			$this->_message('error', '帮助文档索引无法读取');
		}

		// 文档列表数组
		$_doc_list = array();
		@include $this->_list_file;
		if (empty($_doc_list[$this->_category])) {
			$this->_message('error', '暂无相关文档');
		}

		// 当前文档列表
		$this->_category_doc_list = array();
		foreach ($_doc_list[$this->_category] as $index_title => $s) {
			if (is_array($s)) {
				$this->_category_doc_list[$index_title] = array(
					'title' => $s[1],
					'url' => $this->view_help_url($s[1]),
					'filename' => $s[0],
				);
			} else {
				$this->_category_doc_list[$index_title] = array(
					'title' => $index_title,
					'url' => $this->view_help_url($index_title),
					'filename' => $s,
				);
			}
		}

		// 当前类型名称
		$this->view->set('category_name', $categorys[$this->_category]);
		// 当前读取的类型
		$this->view->set('category', $this->_category);
		// 标题栏文字
		$this->_navtitle = $categorys[$this->_category].' - 帮助中心';
		// 文档静态文件目录
		$this->view->set('help_img_dir', $this->_help_img_dir);

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);


		return true;
	}

}
