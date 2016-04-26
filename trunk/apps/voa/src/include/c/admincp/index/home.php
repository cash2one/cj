<?php
/**
 * voa_c_admincp_index_home
 * 首页
 *
 * $Author$
 * $Id$
 */

class voa_c_admincp_index_home extends voa_c_admincp_base {

    public function execute() {

        $this->view->set('controler', $this->controller_name);

        $this->output('index/home');
    }
}

