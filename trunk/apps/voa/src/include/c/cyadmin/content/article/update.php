<?php
/**
 * voa_c_cyadmin_article_update
 * 编辑文章
 */
class voa_c_cyadmin_content_article_update extends voa_c_cyadmin_content_article_base {

	public function execute() {
		$aid = (int) $this->request->post('aid');
		$uda = &uda::factory('voa_uda_cyadmin_content_article_list');
		$this->_is_legal($_POST['title'], 2, 20, '标题长度在2-20字符', 'utf-8');
		$this->_is_legal($_POST['description'], 2, 120, '摘要长度在2-120字符','utf-8');
		$this->_is_legal($_POST['content'], 10, 35000, '内容长度在10-35000字符', 'utf-8');
		$this->_is_negative($_POST['asort']);
		if (!empty($_POST['sourl'])) {
			$this->_is_url($_POST['sourl']);
		}
		$result = $uda->update_news($aid, $_POST);
		if ($result === true) {
			$this->message('success', '文章更新成功', $this->cpurl($this->_module, 'article', 'list'), false);
		} else {
			$this->message('error', '文章更新失败');
		}
	}
}
