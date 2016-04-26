<?php
/**
 * voa_c_api_nvote_get_memoptions
 * User: luckwang
 * Date: 15/3/31
 * Time: 下午3:16
 */

class voa_c_api_nvote_get_memoptions extends voa_c_api_nvote_base
{

    public function execute()
    {

        /*需要的参数*/
        $fields = array (
            'page' => array('type' => 'int', 'required' => false),
            'nv_id' => array('type' => 'int', 'required' => true),
            'limit' => array('type' => 'int', 'required' => false)
        );
        /*基本验证检查*/
        if (!$this->_check_params($fields)) {
            return false;
        }
        /*检查页码*/
        if (empty($this->_params['page'])) {
            $this->_params['page'] = 1;
        }

        return $this->__list();
    }

    private function __list() {
        $this->_params['limit'] = 5;
        $start = ($this->_params['page'] - 1) * $this->_params['limit'];
        $limit = array($start, $this->_params['limit']);
        $result = array();
        //获取用户投票结果
        $uda = &uda::factory('voa_uda_frontend_nvote_mem_option_get');
        if ($uda->limit_mem_options($this->_params['nv_id'], $result, $limit)) {
            //用户
            $usernames = $this->__get_usernames($result['list']);
            $options = $this->__get_options($this->_params['nv_id']);
            $result['data'] = array();
            //组织输出结果
            foreach ($result['list'] as $item) {
                //多选项的用户，拼接输出结果
                if (isset($result['data'][$item['m_uid']])) {
                    $result['data'][$item['m_uid']]['option'] .= ',' . rhtmlspecialchars($options[$item['nvote_option_id']]);
                } else {
                    $result['data'][$item['m_uid']]['m_username'] = $usernames[$item['m_uid']];
                    $result['data'][$item['m_uid']]['option'] = rhtmlspecialchars($options[$item['nvote_option_id']]);
                }

            }
        }

        //输出结果
        $this->_result = array (
            'total' => $result['count'],
            'limit' => $this->_params['limit'],
            'page' => $this->_params['page'],
            'data' => $result['data']
        );

        return true;
    }

    /**
     * 获取投票用户
     * @param $list
     * @return array
     */
    private function __get_usernames($list) {

        $uids = array_column($list, 'm_uid');
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids($uids);
        return array_column($users, 'm_username','m_uid');
    }

    /**
     * 获取投票选项
     * @param $nv_id
     * @return array
     */
    private function __get_options($nv_id) {

        $serv_option = &service::factory('voa_s_oa_nvote_option');
        $options = $serv_option->list_by_conds(array('nvote_id = ?' => $nv_id));

        return array_column($options, 'option', 'id');
    }
}
