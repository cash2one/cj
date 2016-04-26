<?php
namespace File\Controller\Frontend;

use File\Controller\Api;
use Common\Common\Cache;
use Com;

class CommentController extends AbstractController {

	/**
	 * Created by PhpStorm.
	 * User: Hu Sendong
	 */
	public function Comment() {

		$file_id = I('get.file_id');

		// 接口地址
		$this->assign('acurl', U('/File/Api/Comment/Comment_create_post'));
		$this->assign('listurl', U('/File/Api/Comment/Comment_list_get?file_id='.$file_id));

		// 加载Public文件路径
		$this->assign('file_id', $file_id);
		$this->assign('static_path', cfg('static_path'));

		// 模板输出
		$this->_output("Frontend/Comment/comment");
	}
}
