<?php
/**
 * 新建活动接口
 * NewController.class.php
 * $author$
 * User: Yinmengxuan
 */

namespace Activity\Controller\Api;

class NewController extends AbstractController {

	/**
	 * 新建活动接口
	 * post方式
	 * */
	public function New_post() {

		$acid = I('post.acid', '', 'intval');//活动ID
		$title = I('post.title');//活动主题
		$content = I('post.content');//活动内容
		$atids = I('post.atids', '');//附件ID
		$start = I('post.start');//开始时间
		$end = I('post.end');//结束时间
		$cut = I('post.cut');//截止时间
		$address = I('post.address');//活动地点
		$np = I('post.np', '');//限制人数
		$users = I('post.users', '');//邀请人员
		$dp = I('post.dp', '');//邀请部门
		$outsider = I('post.outsider', '');//外部人员
		$outfield = I('post.outfield', '');//列表字段
		$is_edit = I('post.is_edit', '');//是否为编辑状态

		$date = time(); //当前时间

		$serv = D('Activity/Activity', 'Service');
		// 如果是编辑
		if (!empty($is_edit)) {
			//活动创建时间$created
			$activity = $serv->get($acid);
			$created = rgmdate($activity['created'], 'Y-m-d H:i');
			if ($cut < $created) {
				$this->_set_errcode('_TIME_CUT_CREATED');
			}
		} else {
			// 如果是新增
			if (empty($acid)) {
				/*邀请人员选择*/
				if ($cut < $date) {
					$this->_set_errcode('_TIME_CUT_DATA');
				}
				}
		}
		$user = $this->_login->user;
		$data = array(
			'title' => $title,
			'content' => $content,
			'address' => $address,
			'start_time' => $start,
			'end_time' => $end,
			'cut_off_time' => $cut,
			'np' => $np,
			'm_uid' => $user['m_uid'],
			'uname' => $user['m_username'],
			'dp' => $dp,
			'users' => $users,
			'at_ids' => $atids,
			'outsider' => $outsider,
			'outfield' => $outfield,
		);

		// 入库操作
		$return = $serv->add($data, $acid);

		$this->_result = $return;

		return true;
	}
}
