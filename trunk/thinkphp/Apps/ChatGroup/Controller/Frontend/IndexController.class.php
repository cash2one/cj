<?php

/**
 * IndexController.class.php
 * $author$
 */
namespace ChatGroup\Controller\Frontend;

use Common\Common\Cache;
use Org\Net\Snoopy;

class IndexController extends AbstractController {

	// Index
	public function Index() {
		// 先取群组列表
		$serv_gb = D('ChatGroup/Chatgroup', 'Service');
		$list = $serv_gb->list_all($this->_plugin->setting['perpage'], array('cg_id' => 'ASC'));
		// 格式化
		$serv_fmt = D('ChatGroup/Format', 'Service');

		foreach ($list as &$_v) {
			$serv_fmt->chatgroup_format($_v);
		}

		unset($_v);

		// 统计总数
		$count = $serv_gb->count() + 100;
		// 分页
		$page = new \Think\Page($count, $this->_plugin->setting['perpage']);
		$multi = $page->show();

		// 输出模板变量
		$this->assign('list', $list);
		$this->assign('multi', $multi);
		$this->assign('acurl', U('/ChatGroup/Api/Chatgroup/Create_group'));
		$this->assign('quiturl', U('/ChatGroup/Api/Chatgroup/Quit_group'));
		$this->assign('getMsgurl', U('/ChatGroup/Api/ChatgroupRecord/ListMsg'));

		$this->_output("Frontend/Index/Index");
	}

	// About
	public function About() {

		$this->_output("Frontend/Index/About");
	}

}
