<?php

/**
 * 上报地理位置 外出考勤的上报地理位置
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_upsuccess extends voa_c_frontend_sign_base {
	public function execute() {
		//接受数据
		$get = $this->request->getx();
		$post = $this->request->postx();

		$serv = &service::factory('voa_s_oa_sign_location');
		$uda = &Service::factory('voa_uda_frontend_sign_out');
		$serv_attachment = &service::factory('voa_s_oa_sign_attachment');
		//上传操作
		$sl_id = $get['sl_id'];
		if (!empty ($post ['atids'])) {
			$atids = explode(',', $post ['atids']);
			//构造插入数据
			$up_at = array();
			foreach ($atids as $ids) {
				$up_at[] = array(
					'outid' => $post ['sl_id'],
					'atid' => $ids
				);
			}
			//插入操作
			$serv_attachment->insert_multi($up_at);
			$sl_id = $post['sl_id'];
		}

		if (empty ($sl_id)) {
			$this->_error_message('获取详情失败');

			return false;
		}
		$data = $serv->get($sl_id);

		if (!$data) {
			$this->_error_message('获取详情失败');

			return false;
		}
		//格式数据
		$data = $uda->format($data);
		//剩余上传数量
		$count = count($data['attachs']);
		$last = 5 - $count;

		$this->view->set('last', $last);
		$this->view->set('data', $data);

		$this->_output('mobile/sign/upsuccess');

		return;
	}
}
