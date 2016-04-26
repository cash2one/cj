<?php

/**
 * 后台管理/线下培训/报名设置
 */
class voa_c_cyadmin_content_train_setting extends voa_c_cyadmin_content_train_base {
    private $__uda;

    public function execute() {
        $this->__uda = &uda::factory('voa_uda_cyadmin_content_train_setting');

        if ($this->_is_post()) {

            $result = $this->__DataHandler();
            echo json_encode($result);
            return false;
        }

        $allData = $this->__uda->get_all(); // 获取所有的数据

        $this->view->set('list', $allData);
        $this->_render('setting');
    }

    private function __DataHandler() {
        $ActionType = $this->request->post('action'); // Action{add|del}

        // 如果Action值为空,下面程序不执行
        if (empty($ActionType)) {
            return 'Empty Action';
        }

        $form = $this->request->post('data');

        switch ($ActionType) {
            case 'add' :
                return $this->__uda->add($form);
                break;
            case 'del' :
                return $this->__uda->del($form);
                break;
            case 'edit':
                return $this->__uda->edit($form);
                break;
        }
    }
}
