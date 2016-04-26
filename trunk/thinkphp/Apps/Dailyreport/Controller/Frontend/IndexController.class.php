<?php

/**
 * IndexController.class.php
 * $author$
 */

namespace Dailyreport\Controller\Frontend;

class IndexController extends AbstractController {

    //新建报告
    public function NewDailyreport() {
        redirect('/h5/index.html?#/app/page/dailyreport/dailyreport-main');
        return true;
    }

    //我负责的
    public function Responsibles() {
        redirect('/h5/index.html?#/app/page/dailyreport/dailyreport-responsible');
        return true;
    }

    //与我相关
    public function AboutMe() {
        redirect('/h5/index.html?#/app/page/dailyreport/dailyreport-about-me');
        return true;
    }

	// 草稿
	public function Draft() {

		// redirect('/h5/index.html?#/app/page/dailyreport/dailyreport-draft');
		$url = U('/', '', false, true) . 'h5/index.html?_ts=' . NOW_TIME . '#/app/page/dailyreport/dailyreport-draft';
		$this->assign('redirectUrl', $url);
		$this->_output('Common@Frontend/Redirect');
		return true;
	}

    //我发起的
    public function SendList() {
        redirect('/h5/index.html?#/app/page/dailyreport/dailyreport-send-list');
        return true;
    }

}
