<?php
/**
 * SubscribeService.class.php
 * $author$
 */

namespace Cli\Service;

class SubscribeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 更新关注状态
	 * @param int $page 当前页码
	 * @param number $perpage 每页记录数
	 * @return boolean
	 */
	public function update_subscribe($page, $perpage = 500) {

		// 获取起始行, 每页行数, 当前页
		list($start, $limit, $page) = page_limit($page, $perpage);

		// 读取站点列表
		$model_ep = D('Common/Enterprise');
		$eplist = $model_ep->list_all(array($start, $limit), array('ep_id' => 'ASC'));

		foreach ($eplist as $_ep) {
			// 执行计划任务
			$client = &\Com\Rpc::phprpc(cfg('PROTOCAL').$_ep['ep_domain'].'/UcRpc/Rpc/Crontab');
			$client->set_async(true);
			$client->run(array('subscribe'));
		}

		return count($eplist);
	}

}
