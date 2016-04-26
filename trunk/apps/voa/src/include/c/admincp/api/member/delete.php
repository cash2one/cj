<?php
/**
 * api删除用户
 * voa_c_admincp_api_member_delete
 * User: luckwang
 * Date: 15/4/2
 * Time: 上午11:38
 */

class voa_c_admincp_api_member_delete extends voa_c_admincp_api_member_base {

    public function execute() {
        $m_uid = $this->request->get('id');
        $m_uids = explode(',', $m_uid);
        $uids = array();
        foreach ($m_uids as $uid) {
            $uid  = rintval($uid);
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }
        if (empty($uids)) {
            $result = array('errcode' => -11, 'errmsg' => '请指定要删除的用户');
        } else {
            $result = $this->__delete($uids);
        }

        // 更新部门人数
        $uda = &uda::factory('voa_uda_frontend_member_update');
        $uda->update_department_usernum();
        $this->_output_result($result);
    }

    /**
     * 删除用户
     * @param $m_uid
     * @return mixed
     */
    private function __delete($m_uid) {

        $uda_member_delete = &uda::factory('voa_uda_frontend_member_delete');

        if (is_array($m_uid)) {
            foreach ($m_uid as $uid) {
                if(!$result = $uda_member_delete->delete($uid, true)) {
                    break;
                }
            }
        }

        if ($result) {
            return array('errcode' => 0, 'errmsg' => '删除成功');
        } else {
            return array('errcode' => $uda_member_delete->errcode, 'errmsg' => $uda_member_delete->errmsg);
        }

        return $result;
    }
}
