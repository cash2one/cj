<?php
/**
 * voa_s_oa_blessingredpack_blessingredpack.php
 * 说明
 * @author: anything
 * @createTime: 2015/11/26 17:46
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_s_oa_blessingredpack_blessingredpack extends voa_s_abstract {
    protected $_d_class;

    public function __construct() {

        parent::__construct();
        if ($this->_d_class == null) {
            $this->_d_class = new voa_d_oa_blessingredpack_blessingredpack();
        }
    }
} 
