<?php
/**
 * voa_c_cyadmin_content_article_insert
 * 添加文章
 */
class voa_c_cyadmin_content_article_insert extends voa_c_cyadmin_content_article_base {

	public function execute() {
		$uda_insert = &uda::factory('voa_uda_cyadmin_content_article_list');
		$data = array();
		
		$this->_is_legal($_POST['title'], 2, 20, '标题长度在2-20字符', 'utf-8');
		$this->_is_legal($_POST['description'], 2, 120, '摘要长度在2-120字符','utf-8');
		$this->_is_legal($_POST['content'], 10, 35000, '内容长度在10-35000字符', 'utf-8');
		$this->_is_negative($_POST['asort']);
		if (!empty($_POST['sourl'])) {
			$this->_is_url($_POST['sourl']);
		}
		
		$result = $uda_insert->add_news($_POST, $data);
		if ($result === true) {
			$this->message('success', '文章添加成功', $this->cpurl($this->_module, 'article', 'list'), false);
		} else {
			$this->message('error', '文章添加失败');
		}
	}
}
