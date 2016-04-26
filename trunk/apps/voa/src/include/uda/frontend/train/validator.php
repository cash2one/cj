<?php
/**
 * validator.php
 * 数据验证
 * $Author$
 * $Id$
 */
class voa_uda_frontend_train_validator extends voa_uda_frontend_train_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 检查文章标题
	 * @param string $title
	 * @return boolean
	 */
	public function article_title(&$title) {

		$title = (string)$title;
		$title = trim($title);
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['article_title'])) {
			$this->plugin_setting['article_title'] = array(
				voa_d_oa_train_article::LENGTH_TITLE_MIN,
				voa_d_oa_train_article::LENGTH_TITLE_MAX
			);
		}
		// 字符限制最小长度
		$min = max($this->plugin_setting['article_title'][0], voa_d_oa_train_article::LENGTH_TITLE_MIN);
		// 字符限制最大长度
		$max = min($this->plugin_setting['article_title'][1], voa_d_oa_train_article::LENGTH_TITLE_MAX);

		if (validator::is_string_count_in_range($title, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_train::ARTICLE_TITLE_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_train::ARTICLE_TITLE_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 检查文章作者
	 * @param string $author
	 * @return boolean
	 */
	public function article_author(&$author) {

		$author = (string)$author;
		$author = trim($author);
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['article_author'])) {
			$this->plugin_setting['article_author'] = array(
				voa_d_oa_train_article::LENGTH_TITLE_MIN,
				voa_d_oa_train_article::LENGTH_TITLE_MAX
			);
		}
		// 字符限制最小长度
		$min = max($this->plugin_setting['article_author'][0], voa_d_oa_train_article::LENGTH_AUTHOR_MIN);
		// 字符限制最大长度
		$max = min($this->plugin_setting['article_author'][1], voa_d_oa_train_article::LENGTH_AUTHOR_MAX);

		if (validator::is_string_count_in_range($author, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_train::AUTHOR_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_train::AUTHOR_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 检查目录标题
	 * @param string $title
	 * @return boolean
	 */
	public function category_title(&$title) {

		$title = (string)$title;
		$title = trim($title);
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['category_title'])) {
			$this->plugin_setting['category_title'] = array(
				voa_d_oa_train_category::LENGTH_TITLE_MIN,
				voa_d_oa_train_category::LENGTH_TITLE_MAX
			);
		}
		// 字符限制最小长度
		$min = max($this->plugin_setting['category_title'][0], voa_d_oa_train_category::LENGTH_TITLE_MIN);
		// 字符限制最大长度
		$max = min($this->plugin_setting['category_title'][1], voa_d_oa_train_category::LENGTH_TITLE_MAX);

		if (validator::is_string_count_in_range($title, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_train::CATEGORY_TITLE_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_train::CATEGORY_LENGTH_MAX_ERROR, $max);
		}
	}

}
