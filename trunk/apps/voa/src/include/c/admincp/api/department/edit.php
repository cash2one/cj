<?php
/**
 * api编辑部门
 * voa_c_admincp_api_department_edit
 * User: luckwang
 * Date: 15/4/2
 * Time: 上午11:40
 */

class voa_c_admincp_api_department_edit extends voa_c_admincp_api_department_base {

    public function execute() {

        $up_id = $this->request->get('up_id');
        $name = $this->request->get('name');
        $order = $this->request->get('order');
        $cd_id = $this->request->get('id');
        $purview = $this->request->get('purview');
        $up_id = rintval($up_id);
        $this->__edit($up_id, $name, $order, $cd_id,$purview);

    }

    public function __edit($up_id, $name, $order, $cd_id,$purview) {

        $data['cd_upid'] = $up_id;
        $data['cd_name'] = $name;
        $data['cd_displayorder'] = $order;
        $data['cd_id'] = $cd_id;
        $data['cd_purview'] = $purview;

        $history = array();
        //判断是否为编辑，读取历史信息
        if (!empty($cd_id) && $cd_id > 0) {
            $history = $this->_departments[$cd_id];
        }

        $update = array();
        //调用uda更新
        $uda = &uda::factory('voa_uda_frontend_department_update');
        $flag = $uda->update($history, $data, $update);

        // 更新该部门的成员数
        $uda->update_usernum($cd_id, 0);

        $result['errcode'] = $uda->errcode;
        $result['errmsg'] = $uda->errmsg;
        if ($flag) {

            $result['id'] = $update['cd_id'];
            $result['name'] = $update['cd_name'];
            $result['upid'] = $update['cd_upid'];
        }

        $this->_output_result($result);
    }
}
