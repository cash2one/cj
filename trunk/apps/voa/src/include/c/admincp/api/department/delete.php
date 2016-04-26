<?php
/**
 * api删除部门
 * voa_c_admincp_api_department_delete
 * User: luckwang
 * Date: 15/4/2
 * Time: 上午11:38
 */

class voa_c_admincp_api_department_delete extends voa_c_admincp_api_department_base {

    public function execute() {

        $cd_id = (int)$this->request->get('id');

        if ($cd_id < 1) {
            $result = array('errcode' => -11, 'errmsg' => '请指定要删除的部门');
        } else {
            $result = $this->__delete_check($cd_id);
        }
        $this->_output_result($result);

    }

    /**
     * 删除部门检查
     * @param $cd_id
     * @return array
     */
    private function __delete_check($cd_id) {

        $servd = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
        $servmd = &service::factory('voa_s_oa_member_department', array('pluginid' => 0));

        $count = 0;
        //查询部门
        $department = $this->_departments[$cd_id];

        if (empty($department)) {
            $result = array('errcode' => -12, 'errmsg' => '部门不存在');
        }
        elseif ($count = $servd->count_by_conditions(array('cd_upid' => $cd_id))) {
            if ($count > 0) {
                $result = array('errcode' => -13, 'errmsg' => '不能删除有子部门的部门');
            }
        }
        elseif ($count = $servmd->count_by_conditions(array('cd_id' => $cd_id))) {
            if ($count > 0) {
                $result = array('errcode' => -14, 'errmsg' => '不能删除有成员的部门');
            }
        }
        else {
            $result = $this->__delete_record($cd_id, $department['cd_qywxid']);
        }

        //回收资源
        unset($servd, $servmd, $department);

        return $result;
    }

    /**
     * 删除记录
     * @param $cd_id
     * @param $qywxid
     * @return array
     */
    private function __delete_record($cd_id, $qywxid) {
        $servd = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
        $wxqyab = &voa_wxqy_addressbook::instance();

        try {
            $servd->begin();
            //删除数据记录
            if ($servd->delete($cd_id)) {

                //删除微信里的部门
                $flag = $wxqyab->department_delete($qywxid, $result);
                //60003 代表微信里不存在
                if ($flag || $wxqyab->errcode == '60003') {

                    $result = array('errcode' => 1, 'errmsg' => '删除成功', 'cd_id' => $cd_id);
                    voa_h_cache::get_instance()->get('department', 'oa', true);
                    $servd->commit();
                } else {

                    $result = array('errcode' => -19, 'errmsg' => '删除失败');
                    $servd->rollback();
                }

            } else {
                $result = array('errcode' => -19, 'errmsg' => '删除失败');
                $servd->rollback();
            }
        }
        catch(Exception $e) {
            logger::error(print_r($e, true));
            $servd->rollback();
            $result = array('errcode' => -19, 'errmsg' => '删除失败');
        }
        return $result;
    }
}
