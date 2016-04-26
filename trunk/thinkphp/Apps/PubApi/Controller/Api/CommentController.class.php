<?php
/**
 * CommentController.class.php
 * $author$
 */
namespace PubApi\Controller\Api;

class CommentController extends AbstractController {

	/**
	 * 新增评论
	 * @return bool
	 */
	public function Add_post($r_params = array(), $extend = array()) {

		// 评论信息
		$comment = array();
		// 传入参数
		$params = I('request.');

		if (! empty($r_params)) {
			$params = $r_params;
		}

		// 数据入库
		$serv_c = D('PubApi/Comment', 'Service');
		if (!$serv_c->create_comment($comment, $params, $extend)) {
			$this->_set_error($serv_c->get_errmsg(), $serv_c->get_errcode());
			return false;
		}

		// 返回结果
		$this->_result = $comment;
		return $comment;
	}

	/**
	 * 评论列表
	 * @return bool
	 */
	public function List_get($r_params = array()) {

		// 传入参数
		$params = I('request.');
		if (! empty($r_params)) {
			$params = array_merge($params, $r_params);
		}

		// 获取数据
		$serv_c = D('PubApi/Comment', 'Service');
		$list = $serv_c->list_comment($params);

		// 返回数据
		$this->_result = $list;
		return $list;
	}

}
