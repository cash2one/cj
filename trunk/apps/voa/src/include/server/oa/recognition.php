<?php
/**
 * 企业 oa 名片识别内部接口
 * $Author$
 * $Id$
 */

class voa_server_oa_recognition {

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}
	}

	/**
	 * 更新发票识别结果
	 * @param array $args 识别结果数组
	 *  + rbb_expend 花费
	 *  + rbb_time 发票时间
	 * @see voa_uda_cyadmin_recognition_post
	 * @return multitype:NULL |multitype:number
	 */
	public function bill($args) {
		$req = controller_request::get_instance();
		/** 可用数据 */
		$param_keys = array(
			'rbb_expend', 'rbb_time'
		);
		$data = array();
		foreach ($param_keys as $v) {
			$data[$v] = (string)$args[$v];
		}

		/** 导入参数 */
		$req->set_params($data);

		/** 修改名片夹 */
		$namecard = array(
			'nc_id' => $data['nc_id'],
			'nc_status' => voa_d_oa_namecard::STATUS_UPDATE
		);
		$folders = array();
		$uda_up = &uda::factory('voa_uda_frontend_namecard_update');
		if (!$uda_up->namecard_update($namecard, $folders)) {
			return array('errcode' => $uda_up->errno, 'errmsg' => $uda_up->error);
		}

		return array('errcode' => 0);
	}

	/**
	 * 更新名片识别结果
	 * @param array $args 识别结果数组
	 *  + realname 真实姓名
	 *  + wxuser 微信号
	 *  + job 职位
	 *  + mobilephone 手机号码
	 *  + telephone 固话
	 *  + email 邮箱
	 *  + company 公司名称
	 *  + address 地址
	 *  + postcode 邮编
	 *  + qq QQ号码
	 *  + nc_id 名片id
	 *  @see voa_uda_cyadmin_recognition_post
	 */
	public function namecard($args) {

		$req = controller_request::get_instance();
		/** 可用数据 */
		$param_keys = array(
			'realname', 'wxuser', 'job', 'mobilephone', 'telephone', 'email', 'company',
			'address', 'postcode', 'qq', 'nc_id'
		);
		$data = array();
		foreach ($param_keys as $v) {
			$data[$v] = (string)$args[$v];
		}

		/** 导入参数 */
		$req->set_params($data);

		/** 修改名片夹 */
		$namecard = array(
			'nc_id' => $data['nc_id'],
			'nc_status' => voa_d_oa_namecard::STATUS_UPDATE
		);
		$folders = array();
		$uda_up = &uda::factory('voa_uda_frontend_namecard_update');
		if (!$uda_up->namecard_update($namecard, $folders)) {
			return array('errcode' => $uda_up->errno, 'errmsg' => $uda_up->error);
		}

		/** 获取站点配置 */
// 		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
// 		$nc_sets = voa_h_cache::get_instance()->get('plugin.namecard.setting', 'oa');

// 		/** 发送微信消息 */
// 		$viewurl = '';
// 		$this->get_view_url($viewurl, $askoff['ao_id']);
// 		voa_wxqy_service::instance()->oauth_url(config::get(startup_env::get('app_name') . '.oa_http_scheme').$sets['domain'].'/namecard/view/'.$data['nc_id'].'?pluginid='.startup_env::get('pluginid'));
// 		$content = "待 ".$mem['m_username']." 审核\n"
// 				 . "申请人：".$this->_user['m_username']."\n"
// 				 . " <a href='".$viewurl."'>点击查看详情</a>";

		return array('errcode' => 0);
	}
}
