<?php
/**
 * 模板消息发送类
 * $Author$
 * $Id$
 */

class voa_weixin_wxmsg {
	/** 消息模板对应关系 */
	protected $_tplids;
	/** 站点前台域名 */
	protected $_domain;

	static function &instance() {
		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	public function __construct() {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_tplids = $sets['wxtplids'];
		$this->_domain = $sets['domain'];
	}

	/**
	 * 发送消息
	 * @param string $openid openid
	 * @param string $tplid 模板id
	 * @param array $data 模板数据
	 */
	public function send($openid, $tplid, $data) {
		return voa_wxlife_service::instance()->send_msg($openid, $tplid, $data);
	}

	/** 发送自定义模板消息 */
	public function diy($openid, $message) {
		return $this->add_queue($openid, $this ->_tplids['default'], array(
			'message' => $message
		));
	}

	/**
	 * 待发送模板消息加入队列
	 * @param string $openid openid
	 * @param string $tplid 模板id
	 * @param array $data 模板数据
	 */
	public function add_queue($openid, $tplid, $data) {
		$serv = &service::factory('voa_s_oa_msg_queue', array('pluginid' => 0));
		$serv->insert(array(
			'mq_cardid' => config::get('voa.wxlife.cardid'),
			'mq_skey' => config::get('voa.wxlife.skey'),
			'mq_openid' => $openid,
			'mq_tplid' => $tplid,
			'mq_message' => serialize($data)
		));
	}

	/**
	 * 发模板消息给项目人员
	 * @param array $proj 项目信息
	 * @param string $message 具体任务
	 * @param array $users 用户列表
	 */
	public function project_new($proj, $message, $users) {
		/** 发送消息给所有项目人员 */
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/project/view/'.$proj['p_id']);
		foreach ($users as $m) {
			$this->add_queue($m['m_openid'], $this ->_tplids['default'], array(
				'message' => "新项目\n名称:{$proj['p_subject']}\n {$message}\n <a href=\"{$url}\">由[{$proj['m_username']}]发起了一个新项目, 查看详情请点击</a>"
			));
		}
	}

	public function project_carboncopy($proj, $message, $users) {
		/** 发送消息给所有项目人员 */
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/project/view/'.$proj['p_id']);
		foreach ($users as $m) {
			$this->add_queue($m['m_openid'], $this ->_tplids['default'], array(
				'message' => "项目\n名称:{$proj['p_subject']}\n {$message}\n <a href=\"{$url}\">由[{$proj['m_username']}]发起了一个新项目, 查看详情请点击</a>"
			));
		}
	}

	public function project_advanced($proj, $message, $users) {
		/** 发送消息给所有项目人员 */
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/project/view/'.$proj['p_id']);
		foreach ($users as $m) {
			$this->add_queue($m['m_openid'], $this ->_tplids['default'], array(
				'message' => "项目推进\n名称:{$proj['p_subject']}\n {$message}\n <a href=\"{$url}\">由[{$proj['m_username']}]发起了一个新项目, 查看详情请点击</a>"
			));
		}
	}

	/**
	 * 发布微信墙的模板消息
	 */
	public function wxwall_new($wxwall) {
		$msg = array(
			"发布成功",
			"管理地址:http://".$this->_domain."/".config::get('voa.wxwall_path'),
			"管理用户:{$wxwall['ww_admin']}",
			"管理密码:{$wxwall['_passwd']}"
		);
		$user = voa_h_user::get($wxwall['m_uid']);
		$this->add_queue($user['m_openid'], $this->_tplids['default'], array(
			'message' => implode("\n", $msg)
		));
	}

	/** 编辑微信墙信息 */
	public function wxwall_edit($wxwall) {
		$msg = array(
			"编辑成功",
			"管理地址:http://".$this->_domain."/".config::get('voa.wxwall_path')
		);
		if (!empty($wxwall['_passwd'])) {
			$msg[] = "管理用户:{$wxwall['ww_admin']}\n管理密码:{$wxwall['_passwd']}";
		}

		$user = voa_h_user::get($wxwall['m_uid']);
		$this->add_queue($user['m_openid'], $this->_tplids['default'], array(
			'message' => implode("\n", $msg)
		));
	}

	/**
	 * 注册时的模板消息
	 * @param array $user 用户信息
	 */
	public function register_succeed($user) {
		$msg = "注册成功\n[ {$user['m_username']} ]欢迎注册, 已经审核通过";
		/**if (voa_d_oa_member::STATUS_VERIFY == $user['m_status']) {
			$msg .= ", 请耐心等待审核";
		} else {
			$msg .= ", 已经审核通过";
		}*/

		$this->add_queue($user['m_openid'], $this->_tplids['default'], array('message' => $msg));
	}

	/** 发送注册链接的模板消息 */
	public function register_link($openid) {
		$url = voa_weixin_service::instance()->oauth_url_base('http://'.$this->_domain.'/register');
		$this->add_queue($openid, $this->_tplids['reg'], array(
			'url' => $url
		));
	}

	/**
	 * 发送会议取消的模板消息
	 * @param array $meeting 会议信息
	 */
	public function meeting_cancel($meeting) {
		/** 读取所有参会人 */
		$serv_mm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => startup_env::get('pluginid')));
		$mt_mems = $serv_mm->fetch_by_mt_id($meeting['mt_id']);
		$uids = array();
		foreach ($mt_mems as $v) {
			$uids[$v['m_uid']] = $v['m_uid'];
		}

		/** 读取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$mems = $serv_m->fetch_all_by_ids($uids);

		/** 发送消息给所有参会人 */
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/meeting/view/'.$meeting['mt_id']);
		foreach ($mems as $m) {
			if ($m['m_uid'] == startup_env::get('wbs_uid')) {
				continue;
			}

			$this->add_queue($m['m_openid'], $this ->_tplids['default'], array(
				'message' => "会议取消\n主题:{$meeting['mt_subject']}\n <a href=\"{$url}\">由[{$meeting['m_username']}]发起的会议已取消, 查看详情请点击</a>"
			));
		}
	}

	/**
	 * 新会议创建时, 给所有参会人发送模板消息
	 * @param array $meeting 会议信息
	 * @param array $users 用户列表
	 */
	public function meeting_new($meeting, $users) {
		$rooms = voa_h_cache::get_instance()->get('plugin.meeting.room', 'oa');
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/meeting/view/'.$meeting['mt_id']);
		foreach ($users as $k => $v) {
			if ($v['m_uid'] == startup_env::get('wbs_uid')) {
				continue;
			}

			$this->add_queue($v['m_openid'], $this->_tplids['meeting']['new'], array(
				'url' => $url,
				'sponsor' => startup_env::get('wbs_username'),
				'subject' => $meeting['mt_subject'],
				'ymdhi' => rgmdate($meeting['mt_begintime'], 'Y-m-d H:i'),
				'roomname' => $rooms[$meeting['mr_id']]['mr_name']
			));
		}
	}

	public function thread_newthread($thread, $members) {
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/thread/viewthread/'.$thread['t_id']);
		foreach ($members as $m) {
			if ($m['m_uid'] == startup_env::get('wbs_uid')) {
				continue;
			}

			$this->add_queue($m['m_openid'], $this->_tplids['thread']['share'], array(
				'url' => $url,
				'sponsor' => startup_env::get('wbs_username'),
				'subject' => $thread['t_subject']
			));
		}
	}

	/**
	 * 回复工作台信息时, 发送模板消息给被回复人
	 * @param array $thread 工作台主题
	 * @param int $uid 用户 uid
	 */
	public function thread_reply($thread, $uid) {
		/** 取用户信息 */
		$u = voa_h_user::get($uid);
		/** 发送微信模板消息 */
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/thread/view/'.$thread['t_id']);
		$this->add_queue($u['m_openid'], $this->_tplids['thread']['reply'], array(
			'url' => $url,
			'replyer' => startup_env::get('wbs_username'),
			'subject' => $thread['t_subject']
		));
	}

	/**
	 * 对工作台信息评论时, 发送模板消息给当前记录所属的用户
	 * @param array $thread 工作台主题
	 */
	public function thread_comment($thread) {
		$this->thread_reply($thread, $thread['m_uid']);
	}

	/**
	 * 同意审批
	 * @param array $askfor 审批信息
	 */
	public function askfor_approve($askfor) {
		/** 用户 */
		$u = voa_h_user::get($askfor['m_uid']);
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/askfor/view/'.$askfor['af_id']);
		$this->add_queue($u['m_openid'], $this->_tplids['askfor']['approve'], array(
			'url' => $url,
			'applyer' => $askfor['m_username'],
			'subject' => $askfor['af_subject'],
			'approver' => startup_env::get('wbs_username')
		));
	}

	/**
	 * 拒绝审批
	 * @param array $askfor 审批信息
	 */
	public function askfor_refuse($askfor) {
		/** 用户 */
		$u = voa_h_user::get($askfor['m_uid']);
		$wxserv = voa_weixin_service::instance();
		$viewurl = $wxserv->oauth_url_base('http://'.$this->_domain.'/askfor/view/'.$askfor['af_id']);
		$this->add_queue($u['m_openid'], $this->_tplids['askfor']['refuse'], array(
			'url' => $viewurl,
			'applyer' => $askfor['m_username'],
			'subject' => $askfor['af_subject'],
			'approver' => startup_env::get('wbs_username')
		));
	}

	/**
	 * 发送转审批的模板消息
	 * @param array $askfor 审批信息
	 * @param array $mem 目标审批人信息
	 */
	public function askfor_transmit($askfor, $mem) {
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/askfor/view/'.$askfor['af_id']);
		$this->add_queue($mem['m_openid'], $this->_tplids['askfor']['transmit'], array(
			'url' => $url,
			'applyer' => $askfor['m_username'],
			'subject' => $askfor['af_subject'],
			'approver' => startup_env::get('wbs_username')
		));
	}

	/**
	 * 给审批人发送模板消息
	 * @param array $askfor 审批信息
	 * @param int $approveuid 审批人uid
	 * @param array $user 用户信息
	 */
	public function askfor_new($askfor, $user) {
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/askfor/view/'.$askfor['af_id']);

		$this->add_queue($user['m_openid'], $this->_tplids['askfor']['new'], array(
			'url' => $url,
			'applyer' => startup_env::get('wbs_username'),
			'subject' => $askfor['af_subject']
		));
	}

	/**
	 * 给审批抄送人发送模板消息
	 * @param array $askfor 审批信息
	 * @param array $users 用户列表
	 */
	public function askfor_carboncopy($askfor, $users) {
		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/askfor/view/'.$askfor['af_id']);

		foreach ($users as $k => $v) {
			if ($v['m_uid'] == startup_env::get('wbs_uid')) {
				continue;
			}

			$this->add_queue($v['m_openid'], $this->_tplids['askfor']['carboncopy'], array(
				'url' => $url,
				'applyer' => startup_env::get('wbs_username'),
				'subject' => $askfor['af_subject']
			));
		}
	}

	/**
	 * 给报告接收人发送模版消息
	 * @param array $dailyreport 报告信息
	 * @param array $user 用户信息
	 */
	public function dailyreport_new($dailyreport, $user) {
		return;
	}

	/**
	 * 给报告抄送人发送模版信息
	 * @param array $dailyreport 报告信息
	 * @param array $users 用户列表
	 */
	public function dailyreport_carboncopy($dailyreport, $users) {
		return;
	}

	/**
	 * 给目标人发送模板消息
	 * @param array $minutes 会议纪要信息
	 * @param array $user 用户信息
	 */
	public function minutes_new($minutes, $user) {
		return;
	}

	/**
	 * 给抄送人发送模板消息
	 * @param array $minutes 会议纪要信息
	 * @param array $users 用户列表
	 */
	public function minutes_carboncopy($minutes, $users) {
		return;
	}

	/**
	 * 公告信息的模板消息
	 * @param array $notice 公告信息
	 * @param array $users 接收通知的用户数据列表
	 */
	public function notice_new($notice, $users) {

		$wxserv = voa_weixin_service::instance();
		$url = $wxserv->oauth_url_base('http://'.$this->_domain.'/notice/view/'.$notice['nt_id']);

		$message = array();
		$message[] = $notice['nt_subject'];
		if ($notice['nt_author']) {
			$message[] = '发送人：'.$notice['nt_author'];
		}
		$message[] = '发送时间：'.$notice['_created'];
		$message[] = '';
		$message[] = '<a href="'.$url.'">点击查看详情</a>';

		$tpl_message = implode("\n", $message);

		foreach ($users as $k => $v) {
			$this->add_queue($v['m_openid'], $this ->_tplids['default'], array(
					'message' => $tpl_message
			));
		}
	}

	/**
	 * 给自己发送定时提醒的模板消息
	 * @param array $remind 定时提醒消息
	 * @param array $user 用户信息
	 */
	public function remind_new($remind, $user) {
		return;
	}
}
