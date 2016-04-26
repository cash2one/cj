<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/12
 * Time: 14:44
 */

namespace Note\Controller\Apicp;
use Common\Common\Plugin;
use Common\Common\Cache;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController{

    public function before_action($action = '') {

        $this->_require_login = true;
        return parent::before_action($action);
    }

    public function after_action($action = '') {

        return parent::after_action($action);
    }

}
