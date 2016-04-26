<?php
/**
 * view.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_help_view extends voa_c_admincp_help_base {

	public function execute() {

		$doc_title = (string)$this->request->get('read');
		$doc_title = rhtmlspecialchars($doc_title);

		if (!isset($this->_category_doc_list[$doc_title])) {
			$this->_message('error', '指定帮助文档不存在');
		}

		// 文档信息数据
		$data = $this->_category_doc_list[$doc_title];

		$filename = $data['filename'];

		$filepath = $this->_depot_dir.$filename;
		if (!is_file($filepath)) {
			$this->_message('error', '指定帮助文档文件不存在');
		}

		$content = @file_get_contents($filepath);
		$content = str_replace('{$help_img_dir}', $this->_help_img_dir, $content);

		$this->view->set('content', $content);
		$this->view->set('nav_title', $doc_title.' - '.$this->_navtitle);
		$this->view->set('doc_title', $doc_title);

		$this->output('help/view');
	}

}
