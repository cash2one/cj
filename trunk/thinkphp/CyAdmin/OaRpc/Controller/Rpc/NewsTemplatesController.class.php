<?php
/**
 * 新闻公告模板
 * NewsTemplatesController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class NewsTemplatesController extends AbstractController {

	/**
	 * @return 获取新闻模板所有记录
	 */
	public function list_template() {

		$serv_tpl = D('Common/NewsTemplates', 'Service');
		return $serv_tpl->list_all();
	}

	/**
	 * 获取指定ID的新闻模板
	 * @param int $id 新闻公告ID
	 */
	public function get_by_id($id) {

		$serv_tpl = D('Common/NewsTemplates', 'Service');
		return $serv_tpl->get($id);
	}

}
