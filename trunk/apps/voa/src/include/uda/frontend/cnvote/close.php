<?php
/**
 * voa_uda_frontend_cnvote_close
 * 关闭投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 下午4:07
 */

class voa_uda_frontend_cnvote_close extends voa_uda_frontend_cnvote_abstract {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param int $close_uid
     * @param int $nvote_id
     * @param bool $only_self 只能关闭自己添加的投票
     * @return bool
     */
    public function close($nvote_id, $session = null) {

        $nvote_id = rintval($nvote_id);

        if ($nvote_id < 1) {
            $this->errmsg('202', '请选择需要关闭的投票');
            return false;
        }

        $vote = $this->_serv->get($nvote_id);
        if (empty($vote)) {
            $this->errmsg('203', '投票不存在');
            return false;
        }

        if ($vote['is_stop'] == voa_d_oa_cnvote::CLOSE_STATUS_YES) {
            $this->errmsg('204', '不能重复关闭投票');
            return false;
        }

        $nvote['is_stop'] = voa_d_oa_cnvote::CLOSE_STATUS_YES;
        $nvote['end_time'] = startup_env::get('timestamp');

        $result = $this->_serv->update($nvote_id, $nvote);

        return $result;
    }

    /**
     * 后台关闭投票-推送消息通知发起人
     * @param array $nvote
     * @return void
     */
    public function push_close_msg($nvote, $session) {

        //demo体验号不发送提醒
        if (strpos(strtolower(controller_request::get_instance()->server('SERVER_NAME')), 'demo') !== false) {
            return ;
        }

        $viewurl = voa_wxqy_service::instance()->oauth_url(
                                            config::get(startup_env::get('app_name').'.oa_http_scheme') .
                                            $this->_setting['domain'] .
                                            '/frontend/cnvote/view?nv_id=' . $nvote['id'] .
                                            '&pluginid=' . $this->_sets['pluginid']);

        //获取用户openid
        $serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $serv_m->fetch_by_uid($nvote['submit_id']);
        $touser = $users['m_openid'];

        $toparty = '';
        $msg_title = '您的投票已被后台管理员关闭';
        $msg_desc = '主题：'.$nvote['subject'];
        $msg_url = $viewurl;
        $msg_picurl = '';
        // 发送消息
        voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl, (int)$this->_plugins[$this->_sets['pluginid']]['cp_agentid']);
    }
}
