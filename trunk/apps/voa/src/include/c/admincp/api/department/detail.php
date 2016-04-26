<?php
/**
 * voa_c_admincp_api_department_detail
 * User: luckwang
 * Date: 15/4/13
 * Time: 下午2:48
 */

class voa_c_admincp_api_department_detail extends voa_c_admincp_api_department_base {

    public function execute() {
        $id = $this->request->get('id');
        $id = rintval($id);

        if (empty($id)) {
            $this->_output_result(array('data' => array()));
            return;
        }
        //读取数据记录
        $servd = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
        $data = $servd->fetch($id);
        //var_dump($data);echo '11111111111111111111111111';exit;

        //判断是否为空
        if (!empty($data)) {
            $result['id'] = $data['cd_id'];
            $result['name'] = $data['cd_name'];
            $result['up_id'] = $data['cd_upid'];
            $result['order'] = $data['cd_displayorder'];
            $result['purview'] = $data['cd_purview'];
            $this->_output_result(array('data' => $result));
            return;
        }

        $this->_output_result(array('data' => array()));
    }
}
