<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2015/12/28 0028
 * Time: 17:59
 * Email：muzhitao@vchangyi.com
 */
class voa_c_admincp_api_cnvote_send extends voa_c_admincp_api_cnvote_base {

	public function execute() {

		$uids = $this->request->post('uids');
		$acid = $this->request->post('acid');

		$serv = &service::factory('voa_s_oa_cnvote');
		$detail = $serv->get($acid);

		$settings = voa_h_cache::get_instance()->get('setting', 'oa');

		$msg_title = " [投票]".$detail['subject'];
		$msg_desc = " 发布日期:".rgmdate(time(), 'Y-m-d')."\n 发布人:".$detail['m_username'];
		$msg_url = voa_wxqy_service::instance()->oauth_url(
			config::get(startup_env::get('app_name').'.oa_http_scheme') .
			$this->_setting['domain'] .
			'/previewh5/micro-community/index.html?_ts=1451269716#/app/page/vote/vote-detail?id=' . $detail['id'] .
			'&pluginid=' . $this->_sets['pluginid']);
		$msg_picurl = voa_h_attach::attachment_url($detail['thumb']);
		// 发消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $uids, '', $msg_picurl, 0, 0, -1);

		// 返回结果阿斯达斯柯达昊
		$result = array(

		);

		return $this->_output_result($result);
	}

}
