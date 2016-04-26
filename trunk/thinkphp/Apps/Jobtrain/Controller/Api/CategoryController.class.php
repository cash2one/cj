<?php

namespace Jobtrain\Controller\Api;
use Common\Common\WxqyMsg;
use Org\Util\String;

class CategoryController extends AbstractController {

    
    public function cata_list_get() {
        $serv_cata = D('Jobtrain/JobtrainCategory', 'Service');
        $catas = $serv_cata->get_tree_with_right($this->_login->user['m_uid']);
        $this->_result = array(
            'catas' => $catas
        );
        return true;
    }

}