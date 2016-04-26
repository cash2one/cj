<?php
/**
 * voa_c_admincp_office_nvote_view
 * 投票调研-浏览
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:46
 */

class voa_c_admincp_office_nvote_view extends voa_c_admincp_office_nvote_base {

    protected $__department = array();

    protected $__nvote_options = array();

    public function execute() {

        $nv_id = $this->request->get('nv_id');
        if (empty($nv_id) ||
            !is_numeric($nv_id)) {
            $this->message('error', '请指定要查看的'.$this->_module_plugin['cp_name'].'数据');
        }
        if ($this->request->get('download')) {
            return $this->__download($nv_id);
        }
        $this->view->set('close_url', $this->cpurl($this->_module, $this->_operation, 'close', $this->_module_plugin_id, array('nv_id' => $nv_id)));
        $this->view->set('download_url', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('nv_id' => $nv_id, 'download' => 1)));

        $this->__get_detail($nv_id);

        $this->output('office/nvote/view');
    }

    /**
     * 获取投票信息
     * @param $nv_id
     */
    private function __get_detail($nv_id) {

        $uda = &uda::factory('voa_uda_frontend_nvote_get');
        $nvote = array();
        if (!$uda->get_vote($nv_id, $nvote)) {
            $this->message('error', '请指定要查看的'.$this->_module_plugin['cp_name'].'数据');
        }

        $uda_mem_option = &uda::factory('voa_uda_frontend_nvote_mem_option_get');
        $mem_options = array();
        if (!$uda_mem_option->mem_option_list($nv_id, $mem_options)) {
            $this->message('error', '请指定要查看的'.$this->_module_plugin['cp_name'].'数据');
        }

        $this->__get_username($nvote, $mem_options);

        $this->__format_nvote($nvote);

        $this->view->set('mem_options', $mem_options);

        $this->view->set('nvote', $nvote);
    }

    /**
     * 格式化投票信息
     * @param $nvote
     */
    private function __format_nvote(&$nvote) {
        $nvote['_start_time'] = rgmdate($nvote['created'], 'Y-m-d H:i');
        $nvote['_end_time'] = rgmdate($nvote['end_time'], 'Y-m-d H:i');
        $nvote['_is_show_name'] = $nvote['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES ? '实名投票' : '匿名投票';
    }

    /**
     * 获取用户名
     * @param $nvote
     * @param $mem_options
     */
    private function __get_username($nvote, $mem_options) {
        $uids = array();
        if (!empty($nvote['submit_id'])) {
            $uids[] = $nvote['submit_id'];
        }
        if (!empty($mem_options) &&
                $nvote['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES) {
            $mem_uids = array_column($mem_options, 'm_uid');
            $uids = array_merge($uids, $mem_uids);
        }

        if ($uids) {
            $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
            $users = $servm->fetch_all_by_ids($uids);
            $usernames = array_column($users, 'm_username', 'm_uid');
        }
        $usernames[0] = '后台管理员';
        $this->view->set('usernames', $usernames);
    }

    private function __download($nv_id) {


        $servn = &service::factory('voa_s_oa_nvote', array('pluginid' => 0));
        $nvote = $servn->get($nv_id);

        if (empty($nvote)) {
            $this->message('error', '请指定要查看的'.$this->_module_plugin['cp_name'].'数据');
        }

        $this->__department = voa_h_cache::get_instance()->get('department', 'oa');

        foreach ($this->__department as $_cd_id => &$_cd) {
            $allname = array();
            $this->__get_department_name($_cd_id, $allname);
            $_cd['allname'] = implode('>', $allname);
        }

        $title_string = array (
            '用户名称',
            '所在部门',
            '投票选项',
            '投票时间'
        );


        $serv_option = &service::factory('voa_s_oa_nvote_option');
        $options = $serv_option->list_by_conds(array('nvote_id = ?' => $nv_id));

        $this->__nvote_options = array_column($options, 'option', 'id');

        $servmn = &service::factory('voa_s_oa_nvote_mem_option', array('pluginid' => 0));
        $member_options = $servmn->get_vote_mems($nv_id);
        $mem_uids = array_column($member_options, 'm_uid');


        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids($mem_uids);
        $usernames = array_column($users, 'm_username', 'm_uid');

        $row_data = array();
        foreach ($member_options as $mo) {
            $created = rgmdate($mo['created']);
            //$username = isset($usernames[$mo['m_uid']]) ? $usernames[$mo['m_uid']] : '';
	        if ($nvote['is_show_name'] == 1) {
		        $username = $usernames[$mo['m_uid']];
	        } else {
		        $username = '匿名';
	        }

            $temp = array();
            $temp[] = $username;
            $temp[] = $this->__get_department($mo['m_uid']);
            $temp[] = $this->__get_nvote_option($mo['option_ids']);
            $temp[] = $created;

            $row_data[] = $temp;
        }
        // 下载的文件名
        $filename = $nvote['subject'];
        // 载入 Excel 类
        excel::make_excel_download($filename, $title_string, array(), $row_data, array());

        return true;
    }

    /**
     * 获取投票选项信息
     * @param $option_ids
     * @return string
     */
    private function __get_nvote_option($option_ids) {

        $options = explode(',', $option_ids);
        $option_names = array();
        foreach ($options as $option_id) {
            if (isset($this->__nvote_options[$option_id])) {
                $option_names[] = $this->__nvote_options[$option_id];
            }
        }

        return implode('|', $option_names);
    }

    /**
     * 获取部门
     * @param $m_uid
     * @return string
     */
    private function __get_department($m_uid) {

        $servm = &service::factory('voa_d_oa_member_department', array('pluginid' => 0));
        $cd_ids = $servm->fetch_all_by_uid($m_uid);
        $dps = array();
        foreach ($cd_ids as $_cd_id) {
            if (isset($this->__department[$_cd_id])) {
                $dps[] = $this->__department[$_cd_id]['allname'];
            }
        }
        return implode(',', $dps);
    }


    /**
     * 获取指定部门的所有级别
     * @param number $cd_id
     * @param array $names
     * @return boolean
     */
    private function __get_department_name($cd_id, &$names) {
        if (!isset($this->__department[$cd_id])) {
            // 可能是最顶级部门，则按层级排序
            krsort($names);
            return true;
        }

        $names[] = $this->__department[$cd_id]['cd_name'];
        if ($this->__department[$cd_id]['cd_upid'] && isset($this->__department[$this->__department[$cd_id]['cd_upid']]['cd_upid']) && $this->__department[$this->__department[$cd_id]['cd_upid']]['cd_upid']) {
            $this->__get_department_name($this->__department[$cd_id]['cd_upid'], $names);
        } else {
            krsort($names);
            return true;
        }

        return true;
    }


}
