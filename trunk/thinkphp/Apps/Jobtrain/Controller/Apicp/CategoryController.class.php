<?php

namespace Jobtrain\Controller\Apicp;

class CategoryController extends AbstractController {
    /**
     * 获取试卷列表
     */
    public function auths_get() {
        $cid = I('get.cid', 0, 'intval');
        $serv_cata = D('Jobtrain/JobtrainCategory', 'Service');

        $cata = $serv_cata->get_by_id($cid);
        // 返回操作
        $this->_result = array(
            'is_all' => $cata['is_all'],
            'departments' => $cata['departments'],
            'members' => $cata['members'],
        );
        return true;
    }
}