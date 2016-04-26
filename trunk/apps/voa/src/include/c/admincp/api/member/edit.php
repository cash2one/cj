<?php
/**
 * api编辑用户
 * voa_c_admincp_api_member_edit
 * User: luckwang
 * Date: 15/4/2
 * Time: 上午11:40
 */

class voa_c_admincp_api_member_edit extends voa_c_admincp_api_member_base {

    public function execute() {
        $this->__edit();
    }

    private function __edit() {
        $post = $this->request->postx();

        $submit['m_uid'] = isset($post['id']) ? $post['id'] : '';
        $submit['m_weixin'] = isset($post['weixin']) ? trim($post['weixin']) : '';
        $submit['m_mobilephone'] = isset($post['mobilephone']) ? trim($post['mobilephone']) : '';
        $submit['m_email'] = isset($post['email']) ? trim($post['email']) : '';
        $submit['m_username'] = isset($post['username']) ? trim($post['username']) : '';
        $submit['m_openid'] = isset($post['openid']) ? trim($post['openid']) : '';

        //电话/邮箱/微信不能同时为空
        if (empty($submit['m_mobilephone']) &&
            empty($submit['m_email']) &&
            empty($submit['m_weixin'])) {

            $this->_output_result(array('errcode' => 1, 'errmsg' => '微信号手机号邮箱不能同时为空'));
        }

        // 验证 openid
        if (!empty($submit['m_openid']) && !preg_match('/^[a-z0-9_]+$/i', $submit['m_openid'])) {
        	$this->_output_result(array('errcode' => 1, 'errmsg' => '账号格式错误'));
        	return true;
        }

        //姓名不能为空
        if (empty($submit['m_username'])) {
            $this->_output_result(array('errcode' => 3, 'errmsg' => '用户姓名不能为空'));
        }

        $submit['cj_name'] = isset($post['job']) ? trim($post['job']) : '';
        $submit['m_displayorder'] = isset($post['displayorder']) ? trim($post['displayorder']) : '';

        //性别
        $submit['m_gender'] = voa_d_oa_member::GENDER_UNKNOWN;
        if (isset($post['gender'])) {
            $submit['m_gender'] = $post['gender'];
        }

        //部门id
        if (isset($post['cd_ids'])) {
            $submit['cd_id'] = $post['cd_ids'];
        }

        //职位id
        $mp_ids = array();
        if (isset($post['mp_ids']) && is_array($post['mp_ids'])) {
            $mp_ids = $post['mp_ids'];
            $ps = voa_h_cache::get_instance()->get('plugin.member.positions', 'oa');
            foreach ($mp_ids as $mp_id) {
                if (!isset($ps[$mp_id])) {
                    $this->_output_result(array('errcode' => 4, 'errmsg' => '没有找到对应的职务'));
                }
            }

        }

        //判断部门是否为空
        if (empty($submit['cd_id'])) {
            $this->_output_result(array('errcode' => 2, 'errmsg' => '没有找到对应的部门'));
        }

        $set_fields = empty($this->_settings['fields']) ? array() : $this->_settings['fields'];
        foreach ($set_fields as $k => $field) {
            if(isset($post['fields'][$k])) {
                if ($field['status'] == 2) {
                    $submit['mf_' . $k] = trim($post['fields'][$k]);
                } elseif ($field['status'] == 1) {
                    $submit['mf_ext' . $k] = trim($post['fields'][$k]);
                }
            }
        }

        $uda_member_update = &uda::factory('voa_uda_frontend_member_update');
        $member = array();
        try{
            $uda_member_update->update($submit, $member, $mp_ids);
            // 更新部门人数
            $uda_member_update->update_department_usernum();
        } catch (Exception $e) {
            logger::error($e);
        }

        $this->_output_result(array('errcode' => $uda_member_update->errcode, 'errmsg' => $uda_member_update->errmsg));
    }
}
