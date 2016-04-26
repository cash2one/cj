<?php
/**
 * voa_c_cyadmin_index_home
 * 主站后台/首页
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_index_home extends voa_c_cyadmin_base {

	public function execute() {

		$this->view->set('controler', $this->controller_name);

		/*
		// 设置开通、关闭、删除应用操作
		$uda_application_app = &uda::factory('voa_uda_cyadmin_enterprise_app');

		// 开通
		$type = 'open';
		if ($uda_application_app->post_to_oasite($domain, $type, $ea_id)) {
			// 操作成功
		} else {
			// 操作失败
			echo $uda_application_app->error;
		}

		// 关闭
		$type = 'close';
		if ($uda_application_app->post_to_oasite($domain, $type, $ea_id)) {
			// 操作成功
		} else {
			// 操作失败
			echo $uda_application_app->error;
		}

		// 删除
		$type = 'delete';
		if ($uda_application_app->post_to_oasite($domain, $type, $ea_id)) {
			// 操作成功
		} else {
			// 操作失败
			echo $uda_application_app->error;
		}
		*/

		$this->output('cyadmin/index/index_home');
	}

}
