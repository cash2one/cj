<?php
/**
 * 设置员工在职状态
 * User: luckwang
 * Date: 15/5/7
 * Time: 下午8:22
 */

class voa_c_admincp_api_member_active extends voa_c_admincp_api_member_base {

    protected $_active_status = array(
        voa_d_oa_member::ACTIVE_YES,
        voa_d_oa_member::ACTIVE_NO
    );

    public function execute() {

        $id = $this->request->get('id');
        $active = $this->request->get('active');
        $id = rintval($id);
        $active = rintval($active);
        //用户id
        if (empty($id)) {
            $this->_output_result(array('errcode' => -20, 'errmsg' => '请选择需要设置的用户'));
            return;
        }
        //判断提交的状态是否正确
        if (!in_array($active, $this->_active_status)) {
            $this->_output_result(array('errcode' => -21, 'errmsg' => '请求错误'));
            return;
        }

        //获取所有用户信息
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $user = $servm->fetch($id);
        if (empty($user)) {

            $this->_output_result(array('errcode' => -20, 'errmsg' => '请选择需要设置的用户'));
        } else {

            //判断状态没有更改
            if ($active == $user['m_active']) {
                if ($active == voa_d_oa_member::ACTIVE_YES) {
                    $this->_output_result(array('errcode' => -22, 'errmsg' => '用户已是在职状态'));
                } else {
                    $this->_output_result(array('errcode' => -23, 'errmsg' => '用户已是离职状态'));
                }
            } else {
                //更新用户数据
                $submit['m_active'] = $active;
                $submit['m_uid'] = $id;
                $uda_member_update = &uda::factory('voa_uda_frontend_member_update');
                $member = array();
                try{
                    $uda_member_update->update($submit, $member);
                } catch (Exception $e) {
                    logger::error($e);
                }
                //输出更新结果
                $this->_output_result(array('errcode' => $uda_member_update->errcode, 'errmsg' => $uda_member_update->errmsg));
            }
        }
    }
}
