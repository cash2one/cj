<?php
/**
 * 统一数据访问/活动
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_campaign_campaign extends uda {

	/**
	 * 根据条件查找活动
	 *
	 * @param int|array $page_option 分页参数
	 * @param array $conds 条件数组
	 * @param bool $is_total
	 */
	public function ls($page, &$result, $conds, $is_total = true) {

		$d = &uda::factory('voa_d_oa_campaign_campaign');

		$result['count'] = $d->count_by_conds($conds);
		$result['list'] = $d->list_by_conds($conds, $page, array('id' => 'DESC'));
		if ($result['count'] == 0) {
			$result['list'] = array();
			return true;
		}

		$this->_format($result['list'], $is_total);
		return true;
	}

	// 数据格式化
	public function _format(& $list, $is_total = false) {

		$single = 0;
		if (is_numeric(current($list))) {
			// 一维转为二维数组,方便下面统一处理
			$single = 1;
			$list = array($list);
		}

		$actids = array();
		foreach ($list as & $r) {
			$r['_overtime'] = rgmdate($r['overtime'], 'Y-m-d H:i');
			$r['_type'] = voa_d_oa_campaign_type::get_type($r['typeid']);
			$r['_is_push'] = $r['is_push'] == 0 ? '草稿' : '已发布';
			$actids[] = $r['id'];
		}

		// 获取统计信息
		if ($is_total) {
			$t = &uda::factory('voa_d_oa_campaign_total');
			$total = $t->get_total($actids);
			foreach ($list as & $v) {
				if (isset($total[$v['id']])) {
					$v['share'] = $total[$v['id']]['share'];
					$v['hits'] = $total[$v['id']]['hits'];
					$v['regs'] = $total[$v['id']]['regs'];
					$v['signs'] = $total[$v['id']]['signs'];
				} else {
					$v['share'] = $v['hits'] = $v['regs'] = $v['signs'] = 0;
				}
			}
		}

		if ($single) {
			$list = $list[0];
		}
	}

	/**
	 * 编辑/添加活动
	 *
	 * @param array $request 请求的参数
	 * @param array $result 返回的结果
	 * @param array $options 其他额外的参数（扩展用）
	 * @param string $error 错误信息
	 * @return boolean
	 */
	public function save($request, &$result, &$error) {

		// 验证兼过滤内容
		$s = new voa_s_oa_campaign();
		$data = array();
		$rs = $s->filter($request, $data, $error);
		// 检查过滤，参数
		if (! $rs) {
			return false;
		}

		// 保存
		$d = new voa_d_oa_campaign_campaign();
		$new_deps = isset($_POST['deps']) ? $_POST['deps'] : array();
		$data['is_all'] = $new_deps ? 0 : 1; // 如果不选部门则全部可见

		$right = new voa_d_oa_campaign_right();
		if ($data['id']) {
			$rs = $d->update($data['id'], $data);
			if (! $rs) {
				$error = '编辑活动错误';
				return false;
			}

			// 保存权限1.获取旧权限 2.对比新权限 无则增加,有则删除
			$old_deps = $right->get_right($data['id']);

			// 新增部门
			$add = array_diff($new_deps, $old_deps);
			$right->add_right($data['id'], $add);

			// 要删除的部门
			$minus = array_diff($old_deps, $new_deps);
			$right->del_right($data['id'], $minus);

			$result = $data;
		} else {
			unset($data['id']);
			$act = $d->insert($data);
			if (! $act) {
				$error = '添加活动错误';
				return false;
			}

			// 新增部门
			$right->add_right($act['id'], $new_deps);
			$result = $act;
		}

		return true;
	}

	/**
	 * 保存自定义字段
	 *
	 * @param intval $actid
	 * @param intval $saleid
	 * @param array $custom
	 */
	public function save_custom($actid, $saleid, $custom) {

		$d = new voa_d_oa_campaign_custom();
		$rec = $d->get_by_conds(array('actid' => $actid, 'saleid' => $saleid));
		$custom = implode(',', $custom);
		$data = array('actid' => $actid, 'saleid' => $saleid, 'custom' => $custom);
		if (! $rec) {
			$rs = $d->insert($data);
		} else {
			$rs = $d->update($rec['id'], $data);
		}

		if (! $rs) {
			return false;
		}

		return true;
	}

	/**
	 * 获取自定义字段
	 *
	 * @param intval $actid
	 * @param intval $saleid
	 */
	public function get_custom($actid, $saleid) {

		$d = new voa_d_oa_campaign_custom();
		$rec = $d->get_by_conds(array('actid' => $actid, 'saleid' => $saleid));
		$custom = $rec['custom'] ? explode(',', $rec['custom']) : array();
		return $custom;
	}

	/**
	 * 生成二维码
	 *
	 * @param int $id
	 * @param string $file
	 */
	public function qrcode($id) {

		// 生成二维码
		include_once (ROOT_PATH . '/framework/lib/phpqrcode.php');
		// 跳转地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . "{$_SERVER['HTTP_HOST']}/frontend/campaign/scan?regid=" . $id;

		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);

		// 输出图片
		header('Content-Type: image/png');
		imagepng($qrcode);
	}
}
