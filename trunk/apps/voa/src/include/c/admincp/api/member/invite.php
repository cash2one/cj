<?php
/**
 * api邀请用户关注
 * voa_c_admincp_api_member_invite
 * User: luckwang
 * Date: 15/4/2
 * Time: 上午11:37
 */

class voa_c_admincp_api_member_invite extends voa_c_admincp_api_member_base {

    public function execute() {
        $ids = $this->request->get('id');

        if (empty($ids)) {
            $this->_output_result(array('errcode' => -20, 'errmsg' => '请选择需要邀请的用户'));
            return;
        }
        $ids = explode(',', $ids);
        //获取所有用户信息
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids($ids);

        $qywx_ab = voa_wxqy_addressbook::instance();
        $result = array();

        // 发送邀请邮件，不返回发送失败错误提示，因为不影响前台显示
        $uda_mailcloud = &uda::factory('voa_uda_uc_mailcloud_insert');
        $subject = $this->_setting['sitename'] . config::get('voa.mailcloud.subject_for_follow');
        $scheme = config::get('voa.oa_http_scheme');

        $vars = array(
            '%sitename%' => array($this->_setting['sitename']),
            '%qrcode_url%' => array('<img src="' . $this->_setting['qrcode'] . '" />'),
            '%pc_url%' => array($scheme . $this->_setting['domain'] . '/pc'),
            '%download_url%' => array('<a href="' . $scheme . $this->_setting['domain'] . '/frontend/index/download">点击下载</a>')
        );
        foreach ($users as $user) {
            if ($user['m_qywxstatus'] == voa_d_oa_member::WX_STATUS_UNFOLLOW) {
                if (!empty($user['m_email'])) {
                    $uda_mailcloud->send_invite_follow_mail(array($user['m_email']), $subject, $vars);
                } else {
                    //遍历用户根据open_id发送邀请
                    $qywx_ab->user_invite($user['m_openid'], '', $result);
                }
            }
        }

        //if (empty($result)) {
        //    $result = array('errcode' => -21, 'errmsg' => '用户已关注或已冻结');
        //}

        $result = array('errcode' => 1, 'errmsg' => '发送邀请成功');
        //输出结果
        $this->_output_result($result);
    }
}
