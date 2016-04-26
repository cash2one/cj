<?php

/**
 * 上报地理位置，外出考勤签到
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_uplocation extends voa_c_frontend_sign_base {

	/*内部错误信息码*/
	private $__errcode = 0;
	/**
	 * 内部错误信息
	 */
	private $__errmsg = '';
	private $__use_system_location = 0;

	public function execute() {

		//header('Location: /h5/index.html?_ts=' . startup_env::get('timestamp') . '#/app/page/checking-in/outer');
		//$this->response->stop();
		$url = '/h5/index.html?_ts=' . startup_env::get('timestamp') . '#/app/page/checking-in/outer';
		$this->view->set('redirect_url', $url);
		$this->_output('mobile/redirect');
		exit;
		$post = $this->request->postx();

		if (!empty ($post)) {
			if (empty ($post ['address'])) {
				$this->_error_message('地理位置上报操作失败');
				return false;
			}

			// 初始化经纬度
			$get_lon_lat = $this->get_lon_lat($post ['location']);
			$g_longitude = $get_lon_lat['g_longitude'];
			$g_latitude = $get_lon_lat['g_latitude'];

			// $post['address'] = '上海市北区';

			// 待插入的位置上报数据
			$insert = $this->insert_data($g_longitude, $g_latitude, $post ['address']);

			$serv_location = &service::factory('voa_s_oa_sign_location');
			$serv_attachment = &service::factory('voa_s_oa_sign_attachment');
			$result = $serv_location->insert($insert);
			// 插入失败提示
			if (empty ($result)) {
				$this->_error_message('地理位置上报操作失败');

				return false;
			}
			// 上传了附件
			$this->upload_fj($post ['atids'], $result, $serv_attachment);
			// 跳转到成功界面
			$this->redirect('frontend', 'sign/upsuccess', array(
				'sl_id' => $result ['sl_id']
			));
		}
		$uptime = rgmdate(time(), 'Y-m-d H:i');
		$this->view->set('uptime', $uptime);
		$this->_output('mobile/sign/out');

		return;
	}


	/**
	 * [upload_fj  上传附件]
	 * @return [type] [description]
	 */
	public function upload_fj($atids, $result, $serv_attachment) {
		$data_at = array();
		if (!empty ($atids)) {
			$atids = explode(',', $atids);
			// 构造插入数据
			$data_at = array();
			foreach ($atids as $ids) {
				$data_at [] = array(
					'outid' => $result ['sl_id'],
					'atid' => $ids
				);
			}
			$serv_attachment->insert_multi($data_at);
		}
	}

	/**
	 * [get_lon_lat 初始化经纬度]
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_lon_lat($location) {
		if (!empty ($location)) {
			$location = explode(',', $location);
			$g_longitude = $location [0];
			$g_latitude = $location [1];
		} else {
			$g_longitude = '0';
			$g_latitude = '0';
		}
		$re_data['g_longitude'] = $g_longitude;
		$re_data['g_latitude'] = $g_latitude;

		return $re_data;
	}

	/**生成待插入的数据*/
	public function insert_data($g_longitude, $g_latitude, $address) {
		return array(
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => $this->_user ['m_username'],
			'sl_signtime' => startup_env::get('timestamp'),
			'sl_ip' => controller_request::get_instance()->get_client_ip(),
			'sl_longitude' => $g_longitude,
			'sl_latitude' => $g_latitude,
			'sl_address' => $address
		);
	}

}
