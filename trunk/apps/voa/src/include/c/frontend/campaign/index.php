<?php
/**
 * 编辑报名信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_campaign_index extends voa_c_frontend_campaign_base {

	public function execute() {

		//获取企业信息
		$p_sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$domain = $p_sets['domain'];
		//跳转地址
		$ac = $this->request->get('ac');
        switch ($ac) {
        	case 'list':
        		$cp_url = 'http://' . $domain . '/previewh5/index.html?_ts='.startup_env::get('timestamp').'#/app/page/list/home';
        		break;
        	case 'personal':
        		$cp_url = 'http://' . $domain . '/previewh5/index.html?_ts='.startup_env::get('timestamp').'#/app/page/user/home';
        		break;
        	case 'ranking':
        		$cp_url = 'http://' . $domain . '/previewh5/index.html?_ts='.startup_env::get('timestamp').'#/app/page/user/top';
        		break;
        	default:
        		break;
        }
        $this->response->set_redirect($cp_url);
	}
}
