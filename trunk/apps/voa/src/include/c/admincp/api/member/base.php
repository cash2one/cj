<?php
/**
 * api用户基类
 * voa_c_admincp_api_member_base
 * User: luckwang
 * Date: 15/4/2
 * Time: 上午11:44
 */

class voa_c_admincp_api_member_base extends voa_c_admincp_api_base {

    /**
     * 职位缓存
     * @var
     */
    protected $_jobs;

    /**
     * 用户配置信息
     * @var
     */
    protected $_settings;

    /**
     * 部门缓存
     * @var
     */
    protected $_departments;

    protected function _before_action($action) {

        if (!parent::_before_action($action)) {
            return false;
        }
        if (empty($this->_jobs)) {
            $this->_jobs = voa_h_cache::get_instance()->get('job', 'oa');
            $this->_departments = voa_h_cache::get_instance()->get('department', 'oa');
            $this->_settings = voa_h_cache::get_instance()->get('plugin.member.setting', 'oa');
        }
        return true;
    }

    protected function _after_action($action) {

        if (!parent::_after_action($action)) {
            return false;
        }

        return true;
    }

    /**
     * 获取指定部门下面所有的子部门(递归)
     * @param $cd_id
     * @return array
     */
    protected function _get_depart_childrens($cd_id) {
        $dp_ids = array();
        foreach ($this->_departments as $department) {
            if ($department['cd_upid'] == $cd_id) {
                $dp_ids[] = $department['cd_id'];

                $dp_ids = array_merge($dp_ids, $this->_get_depart_childrens($department['cd_id']));
            }
        }
        return $dp_ids;
    }


    /**
     * 获取用户扩展字段
     * @return array
     */
    protected function _get_member_fields() {

        $fields = array();
        // 如果扩展字段为空
        if (empty($this->_settings['fields'])) {
        	return $fields;
        }

        // 遍历所有字段, 重新组织
        foreach ((array)$this->_settings['fields'] as $k=>$field) {
            if ($field['status'] != 0) {
                $fields[$k] = $field;
            }
        }

        return $fields;
    }


    /**
     * 保存扩展字段值
     * @param $m_uid
     * @param $fields
     * @return bool
     */
    protected function _save_member_fields_value($m_uid, $fields) {
        $data = array();
        foreach ($fields as $k=>$v) {
            if (isset($this->_settings['fields'][$k]) &&
                    $this->_settings['fields'][$k]['status'] != 0) {
                if ($this->_settings['fields'][$k]['status'] == 1){
                    $data['mf_ext' . $k] = $v;
                } else {
                    $data[$k] = $v;
                }
            }
        }
        $data['m_uid'] = $m_uid;
        try {
            $servmf = &service::factory('voa_s_oa_member_field', array('pluginid' => 0));

            $servmf->insert($data, false, true);

            return true;
        } catch (Exception $e) {
            logger::error(print_r($e, true));
        }
        return false;

    }


    /**
     * 获取用户扩展字段值
     * @param $m_uid
     * @return array
     */
    protected function _get_member_fields_value($m_uid) {

        $servmf = &service::factory('voa_s_oa_member_field', array('pluginid' => 0));

        $data = array();
        $fields_value = $servmf->fetch_by_id($m_uid);
        if (empty($fields_value)) {
            return $data;
        }
        $fields = $this->_get_member_fields();

        foreach ($fields as $k=>$field) {
            $data[$k]['desc'] = $field['desc'];
            if ($field['status'] != 2 ) {
                $data[$k]['value'] = $fields_value['mf_ext' . $k];
            } else {
                $data[$k]['value'] = $fields_value['mf_' . $k];
            }
        }

        return $data;
    }

}
