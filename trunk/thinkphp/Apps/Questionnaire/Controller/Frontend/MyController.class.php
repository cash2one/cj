<?php
/**
 * MyController.class.php
 * $author$
 */

namespace Questionnaire\Controller\Frontend;

class MyController extends AbstractController {

	public function Index() {
		//我的问卷
		$url = '/newh5/questionnaire/index.html?#/app/page/questionnaire/my-questionnaire-list';
		redirect($url);
		return true;
	}
}
