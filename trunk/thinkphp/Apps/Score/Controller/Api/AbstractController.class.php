<?php

namespace Score\Controller\Api;
use Common\Common\Cache;

abstract class AbstractController extends \Common\Controller\Api\AbstractController
{

    public function before_action($action = '')
    {

        $this->_require_login = true;
        return parent::before_action($action);
    }

    public function after_action($action = '')
    {

        return parent::after_action($action);
    }
}
