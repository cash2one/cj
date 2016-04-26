<?php
/**
 * voa_c_admincp_office_news_addcategory
* 企业后台/微办公管理/新闻公告/批量添加公告类型
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_news_addcategory extends voa_c_admincp_office_news_base {

	public function execute() {

		$ids = array();
		$ne_ids = $this->request->get('ne_ids');
		$nca_id = $this->request->get('nca_id');

		if ($ne_ids) {
			$ids = rintval(explode(',', $ne_ids), true);
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 ' . $this->_module_plugin['cp_name'] . ' 数据');
		}

		$uda = &uda::factory('voa_uda_frontend_news_addcategory');
		if ($uda->add($ids, $nca_id)) {
			$this->message('success', '修改类型成功', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '修改类型失败');
		}
	}

}
