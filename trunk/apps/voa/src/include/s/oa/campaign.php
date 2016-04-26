<?php
/**
 * 活动推广
 * $Author$
 * $Id$
 */

class voa_s_oa_campaign extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 验证兼过滤内容
	 *
	 * @param array $content
	 * @param array $data
	 * @param string $error
	 * @return boolean
	 */
	public function filter($content, & $data, & $error) {

		if (! $content['time'] || $content['time'] == '全天') {
			$content['time'] = '23:59:59';
		}

		// 过滤多余字段
		$fiels = array('subject', 'typeid', 'cover', 'begintime', 'overtime', 'time', 'is_custom', 'needsign', 'id', 'content', 'is_push', 'uid', 'username');
		foreach ($fiels as $f) {
			if (isset($content[$f])) {
				$data[$f] = $content[$f];
			}
		}

		if (! $data['subject']) {
			$error = '标题不能为空';
			return false;
		}

		if (! is_numeric($data['typeid'])) {
			$error = '分类错误';
			return false;
		}

		$data['overtime'] = rstrtotime($data['overtime'] . ' ' . $data['time']);
		if (!empty($data['begintime'])) {
			$data['begintime'] = rstrtotime($data['begintime']) . ' ' . $data['btime'];
		} else {
			$data['begintime'] = 0;
		}

		unset($data['time'], $data['btime']);
		if ($data['overtime'] < time()) {
			$error = '截止时间不能小于当前时间';
			return false;
		}

		if ($data['begintime'] > $data['overtime']) {
			$error = '开始时间不能在截止时间之后';
			return false;
		}

		$data['id'] = intval($data['id']);
		$data['is_custom'] = intval($data['is_custom']);
		$data['is_push'] = intval($data['is_push']);
		$data['uid'] = intval($data['uid']);
		$data['cover'] = intval($data['cover']);
		$data['subject'] = strip_tags($data['subject']);

		return true;
	}

	/**
	 * 删除活动及其相关的报名表,权限表,统计表
	 *
	 * @param mixed $id
	 */
	public function del_act($id) {

		// 删除活动
		$d = new voa_d_oa_campaign_campaign();
		$rs = $d->delete($id);

		// 删除报名信息
		$d = new voa_d_oa_campaign_reg();
		$where = array('actid' => $id);
		$rs = $d->delete_by_conds($where);

		// 删除权限信息
		$d = new voa_d_oa_campaign_right();
		$rs = $d->delete_by_conds($where);

		// 删除统计信息
		$d = new voa_d_oa_campaign_total();
		$rs = $d->delete_by_conds($where);
		return true;
	}
}
