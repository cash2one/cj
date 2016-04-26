<?php
/**
 * voa_s_oa_blessingredpack_blessingmember.php
 * 红包活动用户
 * @author: anything
 * @createTime: 2015/11/26 18:01
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_s_oa_blessingredpack_blessingmember extends voa_s_abstract {

    protected $_d_class;

    public function __construct() {

        parent::__construct();
        if ($this->_d_class == null) {
            $this->_d_class = new voa_d_oa_blessingredpack_blessingmember();
        }
    }
} 
