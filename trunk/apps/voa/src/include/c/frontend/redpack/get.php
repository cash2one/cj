<?php
/**
 * get.php
 * 领取红包前台
 * GET:
 * + redpack_id 红包ID
 * $Author$
 * $Id$
 */
class voa_c_frontend_redpack_get extends voa_c_frontend_redpack_base {

	public function execute() {

		// 请求的红包活动ID
		$redpack_id = (int)$this->request->get('redpack_id', 0);
		// 获取 wx_openid
		$openid = '';
		if (!$this->_get_wx_openid($openid)) {
			$this->_set_errcode('400:请刷新页面, 重新领取');
			return true;
		}

		try {
			// 载入uda
			$uda_get = &uda::factory('voa_uda_frontend_redpack_get');
			// 红包信息
			$redpack = array();
			// 读取红包信息
			$request = array(
				'redpack_id' => $redpack_id
			);
			$uda_get->doit($request, $redpack);
			// 标题
			$this->view->set('navtitle', '领取红包');
			// 红包信息
			$this->view->set('redpack', $redpack);
			// 载入模板
			$this->_output('mobile/redpack/my');
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage() . '[Err: ' . $h->getCode() . ']');
		} catch (Exception $e) {
			logger::error("redpack_id: {$redpack_id}|{$e->getCode()}|{$e->getMessage()}");
			$this->_error_message('抱歉，读取红包信息发生系统错误');
		}
	}

}
