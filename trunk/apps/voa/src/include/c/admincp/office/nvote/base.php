<?php
/**
 * voa_c_admincp_office_nvote_base
 * 投票调研-控制器基类
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:43
 */

class voa_c_admincp_office_nvote_base extends voa_c_admincp_office_base {

    protected $_page = 1;

    protected $_vote_settings;

    public function __construct() {

        parent::__construct();
    }

    protected function _before_action($action) {

        if (!parent::_before_action($action)) {
            return false;
        }

        if (empty($this->_vote_settings)) {
            $this->_vote_settings = voa_h_cache::get_instance()->get('plugin.nvote.setting', 'oa');
        }

        return true;
    }

    protected function _after_action($action) {
        parent::_after_action($action);
        return true;
    }

    //初始化当前页码
    protected function _init_page() {
        //当前页码
        $p = $this->request->get('page');
        $p = rintval($p);
        if ($p > 0) {
            $this->_page = $p;
        }
        unset($p);
    }

    //获取列表分页信息
    protected function _get_multi($result) {
        $multi = '';
        if (isset($result['count']) &&
            $result['count'] > 0) {
            // 输出分页信息
            $multi = pager::make_links(array(
                'total_items' => $result['count'],
                'per_page' => $this->_vote_settings['perpage'],
                'current_page' => $this->_page,
                'show_total_items' => true,
            ));
        }
        return $multi;
    }

    //初始化参数过滤掉为空的值
    protected function _init_params($get, &$params) {
        foreach ($params as $key => &$v) {
            if (!empty($get[$key]) && ($get[$key]) != '') {
                $v = $get[$key];
            }
        }
    }
}
