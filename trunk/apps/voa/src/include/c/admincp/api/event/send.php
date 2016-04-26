<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2015/12/28 0028
 * Time: 17:59
 * Email：muzhitao@vchangyi.com
 */
class voa_c_admincp_api_event_send extends voa_c_admincp_api_event_base {

	public function execute() {

		$uids = $this->request->post('uids');
		$acid = $this->request->post('acid');


		$serv = &service::factory('voa_s_oa_event');
		$detail = $serv->get($acid);

		$settings = voa_h_cache::get_instance()->get('setting', 'oa');

		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		if(isset($plugins[$this->_p_sets['pluginid']]['cp_agentid'])){
			startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		}
		// 发送微信消息e
		$msg_title = "[活动]".$detail['title'];
		$scheme = config::get('voa.oa_http_scheme');
		$msg_desc = "活动时间:".rgmdate($detail['start_time'], 'Y-m-d')."\n";
		$msg_desc .= "活动地点：" .$detail['province'].$detail['city'].$detail['area'].$detail['street'];
		$msg_url = $scheme . $settings['domain'] . '/previewh5/micro-community/index.html#/app/page/activity/activity-detail?id=' . $acid;
		$msg_picurl = voa_h_attach::attachment_url($detail['at_ids']);
		// 发消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $uids, '', $msg_picurl, 0, 0, -1);

		// 返回结果阿斯达斯柯达昊
		$result = array(

		);

		return $this->_output_result($result);
	}

}
