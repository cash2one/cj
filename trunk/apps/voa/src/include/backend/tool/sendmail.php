<?php
/**
 * sendmail.php
 * 比对数据表
 * @uses php tool.php -n sendmail
 * $Author$
 * $Id$
 */
class voa_backend_tool_sendmail extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		return;
		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		// 发送注册成功邮件，不返回发送失败错误提示，因为不影响前台显示
		$uda = &uda::factory('voa_uda_uc_mailcloud_insert');

		exit;
		$db->query('use vchangyi_admincp');
		$q = $db->query('select * from cy_enterprise_profile where ep_id>10067 and ep_id<10167');
		//$q = $db->query('select * from cy_enterprise_profile where ep_id=10068');
		$mails = array();
		$domains = array();
		$names = array();
		$mobilephones = array();
		$emails = array();
		$admincpurls = array();
		$scheme = config::get('voa.oa_http_scheme');
		while ($ep = $db->fetch_array($q)) {
			if (empty($ep['ep_email'])) {
				continue;
			}

			$mails[] = $ep['ep_email'];
			$domains[] = $ep['ep_domain'];
			$names[] = $ep['ep_name'];
			$mobilephones[] = $ep['ep_mobilephone'];
			$emails[] = $ep['ep_email'];
			$admincpurls[] = $scheme.$ep['ep_domain'].'/admincp';
		}

		$subject = config::get('voa.mailcloud.subject_for_register');
		$vars = array(
			'%domain%' => $domains,
			'%sitename%' => $names,
			'%mobilephone%' => $mobilephones,
			'%email%' => $emails,
			'%admincpurl%' => $admincpurls
		);
		//print_r($mails);print_r($vars);
		$uda->send_reg_vchangyi_mail($mails, $subject, $vars, 'sys@vchangyi.com', '畅移云工作');

	}

}
