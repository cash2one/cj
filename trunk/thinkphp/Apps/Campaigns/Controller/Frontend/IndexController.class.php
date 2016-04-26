<?php

/**
 * IndexController.class.php
 * $author$
 */

namespace Campaigns\Controller\Frontend;

class IndexController extends AbstractController {
    //文档中心
    public function docCenter() {
        redirect('/h5/index.html?#/app/page/campaigns/campaigns-center');
        return true;
    }
    //数据跟踪
    public function dataTrack() {
        redirect('/h5/index.html?#/app/page/campaigns/campaigns-sharedata');
        return true;
    }

}
