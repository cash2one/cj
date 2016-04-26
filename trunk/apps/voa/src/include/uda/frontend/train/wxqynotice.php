<?php
/**
 * wxqynotice.php
 * 发布企业微信通知（写入消息发送队列）
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_train_wxqynotice extends voa_uda_frontend_train_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 统一发送出口方法
	 * @return boolean
	 */
	public  function send_wxqy_notice($article,$session_obj) {

		$openids = array(); //人员与微信相关ID
		$qywxids = array(); //部门与微信相关ID
		$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));

		if (!isset($article['contacts']) && !isset($article['deps'])) {
			//如果没有人员及部门信息，则是全部人员都可查看，向所有部门人员发送消息
			$deps = $serv_d->fetch_all();
		}
		if (isset($article['contacts']) && !empty($article['contacts'])) {
			//如果是人员ID
			$users = $serv_m->fetch_all_by_ids($article['contacts']);
			if ($users) {
				foreach ($users as $user) {
					$openids[] = $user['m_openid'];
				}
			}
		}
		if (isset($article['deps']) && !empty($article['deps'])) {
			//如果是部门ID
			$deps = $serv_d->fetch_all_by_key($article['deps']);
		}
		if ($deps) {
			foreach ($deps as $dep) {
				$qywxids[] = $dep['cd_qywxid'];
			}
		}

		// 浏览详情的授权链接
		$view_url = '';
		$this->get_view_url($view_url, $article['ta_id']);
		startup_env::set('agentid', (int)$this->plugin_setting['agentid']);
		$msg_title = '您收到一篇新文章';
		$msg_desc = '标题：'.rhtmlspecialchars($article['title']);
		$msg_desc .= "\n更新时间: ".rgmdate($article['updated'], 'Y-m-d H:i');
		$msg_url = $view_url;
		$touser = implode('|', $openids);
		$toparty = implode('|', $qywxids);
		// 发送消息
		voa_h_qymsg::push_news_send_queue($session_obj, $msg_title, $msg_desc, $msg_url, $touser, $toparty);
		return true;
	}

	/**
	 * 获取文章的微信企业号授权链接
	 * @param string $url (引用结果)链接字符串
	 * @param number $woid 工单ID
	 * @return boolean
	 */
	public function get_view_url(&$url, $ta_id) {

		// 站点使用的传输协议，自全局配置读取
		$url = config::get(startup_env::get('app_name').'.oa_http_scheme');
		// 站点域名
		$url .= $this->setting['domain'].'/frontend/train/detail/?id='.$ta_id;

		return true;
	}

}
