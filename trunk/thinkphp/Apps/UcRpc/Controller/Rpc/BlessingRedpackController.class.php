<?php
/**
 * BlessingRedpackController.class.php
 * 红包定时任务
 * @author: anything
 * @createTime: 2015/11/25 9:41
 * @version: $Id$
 * @copyright: 畅移信息
 */

namespace UcRpc\Controller\Rpc;
use UcRpc\Controller\Rpc\AbstractController;
use UcRpc\Service\BlessingRedpackService;

class BlessingRedpackController extends AbstractController{

    public function __construct(){
        parent::__construct();
    }

	/**
	 * 发送红包消息测试
	 *
	 * @author anything
	 */
	public function test() {

		// 执行计划任务
		/**$client = &\Com\Rpc::phprpc('http://local.vchangyi.net/UcRpc/Rpc/Crontab');
		$client->set_async(true);
		$client->run(array('blessRedpack'));*/
    }
}
