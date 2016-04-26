<?php
/**
 * voa_c_api_nvote_base
 * 投票调研api基类
 * User: luckwang
 * Date: 15/3/11
 * Time: 下午2:22
 */

class voa_c_api_nvote_base extends voa_c_api_base {

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

    /**
     * 获取查看详情的url
     * @param int $nv_id 投票主题id
     * @return boolean
     */
    public function get_view_url($nv_id) {
        /** 组织查看链接 */
        return voa_wxqy_service::instance()->oauth_url(
                        config::get(startup_env::get('app_name') .
                        '.oa_http_scheme') .
                        $this->_setting['domain'] .
                        '/nvote/view/' . $nv_id .
                        '?pluginid='.startup_env::get('pluginid'));
    }
}
