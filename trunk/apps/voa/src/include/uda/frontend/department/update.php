<?php
/**
 * voa_uda_frontend_department_update
 * 统一数据访问/部门表/更新
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_department_update extends voa_uda_frontend_department_base {


	public function __construct() {
		parent::__construct();
	}

	/**
	 * 添加/更新 部门
	 * @param array $history 旧数据，如果为空则为新建
	 * @param array $department 新提交的数据
	 * @param array $update <strong style="color:red">(引用结果)</strong>返回完整的新数据
	 * @param boolean $ignore_qywx （ 已无效 ）是否忽略处理提交到企业微信接口，true则不提交数据给微信接口，false则按需自动提交
	 * @return boolean
	 */
	public function update($history, $department, &$update, $ignore_qywx = false) {

		$update = array();

		if (empty($department['cd_name'])) {
			$this->errmsg('1001', '部门名称必须提供');
			return false;
		}

		if ((empty($history) || empty($history['cd_id'])) && $this->serv->count_all() > voa_d_oa_common_department::COUNT_MAX) {
			// 如果是新增，判断部门数量是否超过限制
			$this->errmsg('1002', '系统限制最多允许添加 '.voa_d_oa_common_department::COUNT_MAX.' 个部门');
			return false;
		}

		if (empty($history)) {
			// 无历史数据，则认为是新增，提取默认值作为历史数据
			$history = $this->serv->fetch_all_field();
		}

		if (!isset($department['cd_displayorder'])) {
			// 如果提交的数据不包含显示顺序值，则认为其与历史数据一致
			$department['cd_displayorder'] = $history['cd_displayorder'];
		}
		if (!isset($department['cd_name'])) {
			// 如果提交的数据不包含部门名称，则认为其与历史数据一致
			$department['cd_name'] = $history['cd_name'];
		}

		// 发生改变的数据
		$update = array();
		$this->updated_fields($history, $department, $update);

		if (empty($update)) {
			$this->errmsg('1003', '数据未发生改变无须提交');
			return false;
		}

		/**
		 * 检查显示顺序取值是否合法
		 */
		if (isset($update['cd_displayorder'])) {
			$update['cd_displayorder'] = (int)$department['cd_displayorder'];
			if ($update['cd_displayorder'] < $this->department_displayorder[0] && $update['cd_displayorder'] > $this->department_displayorder[1]) {
				// 显示顺序取值超出范围
				$update['cd_displayorder'] = 99;
			}
		}

		/**
		 * 检查部门名称是否合法
		 */
		if (isset($update['cd_name'])) {
			$update['cd_name'] = (string)$department['cd_name'];
			$update['cd_name'] = trim($update['cd_name']);
			$update['cd_name'] = preg_replace('/\s+/s', '', $update['cd_name']);
			if (!$this->validator_length($update['cd_name'], $this->department_name_length)) {
				// 部门名称长度不合法
				$this->errmsg('1004', '部门名称：'.$this->error);
				return false;
			}
			if ($update['cd_name'] != rhtmlspecialchars($update['cd_name'])) {
				$this->errmsg('1005', '部门名称不能包含特殊字符');
				return false;
			}
			$so_dp = $this->serv->fetch_by_cd_name($update['cd_name']);
			if (!empty($so_dp) && $so_dp['cd_upid'] == $update['cd_upid']) {
				$this->errmsg('1006', '部门名称“'.$update['cd_name'].'”已被使用，请更换一个');
				return false;
			}
		}

		// 真实可靠的发生改变了的数据
		$updated = array();
		$this->updated_fields($history, $update, $updated);

		if (empty($updated)) {
			$this->errmsg('1007', '数据未发生改变无须提交');
			return false;
		}
		$update = $updated;
		// 加载微信通讯录接口
		$wxqy_addressbook = new voa_wxqy_addressbook();

		/** 取上级部门信息 */
		if (isset($department['cd_upid']) && 0 < $department['cd_upid']) {
			$uda_get = &uda::factory('voa_uda_frontend_department_get');
			$parent_dp = array();
			$uda_get->department($department['cd_upid'], $parent_dp);
			$update['cd_qywxparentid'] = empty($parent_dp) ? $wxqy_addressbook->department_parentid : (int)$parent_dp['cd_qywxid'];
		}

		// 微信接口需要的数据
		$wxqy_data = array();
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$ignore_qywx = empty($sets['ep_wxqy']);

		if ($this->local_to_wxqy($update, $wxqy_data) && !$ignore_qywx) {
			// 存在需要提交到微信接口的数据 且 未设置忽略微信接口提交

			if ($history['cd_qywxid']) {
				$wxqy_data['id'] = $history['cd_qywxid'];
			}

			// 连接接口创建部门
			$result = array();

			// 确定使用更新接口还是创建接口
			$class = $history['cd_id'] ? 'department_update' : 'department_create';

			if ($wxqy_addressbook->$class($wxqy_data, $result)) {
				// 连接成功返回

				if (empty($history['cd_id'])) {
					// 创建新部门

					// 转换微信企业的部门数据为本地格式，主要是获取微信企业部门id
					$this->wxqy_to_local($result, $update);
					$update['cd_qywxparentid'] = empty($update['cd_qywxparentid']) ? $wxqy_addressbook->department_parentid : $update['cd_qywxparentid'];
				} else {
					// 更新部门

					// 因为微信企业接口无部门信息返回，因此不做任何操作
				}

			} else {
				// 连接失败
				$this->errmsg('1008', '接口错误：'.$wxqy_addressbook->error_msg);
				return false;
			}

		}

		/**
		 * 提交数据更新
		 */
		if ($history['cd_id']) {
			// 更新部门信息
			$this->serv->update($update, $history['cd_id']);
			$this->errmsg(0, '编辑部门信息操作完毕');
		} else {
			// 新增部门
			$update['cd_id'] = $this->serv->insert($update, true);
			$this->errmsg(0, '新增部门信息操作完毕');
		}

		$update = array_merge($department, $update);

		// 更新缓存
		parent::update_cache();

		return true;
	}

	/**
	 * 更新部门显示顺序
	 * @param array $displayorder
	 * @return boolean
	 */
	public function displayorder_update($displayorder) {

		$displayorder = rintval($displayorder, true);

		$uda_get = &uda::factory('voa_uda_frontend_department_get');
		$list = array();
		$uda_get->list_all($list);

		$update = array();
		foreach ($list as $cd_id => $cd) {
			if (!isset($displayorder[$cd_id]) || $displayorder[$cd_id] == $cd['cd_displayorder']) {
				continue;
			}
			$value = $displayorder[$cd_id];
			if ($value >= $this->department_displayorder[0] && $value <= $this->department_displayorder[1]) {
				$update[$value][$cd_id] = $cd_id;
			}
		}

		if (empty($update)) {
			$this->errmsg('2001', '数据未更新无须提交');
			return false;
		}

		try {

			$this->serv->begin();

			foreach ($update as $_displayorder => $cd_ids) {
				$this->serv->update(array('cd_displayorder' => $_displayorder), $cd_ids);
			}

			$this->serv->commit();

		} catch (Exception $e) {
			$this->serv->rollback();
			$this->errmsg(2002, '更新部门排序发生数据错误');
			return false;
		}

		// 更新缓存
		parent::update_cache();

		return true;
	}

	/**
	 * 更新部门的成员数
	 * <p style="color:red">该方法<strong>应该在 编辑/添加 成员后</strong>使用，而不是在编辑/添加 成员前使用</p>
	 * @param number $cd_id 部门id
	 * @param number $change_number 成员计数改变值，默认为：1。
	 * <p>正整数为增加，负整数为减少，如果为 0 则强制重新计算
	 * 这将会重新统计该部门的实际用户数，其他则只是简单的 +$change_number 或 -$change_number</p>
	 * @return boolean
	 */
	public function update_usernum($cd_id, $change_number = 1) {

		if ($change_number > 0) {
			// 增加数值

			$this->serv->increase_usernum_by_cd_id($cd_id, abs($change_number));

		} elseif ($change_number < 0) {
			// 减少数值

			$this->serv->decrease_usernum_by_cd_id($cd_id, abs($change_number));

		} else {
			// 强制写入自member表进行统计后的数值

			// 统计member表该部门下的成员数
			$serv_member = &service::factory('voa_s_oa_member_department');
			$usernum = $serv_member->count_by_cdid($cd_id);

			// 更新数据
			$this->serv->update(array('cd_usernum' => rintval($usernum, false)), $cd_id);
		}

		return true;
	}

	/**
	 * 将本地数据转换为企业微信通讯录接口需要的数据格式
	 * @param array $department 本地数据
	 * @param array $wxqy_data <strong style="color:red">(引用结果)</strong>转换后的数据
	 * @param number $parentid 父亲部门id
	 * @return boolean
	 */
	public function local_to_wxqy($department = array(), &$wxqy_data = array()) {
		if (!isset($department['cd_name'])) {
			// 部门名称不需要更新，则不生成数据
			$wxqy_data = array();
			return false;
		}

		/** 取微信企业号中的上级部门id */
		$parentid = isset($department['cd_qywxparentid']) ? (int)$department['cd_qywxparentid'] : 0;

		$wxqy_data = array(
			'name' => $department['cd_name'],
			'parentid' => $parentid
		);
		if (!empty($department['cd_qywxid'])) {
			// 如果本地数据存在部门id
			$wxqy_data['id'] = $department['cd_qywxid'];
		}

		return true;
	}

	/**
	 * 将微信企业接口的数据转换为本地数据格式
	 * @param array $wxqy_data 微信传回的数据
	 * @param array $department <strong style="color:red">(引用结果)</strong>转换后的数据
	 * @return boolean
	 */
	public function wxqy_to_local($wxqy_data = array(), &$department = array()) {
		if (isset($wxqy_data['id'])) {
			$department['cd_qywxid'] = $wxqy_data['id'];
		}
		if (isset($wxqy_data['name'])) {
			$department['cd_name'] = $wxqy_data['name'];
		}
		if (isset($wxqy_data['parentid'])) {
			$department['cd_qywxparentid'] = $wxqy_data['parentid'];
		}
		return true;
	}
}
