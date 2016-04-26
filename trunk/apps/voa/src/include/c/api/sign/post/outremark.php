<?php
/**
 * @Author: ppker
 * @Date:   2015-09-09 14:46:17
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-15 14:20:57
 */
class voa_c_api_sign_post_outremark extends voa_c_api_sign_base {
	public function execute() {
		// 公共数据
		$post = $this->request->postx();
		$serv_location = &service::factory('voa_s_oa_sign_location');
		$serv_attachment = &service::factory('voa_s_oa_sign_attachment');
		if(isset($post['send_remark']) && $post['send_remark'] == 1) {
			$this->send_remark($post, $serv_location);
		}


		// 进行不同的数据判断流程
		if(isset($post['su_again_img']) && $post['su_again_img'] == 1) { // 成功后继续上传图片
			$this->su_again_img($post);
		}

	}

	/**
	 * [get_lon_lat 初始化经纬度]
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_lon_lat($location) {
		if(!empty($location)){
			list($g_longitude, $g_latitude) = explode(',', $location);
		}else{
			list($g_longitude, $g_latitude) = array(0, 0);
		}
		return array($g_longitude, $g_latitude);

	}

	/**
	 * [insert_data 生产所需的数据]
	 * @param  [type] $g_longitude [description]
	 * @param  [type] $g_latitude  [description]
	 * @param  [type] $address     [description]
	 * @return [type]              [description]
	 */
	public function insert_data($g_longitude, $g_latitude, $address) {

		return array(
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'sl_signtime' => startup_env::get('timestamp'),
			'sl_ip' => controller_request::get_instance()->get_client_ip(),
			'sl_longitude' => $g_longitude,
			'sl_latitude' => $g_latitude,
			'sl_address' => $address
		);
	}


	
	public function upload_fj($atids, $result, $serv_attachment) {
		$data_at = array();
		$re = null;
		if (!empty ($atids)) {
			$atids = explode(',', $atids);
			if(sizeof($atids) > 5) $this->_set_errcode("10109:图片上传超过5张！");
			// 构造插入数据
			foreach ($atids as $ids) {
				$data_at [] = array(
					'outid' => $result ['sl_id'],
					'atid' => $ids
				);
			}
			$re = $serv_attachment->insert_multi($data_at);
		}
		return $re;
	}


	/**
	 * [su_page 上传成功后进行的页面初始化]
	 * @param  [type] $post [description]
	 * @return [type]       [description]
	 */
	public function su_page($post) {
		if(empty($post['sl_id'])) {
			return $this->_set_errcode("10105:数据异常，缺少参数sl_id！");
		}
		
		$uda = &Service::factory('voa_uda_frontend_sign_out');
		
		
		$data = $uda->format($post);
		
		$end_data = $this->make_data($data);
		$this->_result = $end_data;
		return true;

	}


	/**
	 * [make_data 生成标准接口数据]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function make_data($data) {
		return array(
			'sl_address' => $data['sl_address'],
			'sl_signtime' => $data['sl_signtime'],
			'attachs' => $data['attachs'],
			'sl_id' => $data['sl_id']
			/*'last_pic' => $data['last_pic']*/
		);
	}

	/**
	 * [su_again_img 签到成功后继续上传图片接口部分]
	 * @param  [type] $psot [description]
	 * @return [type]       [description]
	 */
	public function su_again_img($post) {
		$serv_attachment = &service::factory('voa_s_oa_sign_attachment');
		if(empty($post['sl_id'])) {
			return $this->_set_errcode("10105:数据异常，缺少参数sl_id！");
		}
		$conds['outid'] = $post['sl_id'];
		$re_p = $serv_attachment->list_by_conds($conds);
		$count = 5 - sizeof($re_p);

		if(!empty($post['atids'])) {
			$this->up_pic($post['atids'], $serv_attachment, $post['sl_id'], $count);
		}
	}


	/**
	 * [up_pic 继续上传图片的接口]
	 * @param  [type] $up_atids        [description]
	 * @param  [type] $serv_attachment [description]
	 * @return [type]                  [description]
	 */
	public function up_pic($up_atids, $serv_attachment, $sl_id, $count){
		$atids = explode(',', $up_atids);

		if(sizeof($atids) > $count) return $this->_set_errcode("10205:最多共上传5张！");
		// 生成插入数据
		$up_at = array();
		foreach ($atids as $ids) {
			$up_at[] = array(
				'outid' => $sl_id,
				'atid' => $ids
			);
		}
		//插入操作
		$re_insert = $serv_attachment->insert_multi($up_at);
		if($re_insert != false){
			$this->_result = array('sl_id' => $sl_id);
			return true;
		}else{
			return $this->_set_errcode("10205:上传图片出现错误！");
		}

	}

	/**
	 * [send_remark  进行外出考勤签到]
	 * @param  [type] $post          [description]
	 * @param  [type] $serv_location [description]
	 * @return [type]                [description]
	 */
	public function send_remark($post, $serv_location) {
		$serv_attachment = &service::factory('voa_s_oa_sign_attachment');
		if (empty($post ['address'])) {
			return $this->_set_errcode("10107:数据异常，地理位置信息操作失败！");
		}
		// 初始化经纬度
		list($g_longitude, $g_latitude) = $this->get_lon_lat($post['location']);
		// 初始化插入数据
		$insert_data = $this->insert_data($g_longitude, $g_latitude, $post['address']);
		$result = $serv_location->insert($insert_data);
		if(empty($result)) {
			return $this->_set_errcode("10107:数据异常，地理位置信息操作失败！");
		}
		if(!empty($post['atids'])) {
			$re = $this->upload_fj($post['atids'], $result, $serv_attachment);
			if(empty($re)) return $this->_set_errcode("10108:数据异常，图片上传失败！");
		}

		$this->su_page($result);
		
	}


}
