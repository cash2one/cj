<?php
/**
 * base.php
 * 祝福红包基类
 * @author: anything
 * @createTime: 2015/11/16 10:31
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_c_admincp_office_blessingredpack_base extends voa_c_admincp_office_base{


    protected $_p_sets = array();

    public function __construct() {

        parent::__construct();
    }

    protected function _before_action($action) {

        if (!parent::_before_action($action)) {
            return false;
        }

        //FIXME ！！！涉及指定应用更新问题
        $this->_p_sets = voa_h_cache::get_instance()->get('plugin.blessingredpack.setting', 'oa');

        return true;
    }

    protected function _after_action($action) {

        parent::_after_action($action);
        return true;
    }

}
