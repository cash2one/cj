<?php
/**
 * 删除文章
 * voa_c_cyadmin_article_delete
 */
class voa_c_cyadmin_content_article_delete extends voa_c_cyadmin_content_article_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_article_list');
		$ids = 0;
		$delete = $this->request->get('delete');
		$aid = $this->request->get('aid');
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($aid) {
			$ids = rintval($aid, false);
			if (!empty($ids)) {
				$ids = array(
					$ids 
				);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的 数据');
		}
		$result = $uda->del_news($ids);
		if ($result === true) {
			$this->message('success', '指定信息删除完毕', $this->cpurl($this->_module, 'article', 'list'), false);
		} else {
			$this->message('error', '指定信息删除操作失败');
		}
	}
}
