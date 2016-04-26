<?php
/**
 * 发送邮件
 * @uses php tool.php -n mailcloud
 * User: luckwang
 * Date: 4/8/15
 * Time: 18:32
 */

class voa_backend_tool_mailcloud extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {


		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);
		$db->query('use vchangyi_cyadmin');
		$q = $db->query('select * from cy_enterprise_profile where ep_id>10067 and ep_id<10167');
		$mails = array();
		$pc_urls = array();
		$scheme = config::get('voa.oa_http_scheme');
		while ($ep = $db->fetch_array($q)) {
			if (empty($ep['ep_email'])) {
				continue;
			}

			$mails[] = $ep['ep_email'];
			$pc_urls[] = $scheme.$ep['ep_domain'].'/pc';
		}



		/*$mails = array(
			'wangfuxing@vchangyi.com',
			'i@fuxing.me'
		);
		$pc_urls = array(
			'http://bac.vchangyi.com/pc',
			'http://fsdbac.vchangyi.com/pc'
		);*/

		$subject = '畅移【同事聊天】上线通知';
		$vars = array(
			'%pc_url%' => $pc_urls,
		);
		$from = '';
		$fromname = '';
		$tpl = 'release_pc_notice';
		//$tpl = 'invite_follow';
		$result = mailcloud::get_instance()->send_tpl_mail($tpl, $mails, $subject, $vars, $from, $fromname);
		$data = array();
		foreach ($mails as $m) {
			$data[] = array(
				'mc_tplname' => $tpl,
				'mc_email' => $m,
				'mc_subject' => $subject,
				'mc_vars' => serialize($vars),
				'mc_repeat' => 0,
				'mc_status' => $result ? voa_d_uc_mailcloud::STATUS_SENDED : voa_d_uc_mailcloud::STATUS_FAILED
			);
		}

		$serv_mc = &service::factory('voa_s_uc_mailcloud', array('pluginid' => startup_env::get('pluginid')));
		$serv_mc->insert_multi($data);

	}

}
