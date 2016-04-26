<?php
/**
 * voa_server_wxqy_event
 * 微信事件消息处理
 *
 * $Author$
 * $Id$
 */


class voa_server_wxqy_event {
	protected $_wxserv;

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {

		$this->_wxserv = voa_wxqy_service::instance();
	}

	// 关注事件
	public function subscribe($args) {

		// 注册
		$openid = (string)$args['openid'];

		//判断用户是否为首次关注
		$is_first = true;

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));

		$member = $servm->fetch_by_openid($openid);

		if ($args['user']['m_qywxstatus'] == voa_d_oa_member::WX_STATUS_FOLLOWED) {
			$is_first = false;
		}

		$mem = isset($args['user']) ? (array)$args['user'] : array();

		// 已关注
		$uda = uda::factory('voa_uda_frontend_member_update');
		if (!$uda->update_avatar($openid, $mem)) {
			//return false;
		}

		// 发送消息
		$serv_qy = voa_wxqy_service::instance();


		//首次关注时推送消息
		if ($is_first === true) {

			/**$scheme = config::get('voa.oa_http_scheme');
			$setting = voa_h_cache::get_instance()->get('setting', 'oa');
			$title = config::get(startup_env::get('cfg_name').'.mailcloud.wxqy_follow_push_msg') . $scheme . $setting['domain'] . '/pc';
			$serv_qy->post_text($title, 0, $mem['m_openid'], '');*/
			//$serv_qy->response_text($title);
		}

		// 关注的应用 agent id
		$agentid = $this->_wxserv->msg['agent_id'];
		// 如果应用ID不存在
		if (0 >= $agentid) {
			return true;
		}

		$serv_p = &service::factory('voa_s_oa_common_plugin');
		$list = $serv_p->fetch_all();
		foreach ($list as $_p) {
			$cp_agentid = (int)$_p['cp_agentid'];
			if (voa_d_oa_common_plugin::AVAILABLE_OPEN != $_p['cp_available']
					|| 0 >= $cp_agentid || $agentid != $cp_agentid) {
				continue;
			}

			if ('exam' == $_p['cp_identifier']) {
				$data = array(
					'title' => '【' . $_p['cp_name'] . '】微信端操作指南',
					'description' => '移动端的考试应用, 适用于企业或团队内部检验培训成果, 考核员工或团队成员技能知识, 提升员工的业务水平.',
					'url' => 'http://www.vchangyi.com/bbs/thread-449-1-1.html',
					'picurl' => 'http://st.vchangyi.com/plugins/exam/news_cover.jpg'
				);
				$serv_qy->post_news($data, $cp_agentid, $mem['m_openid'], '');
			} elseif (in_array($_p['cp_identifier'], array('jobtrain', 'train'))) {
				$data = array(
					'title' => '【' . $_p['cp_name'] . '】微信端操作指南',
					'description' => '移动互联网时代的企业移动知识库，有效的对企业培训知识进行管理、支持员工随时随地学习与提升.',
					'url' => 'http://www.vchangyi.com/bbs/thread-451-1-1.html',
					'picurl' => 'http://st.vchangyi.com/plugins/jobtrain/news_cover.jpg'
				);
				$serv_qy->post_news($data, $cp_agentid, $mem['m_openid'], '');
			} elseif ('sign' == $_p['cp_identifier']) {
				$data = array(
					'title' => '【' . $_p['cp_name'] . '】微信端操作指南',
					'description' => '移动互联网时代的打卡利器，支持多部门分班次、多地点考勤设置，员工在微信端即可签到。',
					'url' => 'http://www.vchangyi.com/bbs/forum.php?mod=viewthread&tid=384&page=1&extra=#pid919',
					'picurl' => 'http://st.vchangyi.com/plugins/sign/news_cover.jpg'
				);
				$serv_qy->post_news($data, $cp_agentid, $mem['m_openid'], '');
			} elseif ('dailyreport' == $_p['cp_identifier']) {
				$data = array(
					'title' => '【' . $_p['cp_name'] . '】微信端操作指南',
					'description' => '告别邮件，随时随地通过企业号汇报工作成果。支持模板创建，微信端填写报告即可提交。',
					'url' => 'http://www.vchangyi.com/bbs/thread-373-1-2.html',
					'picurl' => 'http://st.vchangyi.com/plugins/dailyreport/news_cover.jpg'
				);
				$serv_qy->post_news($data, $cp_agentid, $mem['m_openid'], '');
			} elseif ('questionnaire' == $_p['cp_identifier']) {
				$data = array(
					'title' => '【' . $_p['cp_name'] . '】微信端操作指南',
					'description' => '基于微信的自定义表单应用，可进行问卷设计、投票发布、数据统计，实现内部数据采集、客户调研、满意度评价等多种场景。支持图片投票、单选、多选及开放题型；灵活设置问卷发送范围、问卷发布时间、是否匿名；还可对未读人员进行填写，对外分享问卷，轻松完成在线调研与投票。',
					'url' => 'http://www.vchangyi.com/bbs/thread-465-1-1.html',
					'picurl' => 'http://st.vchangyi.com/plugins/questionnaire/news_cover.jpg'
				);
				$serv_qy->post_news($data, $cp_agentid, $mem['m_openid'], '');
			} else {
				$title = '欢迎使用' . $_p['cp_name'];
				$serv_qy->post_text($title, $cp_agentid, $mem['m_openid'], '');
			}

            if ('blessingredpack' == $_p['cp_identifier']) {
                $this->_send_redpack_msg($member);
            }
		}


		return true;
	}

    /**
     * 推送扫码关注红包消息
     * @param $member
     */
    private function _send_redpack_msg($member) {

        if (empty($member)) {
            return true;
        }

        //如果来源是扫码关注的红包活动用户
        if($member['m_source'] != voa_d_oa_member::QRCODE_RESOURCE) {
            return true;
        }

        logger::error('扫码关注用户，开始推送红包消息');

        //获取活动红包ID
        $redpack_member_sev = &service::factory('voa_s_oa_blessingredpack_blessingmember');
        $conds = array(
            'm_uid' => $member['m_uid']
        );
        $redpack_member = $redpack_member_sev->get_by_conds($conds);

        //获取活动红包信息
        $redpack_sev = &service::factory('voa_s_oa_blessingredpack_blessingredpack');
        $redpack = $redpack_sev->get($redpack_member['redpack_id']);

        //缓存setting
        $setting = voa_h_cache::get_instance()->get('setting', 'oa');
        $blessing_setting = voa_h_cache::get_instance()->get('plugin.blessingredpack.setting', 'oa');

        //封装推送内容
        $msg_data =  array(
            'title' => $redpack['actname'],//主题
            'description' => $redpack['invite_content'],//邀请语
            'url' => 'http://'. $setting['domain'] . $blessing_setting['redpack_url'] . '?id=' . $redpack['id'] . '&title=' . $redpack['actname'],//抢红包url
            'picurl' => ''
        );

        //发送微信消息
        $serv_qy = voa_wxqy_service::instance();
        $serv_qy->post_news($msg_data, $blessing_setting['agentid'], $member['m_openid'], '');

        logger::error('推送红包消息成功');
    }

	//取消关注事件
	public function unsubscribe($args) {

		$openid = (string)$args['openid'];
		if (empty($openid)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$mem = $servm->fetch_by_openid($openid);

		if (!empty($mem)) {
			//更新用户为未关注
			$data['m_qywxstatus'] = voa_d_oa_member::WX_STATUS_UNFOLLOW;
			$servm->update($data, $mem['m_uid']);
		}
	}

	/** 地理位置事件 */
	public function location($args) {
		$user = $args['user'];
		/** 返回位置的消息模板 */

		/** 地理位置信息入库 */
		$serv = &service::factory('voa_s_oa_weixin_location', array('pluginid' => 0));
		$serv->insert(array(
			'm_uid' => $user['m_uid'],
			'm_username' => $user['m_username'],
			'wl_latitude' => $this->_wxserv->msg['latitude'],
			'wl_longitude' => $this->_wxserv->msg['longitude'],
			'wl_precision' => $this->_wxserv->msg['precision'],
			'wl_ip' => controller_request::get_instance()->get_client_ip()
		));

		return true;
	}

	/** 菜单点击事件 */
	public function click($args) {
		/** 读取插件信息 */
		$serv = &service::factory('voa_s_oa_common_plugin', array('pluginid' => 0));
		$plugin = $serv->fetch_by_identifier($this->_wxserv->msg['event_key']);
		/** 如果插件不存在, 则 */
		if (empty($plugin)) {
			logger::error("plugin is not exists.");
			$this->_wxserv->response_text('无效的关键字:'.$this->_wxserv->msg['event_key'], true);
			return false;
		}

		/** 返回该菜单的链接 */
		$url = $this->_wxserv->oauth_url_base(voa_h_func::get_agent_url('/'.$plugin['cp_url'], $plugin['cp_pluginid']));
		$this->_wxserv->response_text("<a href='{$url}'>".$plugin['cp_name']."</a>", true);
		return true;
	}
}
