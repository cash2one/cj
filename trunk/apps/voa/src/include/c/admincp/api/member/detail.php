<?php
/**
 * voa_c_admincp_api_member_detail
 * User: luckwang
 * Date: 15/4/16
 * Time: 上午9:45
 */

class voa_c_admincp_api_member_detail extends voa_c_admincp_api_member_base {

    public function execute() {
        $id = $this->request->get('id');
        $id = rintval($id);

        $result['data'] = array();

        if ($this->request->get('all_fields')) {
            $result['fields'] = $this->__sort_fields($this->_get_member_fields());
            $result['positions'] = $this->__get_positions();
        }

        if (!empty($id)) {
            $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
            $member = $servm->fetch($id);

            if (!empty($member)) {
                $data['uid'] = $member['m_uid'];
                $data['mobilephone'] = $member['m_mobilephone'];
                $data['email'] = $member['m_email'];
                $data['username'] = $member['m_username'];
                $data['weixin'] = $member['m_weixin'];
                $data['gender'] = $member['m_gender'];
                $data['displayorder'] = $member['m_displayorder'];
                $data['active'] = $member['m_active'];
                //性别
                if ($data['gender'] == 0) {
                    $data['_gender'] = '未知';
                } elseif ($data['gender'] == 1) {
                    $data['_gender'] = '男';
                } elseif ($data['gender'] == 2) {
                    $data['_gender'] = '女';
                }

                $data['face'] = voa_h_user::avatar($member['m_uid'], $member);
                $data['qywxstatus'] = $member['m_qywxstatus'];
                //微信关注状态
                if ($data['qywxstatus'] == 4) {
                    $data['_qywxstatus'] = '未关注';
                } elseif ($data['qywxstatus'] == 1) {
                    $data['_qywxstatus'] = '已关注';
                } else {
                    $data['_qywxstatus'] = '已冻结';
                }

                //职位信息
                $data['job'] = '';
                if ($member['cj_id'] && isset($this->_jobs[$member['cj_id']])) {
                    $data['job'] = $this->_jobs[$member['cj_id']]['cj_name'];
                }
                //获取部门
                $data['cd_ids'] = $this->__get_cd_ids($id);
                $data['cd_names'] = $this->__get_cd_name($data['cd_ids']);

                //获取扩展字段值
                $data['fields'] = $this->_get_member_fields_value($id);
                $result['data'] = $data;
            }
        }

        $this->_output_result($result);
    }

    /**
     * 自定义属性排序
     * @param $fields
     * @return array
     */
    private function __sort_fields($fields) {
        $result = array();
        foreach ($fields as $k => $field) {
            $temp = $field;
            $temp['key'] = $k;
            $result[] = $temp;
        }

        return $result;
    }

    /**
     * 获取部门id
     * @param $m_uid
     * @return array
     */
    private function __get_cd_ids($m_uid) {
        $servm = &service::factory('voa_s_oa_member_department', array('pluginid' => 0));
        $list = $servm->fetch_all_field_by_uid($m_uid);
        $result = array();
        if ($list) {
            foreach ($list as $item) {
                $result[$item['cd_id']] = $item['mp_id'];
            }
        }

        return $result;
    }

    private function __get_cd_name($cd_ids) {

        $ps = voa_h_cache::get_instance()->get('plugin.member.positions', 'oa');
        $cd_name = array();

        foreach ($cd_ids as $cd_id => $mp_id) {
            $temp['cd_name'] = $this->_departments[$cd_id]['cd_name'];
            if (!empty($ps[$mp_id])) {
                $temp['mp_name'] = $ps[$mp_id]['mp_name'];
            } else {
                $temp['mp_name'] = '';
            }
            array_push($cd_name, $temp);
        }

        return $cd_name;
    }

    /**
     * 获取所有职务
     * @return array
     */
    private function __get_positions() {

        $ps = voa_h_cache::get_instance()->get('plugin.member.positions', 'oa');
        $list = array();
        foreach ((array)$ps as $p) {
            $list[$p['mp_id']] = $p['mp_name'];
        }

        return $list;
    }
}
