<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Guestbook\Controller\Frontend;
use Common\Common\Cache;
use Org\Net\Snoopy;
use Com;

class IndexController extends AbstractController {

	// Index
	public function Index() {

		//var_dump($this->_plugin);
		//var_dump($this->_plugin_setting);

		/**$url = cfg('UCENTER_RPC_HOST').'/OaRpc/Rpc/Suite';
		if (!\Com\Rpc::query($suite, $url, 'get_by_suiteid', 'tj0129f84436fb3a58')) {
			exit('error');
		}*/

		//E('_ERR_SUITEID_IS_EMPTY');
		//$snoopy = new Snoopy();
		//$result = $snoopy->fetch('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx550ecf848992c9b0&secret=c92b18999a8b72997eb4d142eeff3d12');
		// var_dump($snoopy->results);

		/**$data = array();
		$url = 'http://test.vchangyi.com/forum/api/dzapi.php?ac=addCredit';
		$url = 'http://test.vchangyi.com/forum/api/dzapi.php?ac=getCredit';
		$pdata = array('extcredits' => 1, 'credits' => 3, 'openid' => 'cy001');
		//$pdata = array();
		$sig = \Com\ApiSig::instance()->create($pdata, NOW_TIME, '0c4c993a9b061847e89ca0db9f18f881');
		$url .= '&ts=' . NOW_TIME . '&sig=' . $sig;
		echo $url;
		rfopen($data, $url, $pdata, '', 'post');
		print_r($data);exit;*/

		// 取 access token
		$serv_wx = &\Common\Common\Wxqy\Service::instance();
		//$serv_wx->get_access_token();
		$chat = new \Com\Chat($serv_wx);
		$chatid = 'chat010';
		/**$chatinfo = array(
			'chatid' => $chatid,
			'name' => '测试001',
			'owner' => 'cy001',
			'userlist' => array('luck', 'cy001', '3ea21f15181b4715551215652e8830a1', '384699fdd55728374b32d432cbf79fb7')
		);
		if (!$chat->create($chatinfo)) {
			echo 'create fail.';
		}*/

		/**$chatinfo = array();
		if (!$chat->get($chatinfo, $chatid)) {
			echo 'get fail.';
		}

		print_r($chatinfo);*/

		/**$chatinfo = array(
			'chatid' => $chatid,
			'name' => 'wsd001',
			'owner' => 'cy001',
			'op_user' => 'cy001',
			'add_user_list' => array('8bcf03c89ccc2472a1656cd6c7f1cddc'),
			'del_user_list' => array('luck')
		);
		if (!$chat->update($chatinfo)) {
			echo 'create fail.';
		}*/

		/**$params = array(
			'chatid' => $chatid,
			'op_user' => 'cy001'
		);
		if (!$chat->quit($params)) {
			echo 'create fail.';
		}*/

		/**$params = array(
			'chatid' => $chatid,
			'user_mute_list' => array(
				array('userid' => 'luck', 'status' => 0),
				array('userid' => '8bcf03c89ccc2472a1656cd6c7f1cddc', 'status' => 1)
			)
		);
		if (!$chat->setmute($params)) {
			echo 'create fail.';
		}*/

		/**$params = array(
			'chatid' => $chatid,
			'op_user' => 'cy001',
			'chat' => array(
				'type' => 'group',
				'id' => $chatid
			)
		);
		if (!$chat->clearnotify($params)) {
			echo 'create fail.';
		}*/

		/**$text = 'test message';
		$to = $chatid;
		$from = 'cy001';
		$chattype = 'group';
		if (!$chat->send($text, $to, $from, $chattype)) {
			echo 'create fail.';
		}*/

		// signature
		//$signature = new \Com\SignatureSuite($serv_wx);
		//$signature->check_signature();

		// 获取 api ticket
		//$serv_wx->get_jsapi_ticket();
		//$addr_wx = new \Common\Common\Wxqy\Addrbook($serv_wx);
		/**$user = array(
			'userid' => 'zhangsan', 'name' => '张三',
			'department' => array(2), 'position' => 'PHP工程师', 'mobile' => '13512345678',
			'gender' => '1', 'tel' => '', 'email' => 'zhuxun37@gmail.com',
			'weixinid' =>'zhangsan37', 'qq' => '12345678'
		);
		$result = '';
		if (!$addr_wx->user_create($result, $user)) {
			echo 'create failed.';
		}*/
		//$dps = array();
		//$addr_wx->list_departments($dps);
		//$users = array();
		//$addr_wx->department_simple_list($users);
		// $this->show('[IndexController->Index]');

		// 先取列表
		/**$serv_gb = D('Guestbook/Guestbook', 'Service');
		// 取页码
		$page = I('get.' . cfg('VAR_PAGE'));
		// 获取起始行, 每页行数, 当前页
		list($start, $limit, $page) = page_limit($page, $this->_plugin->setting['perpage']);
		// 读取列表
		$list = $serv_gb->list_all(array($start, $limit), array('id' => 'ASC'));

		// 格式化
		$serv_fmt = D('Guestbook/Format', 'Service');
		foreach ($list as &$_v) {
			$serv_fmt->guestbook($_v);
		}

		unset($_v);

		// 统计总数
		$count = $serv_gb->count() + 100;

		// 分页
		$page = new \Think\Page($count, $limit);
		$multi = $page->show();*/

		// 输出模板变量
		//$this->assign('list', $list);
		//$this->assign('multi', $multi);
		$this->assign('acurl', U('/Guestbook/Api/Guestbook/Message'));
		$this->assign('listurl', U('/Guestbook/Api/Guestbook/List'));

		$this->_output("Frontend/Index/Index");
	}

	// About
	public function About() {

		$this->_output("Frontend/Index/About");
	}
}
