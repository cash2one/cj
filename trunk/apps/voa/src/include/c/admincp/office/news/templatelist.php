<?php
/**
 * vao_c_admincp_office_news_templatelist
 * 后台新闻公告/
 * @date: 2015年5月15日
 * @author: kk
 * @version:
 */

class voa_c_admincp_office_news_templatelist extends voa_c_admincp_office_news_base {

	public function execute() {

		//获取所有用户信息
		//$servm = &service::factory('voa_server_cyadmin_news', array('pluginid' => 0));
		//$list = $servm->template_list();

		$rpc = voa_h_rpc::phprpc(config::get('voa.cyadmin_url').'OaRpc/Rpc/NewsTemplates');
		$list = $rpc->list_template();

		$this->view->set('list', $list);
		$this->view->set('add_url', $this->cpurl($this->_module, $this->_operation, 'add', $this->_module_plugin_id));
		$this->view->set('madd_url', $this->cpurl($this->_module, $this->_operation, 'madd', $this->_module_plugin_id));
		$this->view->set('tem_add', $this->cpurl($this->_module, $this->_operation, 'add', $this->_module_plugin_id, array('tem_id' => '')));
		$this->output('office/news/templatelist');
	}
}
