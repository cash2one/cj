<?php
class voa_c_admincp_office_vstore_index extends voa_c_admincp_office_base{

	public function execute() {
		//获取企业信息
		$p_sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$domain = $p_sets['domain'];
	    $domains = explode(".",$domain);
		//跳转微零售地址
		$cp_url = 'http://sell.vchangyi.com/go/'.$domains[0];
		$this->response->set_redirect($cp_url);
	}
}
