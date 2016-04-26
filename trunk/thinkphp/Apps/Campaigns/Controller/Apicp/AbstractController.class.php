<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Campaigns\Controller\Apicp;
abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    public function before_action($action = '')
    {
        return parent::before_action($action);
    }

    public function after_action($action = '')
    {

        return parent::after_action($action);
    }
}