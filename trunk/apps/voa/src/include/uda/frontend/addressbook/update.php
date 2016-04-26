<?php
/**
 * vao_uda_frontend_addressbook_update
 * 统一数据访问/通讯录/更新
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_addressbook_update extends voa_uda_frontend_addressbook_base {

	public function __construct() {
		parent::__construct();
	}


	public function update($history, &$submit, &$addressbook) {

		/**
		 * 初步判断，姓名、手机号、部门，是否填写或选择
		 */
		if (empty($submit['cab_mobilephone'])) {
			$this->errmsg(1001, '手机号码必须提供');
			return false;
		}
		if (empty($submit['cd_id']) && empty($submit['cd_name'])) {
			$this->errmsg(1002, '部门不能为空');
			return false;
		}

		// 检查并尝试添加部门
		if (!isset($submit['cd_id'])) {
			$submit['cd_id'] = 0;
		}
		$this->check_department($submit['cd_id'], isset($submit['cd_name']) ? $submit['cd_name'] : '');

		// 确定是新增 还是 编辑
		$cab_id = !empty($history['cab_id']) ? $history['cab_id'] : 0;
		if (!$cab_id) {
			$uda_get = &uda::factory('voa_uda_frontend_addressbook_get');
			$uda_get->addressbook($cab_id, $history);
		}

		if (empty($history['cab_id'])) {
			// 如果是新增通讯录

			if (empty($submit['cab_realname'])) {
				$this->errmsg(1001, '姓名必须要填写');
				return false;
			}
		} else {

			// 因为禁止编辑姓名，所以此处移除姓名字段的提交
			unset($submit['cab_realname']);
		}

		// 检查并尝试添加职务
		if (isset($submit['cj_id']) || isset($submit['cj_name'])) {
			$this->check_job($submit['cj_id'], isset($submit['cj_name']) ? $submit['cj_name'] : '');
		}

		// 发生改变的数据
		$updated = array();
		$this->updated_fields($history, $submit, $updated);
		if (empty($updated)) {
			$this->errmsg(1006, '数据未发生改变无须提交');
			return false;
		}

		/**
		 * 验证各个输入项目
		 */
		$fields = array(
			'cab_realname', 'cab_mobilephone',
			'cab_email', 'cab_qq', 'cab_weixinid',
			'cab_gender', 'cab_number', 'cab_address', 'cab_idcard', 'cab_active',
			'cab_telephone',
			'cab_birthday', 'cab_remark'
		);
		foreach ($fields as $key) {
			if (isset($updated[$key])) {
				$method_name = str_replace('cab_', 'validator_', $key);
				if (!$this->$method_name($updated[$key], $cab_id)) {
					// 验证不通过
					return false;
				}
			}
		}

		if (empty($updated)) {
			// 未发生改变，直接返回
			return true;
		}

		if (isset($updated['cab_realname'])) {
			// 如果姓名发生改动，则更新其字母索引
			$pinyin = new pinyin();
			$updated['cab_index'] = $pinyin->to_ucwords_first($updated['cab_realname'], 4);
		}

		if (empty($history['cab_id'])) {
			// 如果是新增，则生成当前用户的唯一标识符

			$userid = '';
			$this->userid($updated['cab_realname'], $userid);
			$updated['m_openid'] = $userid;
		} else {
			// 如果是编辑，则使用原有的标识符

			$updated['m_openid'] = $history['m_openid'];
		}

		// 链接微信接口
		$qywx_addressbook = new voa_wxqy_addressbook();

		if (array_intersect(array_keys($this->user_field_map), array_keys($updated))) {
			// 检查更新的数据里是否有需要提交到企业微信接口的字段

			$qywx_data = $updated;

			// 构造微信接口需要的数据
			if (!$this->local_to_wxqy($updated, $qywx_data)) {
				return false;
			}

			if (!empty($qywx_data)) {
				// 存在待提交给企业微信接口的数据

				$result = array();
				if (empty($history['cab_id'])) {
					// 调用新增接口
					if (!$qywx_addressbook->user_create($qywx_data, $result)) {
						// 与接口通讯失败
						$this->errmsg(1007, $qywx_addressbook->error_msg);
						return false;
					}
				} else {
					// 调用更新接口
					if (!$qywx_addressbook->user_update($qywx_data, $result)) {
						// 与接口通讯失败
						$this->errmsg(1007, $qywx_addressbook->error_msg);
						return false;
					}
				}
			}
		}

		// @ 用户表member，发生变动的字段的数据
		$member_updated = array();
		if (!empty($history['m_uid']) && ($_member_fields = array_intersect(array_keys($updated), array_keys($this->member_field_map)))) {
			// 如果是编辑通讯录信息且当前通讯录信息已关联了用户表，同时，当前变更的信息包含用户表的字段信息，则尝试找到member表需要更新的数据

			foreach ($_member_fields as $key) {
				if (isset($updated[$key])) {
					$member_updated[$this->member_field_map[$key]] = $updated[$key];
				}
			}
		}

		$member_serv = &service::factory('voa_s_oa_member');

		/** 更新本地库 可能包含同步更新member */
		$msg = '未知错误';
		try {

			$this->serv->begin();

			if (empty($history['cab_id'])) {
				// 新增

				$this->serv->insert($updated, true);
				$msg = '新增通讯录信息操作完毕';
			} else {
				// 更新

				$this->serv->update($updated, $history['cab_id']);
				if (!empty($member_updated)) {
					$member_serv->update($member_updated, $history['m_uid']);
				}
				$msg = '更新通讯录信息操作完毕';
			}

			$this->serv->commit();

		} catch (Exception $e) {
			$this->serv->rollback();

			$this->errmsg(1010, empty($history['cab_id']) ? '添加新通讯录操作失败' : '更新通讯录操作失败');
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
			return false;
		}

		/** by zhuxun begin */
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$mail = &uda::factory('voa_uda_uc_mailcloud_insert');
		$mail->send_reg_mail(array($updated['cab_email']), '邀请您加入企业号', array(
			'%sitename%' => array($sets['sitename']),
			'%username%' => array($updated['cab_realname']),
			'%qrcode%' => array('<img src="'.$sets['qrcode'].'" width=200 />')
		), $sets['sys_email_account'], $sets['sys_email_user']);
		/** by zhuxun end */

		$this->errmsg(0, $msg);

		return true;
	}

	/**
	 * 利用姓名来构造一个用户微信标识ID字符串
	 * @param string $realname 姓名
	 * @param string $userid <strong>(引用结果)</strong> 生成的唯一标识符userid /openid
	 * @return string
	 */
	public function userid($realname = '', &$userid = '') {
		$userid = md5(mt_rand(1, 999999).$realname.time().mt_rand(1, 999999));
		return true;
	}

	/**
	 * 将本地数据字段 转换为企业微信接口需要的数据
	 * @param array $local 本地数据格式
	 * @param array $wxqy <strong style="color:red">(返回结果)</strong> 企业微信数据格式
	 */
	public function local_to_wxqy($local, &$wxqy) {

		$wxqy = array();
		foreach ($this->user_field_map as $local_field => $wxqy_field) {
			if (!isset($local[$local_field])) {
				// 未定义数据则忽略
				continue;
			}

			if ($wxqy_field == 'department') {
				// 处理部门数据

				$department = array();
				$department_uda_get = &uda::factory('voa_uda_frontend_department_get');
				$department_uda_get->department($local['cd_id'], $department);
				if (empty($department['cd_qywxid'])) {
					// 无法获取到本地部门对应的企业微信部门的id

					// 则尝试添加
					$qywx_addressbook = new voa_wxqy_addressbook();
					$post_data = array();
					$new_department = array();
					$department_uda_update = uda::factory('voa_uda_frontend_department_update');
					$department_uda_update->local_to_wxqy($department, $post_data, $qywx_addressbook->department_parentid);

					if ($qywx_addressbook->department_create($post_data, $new_department)) {
						// 提交到微信接口获取id

						// 更新本地数据表
						$_update = array();
						$department_uda_update->update($department, array('cd_name' => $department['cd_name'], 'cd_qywxid' => $new_department['id']), $_update, true);
						$department['cd_qywxid'] = $new_department['id'];
					} else {
						$this->errmsg(1009, '部门ID获取失败');
						return false;
					}
				}
				$wxqy['department'] = $department['cd_qywxid'];

			} elseif ($wxqy_field == 'position') {
				// 处理职位数据

				if ($local['cj_id']) {
					$job = array();
					$job_uda_get = &uda::factory('voa_uda_frontend_job_get');
					$job_uda_get->job($local['cj_id'], $job);
					if (empty($job)) {
						// 无法获取到职位名称
						$this->errmsg(1010, '获取职位名称失败');
						return false;
					}
					$job_name = $job['cj_name'];
				} else {
					$job_name = '';
				}
				$wxqy['position'] = $job_name;

			} elseif ($wxqy_field == 'gender') {
				// 处理性别数据

				$wxqy['gender'] = isset($this->local2qywx_gender_map[$local['cab_gender']]) ? $this->local2qywx_gender_map[$local['cab_gender']] : 0;

			} else {
				// 其他可直接利用的字段
				$wxqy[$wxqy_field] = $local[$local_field];
			}
		}

		if (!isset($wxqy['userid'])) {
			$this->errmsg(1011, '无法获取到员工唯一标识符');
			return false;
		}

		return true;
	}

	/**
	 * 企业微信成员数据转换为本地数据
	 * @param array $wxqy
	 * @param array $local
	 * @return boolean
	 */
	public function wxqy_to_local($wxqy, &$local) {

		if (empty($this->_wxqy_to_local_field_map)) {
			// 如果未定义内部属性
			$this->_wxqy_to_local_field_map = array_flip($this->user_field_map);
		}

		foreach ($this->_wxqy_to_local_field_map as $wxqy_field => $local_field) {
			if (!isset($wxqy[$wxqy_field])) {
				// 未定义字段，则忽略
				continue;
			}

			$value = $wxqy[$wxqy_field];

			if ($wxqy == 'department') {
				// 获取部门

				if ($value) {
					// 指定了部门，试图通过本地记录的企业微信部门id来找到本地部门id
					$department_uda_get = &uda::factory('voa_uda_frontend_department_get');
					$department = array();
					if ($department_uda_get->get_by_qywxid($value, $department)) {
						$local[$local_field] = $department['cd_id'];
					} else {
						// 没有获取到部门id，则认为本地可能不存在该部门
						// 需要添加本地的该部门信息，这里就需要链接企业微信接口来获取
						// 可能会有一些问题，暂时不考虑此种意外情况
					}
				}

			} elseif ($wxqy_field == 'position') {
				// 职位

				$cj_id = 0;
				if ($value) {
					// 职位不为空，则通过职位名找到到其id
					$job_uda_get = &uda::factory('voa_uda_frontend_job_get');
					$job_uda_get->get_cj_id_by_name($value, $cj_id);
					if (!$cj_id) {
						// 如果找不到其id，则试图添加
						$job_uda_update = &uda::factory('voa_uda_frontend_job_update');
						$new = array();
						if ($job_uda_update->update(array(), array('cj_name' => $value), $new)) {
							$cj_id = $new['cj_id'];
						}
					}
				}
				$local[$local_field] = $cj_id;

			} elseif ($wxqy_field == 'gender') {
				// 性别
				$local[$local_field] = isset($this->qywx2local_gender_map[$value]) ? $this->qywx2local_gender_map[$value] : 0;
			} else {
				// 其他可直接使用的数据
				$local[$local_field] = $value;
			}
		}

		if (empty($local)) {
			$this->errmsg(10010, '无法获取成员数据');
			return false;
		}

		return true;
	}

}
