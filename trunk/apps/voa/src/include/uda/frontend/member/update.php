<?php
/**
 * voa_uda_frontend_member_update
 * 更新用户相关信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_update extends voa_uda_frontend_member_base {

	public function __construct() {
		parent::__construct();
	}

    /**
     * 更新用户
     * @param $submit 提交的数据
     * @param $member 引用返回
     * @return bool true|false
     */
    public function update($submit, &$member, $mp_ids = array(), $sync_qywx = true)
    {
        $this->update_cj_name($submit);

        $o_member = array();
        $this->get_o_member($submit, $o_member);

        $updated = array();
        $this->_filter_for_update($submit, $o_member, $updated);

        $member_serv = &service::factory('voa_s_oa_member');

        /** 如果没有变动, 则 */
        if (empty($updated)) {
            !empty($o_member['m_uid']) && $member_serv->update(array(), $o_member['m_uid']);
            return true;
        }

        if (empty($o_member['m_uid'])) {
            $updated['m_uid'] = 0;
        } else {
            $updated['m_uid'] = $o_member['m_uid'];
        }

        /** 验证用户信息 */
        if (!$this->_check_member($updated, $member)) {
            return false;
        }

        $member_field = array();
        /** 验证用户扩展信息 */
        if (!$this->_check_member_field($updated, $member_field)) {
            return false;
        }
        /** 获取 openid */
        if (empty($o_member['m_uid'])) {
            // 如果是新增，则生成当前用户的唯一标识符
	        if (empty($submit['m_openid'])) {
		        $userid = '';
		        $this->_make_userid($member['m_username'], $userid);
		        $member['m_openid'] = $userid;
	        } else {
				$member['m_openid'] = $submit['m_openid'];
	        }

            $member['m_active'] = voa_d_oa_member::ACTIVE_YES;
            // 获取姓名的首字母组合
            $member['m_index'] = '';
            $this->get_username_index($member['m_username'], $member['m_index']);

            $password = 'vchangyi';
            //使用手机后6位为密码
            if (!empty($member['m_mobilephone'])) {
                $password = md5(substr($member['m_mobilephone'], -6));
            }
            //使用邮箱为密码
            elseif (!empty($member['m_email'])) {
                $password = md5($member['m_email']);
            }
            //使用微信id为密码
            elseif (!empty($member['m_weixin'])) {
                $password = md5($member['m_weixin']);
            }
            list($member['m_password'], $member['m_salt']) = voa_h_func::generate_password($password, null, false);

        } else {
            // 如果是编辑，则使用原有的标识符
            $member['m_openid'] = $o_member['m_openid'];
            $member['m_uid'] = $o_member['m_uid'];
			if (isset($member['m_username'])) {
				// 获取姓名的首字母组合
				$member['m_index'] = '';
				$this->get_username_index($member['m_username'], $member['m_index']);
			}
        }

        //用户默认部门
        $cd_ids = array();
        if (!empty($member['cd_id']) && is_array($member['cd_id'])) {
            $cd_ids = $member['cd_id'];
            $member['cd_id'] = current($member['cd_id']);
        }

        if (!empty($submit['m_avatar'])) {
	    	$member['m_face'] = $submit['m_avatar'];
        }

        return $this->update_data_operation($o_member, $member, $member_field, $cd_ids, $mp_ids, $sync_qywx);
    }

    /**
     * 更新数据操作
     * @param $o_member 旧数据
     * @param $member 新数据
     * @param $member_field 新的用户扩展数据
     * @return bool true|false
     */
    public function update_data_operation($o_member, &$member, $member_field, $cd_ids = array(), $mp_ids = array(), $sync_qywx) {
        $qywx_addressbook = new voa_wxqy_addressbook();
        $member_serv = &service::factory('voa_s_oa_member');
        $member_f_serv = &service::factory('voa_s_oa_member_field');

        //组织微信数据
        $wx_data = array();
        $tmp_cd_id = $member['cd_id'];
        $member['cd_id'] = $cd_ids;
        $this->local_to_wxqy($member, $wx_data);
        $member['cd_id'] = $tmp_cd_id;

        /** 更新本地库 可能包含同步更新member */
        $msg = '未知错误';
        try {
            $member_serv->begin();

            if (empty($o_member['m_uid'])) {
                // 新增
                if (empty($member['m_qywxstatus'])) {
                    $member['m_qywxstatus'] = voa_d_oa_member::WX_STATUS_UNFOLLOW;
                }
                $member['m_uid'] = $member_serv->insert($member, true);
                $member_field['m_uid'] = $member['m_uid'];
                $member_f_serv->insert($member_field);
                // 添加到搜索表
                $this->member_search_update($member['m_uid'], array_merge($o_member, $member));
                // 添加到用户与部门对应表
                $this->member_department_update($member['m_uid'], $cd_ids, $mp_ids);
                $result = array();
                if ($sync_qywx === true && !$qywx_addressbook->user_create($wx_data, $result)) {
                    $member_serv->rollback();
                    $this->errmsg($qywx_addressbook->errcode, $qywx_addressbook->errmsg);
                    return false;
                }
                $msg = '新增通讯录信息操作完毕';
            } else {
                // 更新
                $member_serv->update($member, $o_member['m_uid']);
                if (empty($o_member['mf_created'])) {
                    // 扩展表数据不存在
                    $member_f_serv->insert($member_field, false, true);
                } else {
                    $member_f_serv->update($member_field, array('m_uid' => $o_member['m_uid']));
                }

                // 添加到搜索表
                $this->member_search_update($member['m_uid'], array_merge($o_member, $member));
                if ($cd_ids) {
                    // 添加到用户与部门对应表
                    $this->member_department_update($member['m_uid'], empty($cd_ids) ? $o_member['cd_id'] : $cd_ids, $mp_ids);
                }
                $result = array();
                if ($sync_qywx === true && !$qywx_addressbook->user_update($wx_data, $result)) {
                    $member_serv->rollback();
                    $this->errmsg($qywx_addressbook->errcode, $qywx_addressbook->errmsg);
                    return false;
                }
                $msg = '更新通讯录信息操作完毕';
            }

            $member_serv->commit();
        } catch (Exception $e) {
            $member_serv->rollback();

            $this->errmsg(1010, empty($o_member['m_uid']) ? '添加新通讯录操作失败' : '更新通讯录操作失败');
            logger::error($e);
            //throw new controller_exception($e->getMessage(), $e->getCode());
            return false;
        }

        $this->errmsg(0, $msg);

        return true;
    }


    /**
     * 获取用户旧的数据
     * @param $submit
     * @param $o_member
     */
    public function get_o_member($submit, &$o_member) {
        if (!empty($submit['m_uid'])) {
            $uda_get = &uda::factory('voa_uda_frontend_member_get');
            $uda_get->member_by_uid($submit['m_uid'], $o_member, true);
        } else {
            $o_member = array();
        }
    }

    /**
     * 更新职位名称
     * @param $submit
     */
    public function update_cj_name(&$submit) {
        /** 如果有职位信息, 则 */
        $cj_name = isset($submit['cj_name']) ? (string)$submit['cj_name'] : '';
        $cj_name = trim($cj_name);
        if (!empty($cj_name) && empty($submit['cj_id'])) {
            $serv_job = &service::factory('voa_s_oa_common_job');
            $job_info = $serv_job->fetch_by_cj_name($cj_name);
            if (empty($job_info)) {
                $uda_job = &uda::factory('voa_uda_frontend_job_update');
                $job_new = array('cj_name' => $cj_name, 'cj_displayorder' => 99);
                $job_his = array();
                $job_info = array();
                if ($uda_job->update($job_his, $job_new, $job_info)) {
                    $submit['cj_id'] = $job_info['cj_id'];
                }
            } else {
                $submit['cj_id'] = $job_info['cj_id'];
            }
        }
    }

	/**
     * 弃用的更新方法
	 * 新增或更新用户信息
	 * @param array $meminfo 用户信息(旧)
	 * @param array $submit form 表单数据
	 * @param array $member 用户信息(新)
	 */
	public function deprecated_update($o_meminfo, $submit, &$member, &$member_field) {

		/** 如果有职位信息, 则 */
		$cj_name = isset($submit['cj_name']) ? (string)$submit['cj_name'] : '';
		$cj_name = trim($cj_name);
		if (!empty($cj_name) && empty($submit['cj_id'])) {
			$serv_job = &service::factory('voa_s_oa_common_job');
			$job_info = $serv_job->fetch_by_cj_name($cj_name);
			if (empty($job_info)) {
				$uda_job = &uda::factory('voa_uda_frontend_job_update');
				$job_new = array('cj_name' => $cj_name, 'cj_displayorder' => 99);
				$job_his = array();
				$job_info = array();
				if ($uda_job->update($job_his, $job_new, $job_info)) {
					$submit['cj_id'] = $job_info['cj_id'];
				}
			} else {
				$submit['cj_id'] = $job_info['cj_id'];
			}
		}

		$this->_params = $submit;

		$member_serv = &service::factory('voa_s_oa_member');
		$updated = array();
		$this->_filter_for_update($submit, $o_meminfo, $updated);
		/** 如果没有变动, 则 */
		if (empty($updated)) {
			!empty($o_meminfo['m_uid']) && $member_serv->update(array(), $o_meminfo['m_uid']);
			return true;
		}

		/** 验证用户信息 */
		if (!$this->_check_member($updated, $member)) {
			return false;
		}

		/** 验证用户扩展信息 */
		if (!$this->_check_member_field($updated, $member_field)) {
			return false;
		}

		/** 获取 openid */
		if (empty($o_meminfo['m_uid'])) {
			// 如果是新增，则生成当前用户的唯一标识符
			$userid = '';
			$this->_make_userid($member['m_username'], $userid);
			$member['m_openid'] = $userid;
		} else {
			// 如果是编辑，则使用原有的标识符
			$member['m_openid'] = $o_meminfo['m_openid'];
			$member['m_uid'] = $o_meminfo['m_uid'];
		}

		// 链接微信接口
		/**if (!$this->_update_wxqy_user($o_meminfo, $member, $member_field)) {
			return false;
		}*/

		$qywx_addressbook = new voa_wxqy_addressbook();
		$member_serv = &service::factory('voa_s_oa_member');
		$member_f_serv = &service::factory('voa_s_oa_member_field');


		/** 更新本地库 可能包含同步更新member */
		$msg = '未知错误';
		try {
			$member_serv->begin();

			if (empty($o_meminfo['m_uid'])) {
				// 新增
				$member['m_uid'] = $member_serv->insert($member, true);
				$member_field['m_uid'] = $member['m_uid'];
				$member_f_serv->insert($member_field);
				// 添加到搜索表
				$this->member_search_update($member['m_uid'], array_merge($o_meminfo, $member));
				// 添加到用户与部门对应表
				$this->member_department_update($member['m_uid'], empty($member['cd_id']) ? $o_meminfo['cd_id'] : $member['cd_id']);
				$msg = '新增通讯录信息操作完毕';
			} else {
				// 更新
				$member_serv->update($member, $o_meminfo['m_uid']);
				if (empty($o_meminfo['mf_created'])) {
					// 扩展表数据不存在
					$member_f_serv->insert($member_field, false, true);
				} else {
					$member_f_serv->update($member_field, array('m_uid' => $o_meminfo['m_uid']));
				}

				// 添加到搜索表
				$this->member_search_update($member['m_uid'], array_merge($o_meminfo, $member));
				// 添加到用户与部门对应表
				$this->member_department_update($member['m_uid'], empty($member['cd_id']) ? $o_meminfo['cd_id'] : $member['cd_id']);
				$msg = '更新通讯录信息操作完毕';
			}

			$member_serv->commit();
		} catch (Exception $e) {
			$member_serv->rollback();

			$this->errmsg(1010, empty($o_meminfo['m_uid']) ? '添加新通讯录操作失败' : '更新通讯录操作失败');
			logger::error($e);
			//throw new controller_exception($e->getMessage(), $e->getCode());
			return false;
		}

		$this->errmsg(0, $msg);

		return true;
	}

	protected function _check_member($updated, &$member) {

		/** 用户信息验证规则 */
		$options = array(
			'm_openid' => 'check_member_openid',
			'm_username' => 'check_member_username',
			'm_email' => 'check_member_email',
			'm_mobilephone' => 'check_member_mobilephone',
			'm_password' => 'check_member_password',
			'cd_id' => 'check_member_cd_id',
			'cj_id' => 'check_member_cj_id',
			'm_number' => 'check_member_number',
			'm_active' => 'check_member_active',
			'm_gender' => 'check_member_gender',
            'm_weixin' => 'check_member_weixinid',
            'm_displayorder' => 'check_member_displayorder',
			'm_qywxstatus' => '',
            'm_source' => ''
		);

		return $this->_check_t_field($updated, $options, $member);
	}

	protected function _check_member_field($updated, &$member) {

		$options = array(
			'mf_qq' => 'check_member_qq',
			//'mf_weixinid' => 'check_member_weixinid',
			'mf_telephone' => 'check_member_telephone',
			'mf_birthday' => 'check_member_birthday',
			'mf_address' => 'check_member_address',
			'mf_idcard' => 'check_member_idcard',
			'mf_remark' => 'check_member_remark',
            'mf_ext1' => '',
            'mf_ext2' => '',
            'mf_ext3' => '',
            'mf_ext4' => '',
            'mf_ext5' => '',
            'mf_ext6' => '',
            'mf_ext7' => '',
            'mf_ext8' => '',
            'mf_ext9' => '',
            'mf_ext10' => '',
		);

		return $this->_check_t_field($updated, $options, $member);
	}

	protected function _check_t_field($updated, $options, &$member) {

		/** 取数据 */
		$m_options = array();
		foreach ($options as $_k => $_v) {
			if (!isset($updated[$_k])) {
				continue;
			}

			$member[$_k] = $updated[$_k];
			if (empty($_v)) {
				continue;
			}

            if (in_array($_k, array('m_email', 'm_mobilephone', 'm_weixin'))) {
                $m_uid = $updated['m_uid'] ? $updated['m_uid'] : true;
                if (!$this->$_v($member[$_k], $m_uid)) {
                    return false;
                }
            }

			if (in_array($_k, array('cj_id', 'm_active')) && !$this->$_v($member[$_k])) {
				return false;
			}

            if ($_k == 'cd_id' && is_array($member[$_k])) {
                foreach ($member[$_k] as $cd_id) {
                    if (!$this->$_v($cd_id)) {
                        return false;
                    }
                }
            }

		}

		return true;
	}

	/**
	 * 为更新做过滤检查
	 * @param array $submit 提交的数据
	 * @param array $member 已存在的用户信息
	 * @param array $updated 变动的信息
	 * @return boolean
	 */
	protected function _filter_for_update($submit, $member, &$updated) {

		/** 所有字段 */
		$fields = array(
			'm_weixin', 'm_username', 'm_password', 'm_mobilephone', 'm_gender', 'm_number', 'cd_id', 'cj_id',
			'm_qywxstatus', 'm_displayorder', 'm_email', 'm_active', 'm_gender', 'm_source', 'm_openid',
			'mf_weixinid', 'mf_telephone', 'mf_birthday', 'mf_address', 'mf_idcard', 'mf_remark', 'mf_qq',
			'mf_ext1', 'mf_ext2', 'mf_ext3',
            'mf_ext4', 'mf_ext5', 'mf_ext6', 'mf_ext7', 'mf_ext8', 'mf_ext9', 'mf_ext10'
		);

		/** 判断哪些属性有变动 */
		foreach ($fields as $_f) {
			if (isset($member[$_f]) && isset($submit[$_f]) && $submit[$_f] == $member[$_f]) {
				continue;
			}

			if ('m_password' == $_f && !empty($submit[$_f])) {
				list($passwd, $salt) = voa_h_func::generate_password($submit[$_f], $member['m_salt'], true);
				if ($passwd == $member['m_password']) {
					continue;
				}

				$updated['m_password'] = $passwd;
				$updated['m_salt'] = $salt;
				continue;
			}

			if ('m_username' == $_f && empty($submit[$_f])) {
				continue;
			}

			if ('mf_birthday' == $_f && empty($submit[$_f]) && !empty($member[$_f]) && '0000-00-00' == $member[$_f]) {
				continue;
			}
			if (isset($submit[$_f])) {
				$updated[$_f] = $submit[$_f];
			}
		}

		return true;
	}

	/**
	 * 更新微信企业号用户信息
	 * @param unknown $member
	 * @param unknown $member_field
	 * @return boolean
	 */
	protected function _update_wxqy_user($o_meminfo, $member, $member_field) {

		// 连接企业微信接口进行添加
		if (!$this->use_qywx) {
			return true;
		}

		$mmf = array_merge($member, $member_field);
		/** 剔除未更改数据 */
		$updated = array(
			'm_openid' => $member['m_openid']
		);
		foreach ($mmf as $_k => $_v) {
			if ($_v == $o_meminfo[$_k]) {
				continue;
			}

			$updated[$_k] = $_v;
		}

		$qywx_addressbook = new voa_wxqy_addressbook();
		if (!array_intersect(array_keys($this->_local_to_qywx_field_map), array_keys($updated))) {
			return true;
		}

		// 检查更新的数据里是否有需要提交到企业微信接口的字段
		$qywx_data = array();
		// 构造微信接口需要的数据
		if (!$this->local_to_wxqy(array_merge($o_meminfo, $updated), $qywx_data)) {
			return false;
		}

		if (empty($qywx_data)) {
			return true;
		}

		// 存在待提交给企业微信接口的数据
		$result = array();
		if (empty($member['m_uid'])) {
			// 调用新增接口
			if (!$qywx_addressbook->user_create($qywx_data, $result)) {
				// 与接口通讯失败
				$this->errmsg(1007, $qywx_addressbook->errmsg);
				return false;
			}
		} else {
			// 调用更新接口
			if (!$qywx_addressbook->user_update($qywx_data, $result)) {
				// 与接口通讯失败
				$this->errmsg(1007, $qywx_addressbook->errmsg);
				return false;
			}
		}

		return true;
	}

	/**
	 * 更改用户密码
	 * @param number $m_uid
	 * @param string $new_password 新密码，可以是原文也可以是md5值
	 * @param string $is_original $new_password 是否为md5值
	 * @return boolean
	 */
	public function pwd_modify($m_uid, $new_password, $is_original = true) {

		// 储存在用户表的密码和盐值
		list($password, $salt) = voa_h_func::generate_password($new_password, '', $is_original, 6);

		$serv_member = &service::factory('voa_s_oa_member');
		$member = $serv_member->fetch($m_uid);
		if (empty($member)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_UDA_UPDATE_PWD_MODIFY_DATA_NULL, ' - '.$m_uid);
		}

		$serv_member->update(array(
			'm_password' => $password,
			'm_salt' => $salt
		), $m_uid);

		return true;
	}

	/**
	 * 更新用户搜索表数据
	 * @param number $m_uid
	 * @param array $member
	 * @return boolean
	 */
	public function member_search_update($m_uid, $member) {

		$search_data = '';
		$this->_make_member_search_data($member, $search_data);
		$ms = $this->serv_member_search->fetch_by_uid($m_uid);
		if ($ms) {
			// 存在搜索记录
			$this->serv_member_search->update(array(
				'm_uid' => $m_uid,
				'ms_message' => $search_data
			), "m_uid=".$m_uid);
		} else {
			$this->serv_member_search->insert(array(
				'm_uid' => $m_uid,
				'ms_message' => $search_data
			), "m_uid=".$m_uid);
		}

		return true;
	}

	/**
	 * 更新用户部门信息
	 * @param number $m_uid
	 * @param unknown $cd_id
	 * @return boolean
	 */
	public function member_department_update($m_uid = 0, $cd_id = array(), $mp_ids = array()) {

		// 需要关联的部门ID
		if (!is_array($cd_id)) {
			$ids = array($cd_id);
		} else {
			$ids = $cd_id;
		}
        //部门与职务对应
        $cd_mp_ids = array();
        foreach ($ids as $k => $cd_id) {
            if (isset($mp_ids[$k])) {
                $cd_mp_ids[$cd_id] = $mp_ids[$k];
            } else {
                $cd_mp_ids[$cd_id] = 0;
            }
        }

		// 检查关联的部门ID可用性
        // 过滤掉不存在的部门
		$uda_department = &uda::factory('voa_uda_frontend_department_get');
		$departments = array();
		$uda_department->get_by_cd_ids($ids, $departments);
		$department_ids = array();
		foreach ($departments as $d) {
			$department_ids[$d['cd_id']] = $d['cd_id'];
		}
		unset($departments);

		// 关联的所有部门
		$cd_ids = $this->serv_member_department->fetch_all_field_by_uid($m_uid);

		// 检查是否有需要新增的
		foreach ($ids as $id) {
			if (!isset($cd_ids[$id]) && isset($department_ids[$id])) {
				$this->serv_member_department->insert(array(
					'm_uid' => $m_uid,
					'cd_id' => $id,
                    'mp_id' => $cd_mp_ids[$id]
				));
			}
		}

		// 检查是否有需要删除的
		$delete_cd_ids = array();
		foreach ($cd_ids as $id => $cd) {
			if (!in_array($id, $ids) || !isset($department_ids[$id])) {
				$delete_cd_ids[] = $id;
			}
            //更新职务有变更的
            elseif ($cd['mp_id'] != $cd_mp_ids[$id]) {
                $this->serv_member_department->update(array(
                    'mp_id' => $cd_mp_ids[$id]
                ), $cd['md_id']);
            }

		}

		if (!empty($delete_cd_ids)) {
			$this->serv_member_department->delete_by_conditions(array(
				'm_uid' => $m_uid,
				'cd_id' => $delete_cd_ids
			));
		}

		return true;
	}

	/**
	 * 更新头像
	 * @param string $userid 用户的openid
	 * @param array $mem 用户信息
	 */
	public function update_avatar($userid, &$mem) {

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		/** 根据 openid 读取用户信息 */
		if (empty($mem) || $userid != $mem['m_openid']) {
			$mem = $servm->fetch_by_openid($userid);
		}

		/** 判断用户是否存在 */
		if (empty($mem)) {
			return false;
		} elseif (voa_d_oa_member::STATUS_REMOVE == $mem['m_status']) {
			$this->errmsg(102, '请先从后台进行同步');
			return false;
		}

		/** 根据时间间隔判断是否需要更新头像 */
		$update_face_interval = (int)$this->setting['update_face_interval'];
		$update_face_interval = 86400 > $update_face_interval ? 86400 : $update_face_interval;
		$facetime = (int)$mem['m_facetime'];
		if ($update_face_interval > startup_env::get('timestamp') - $facetime &&
			isset($mem['m_qywxstatus']) &&
			$mem['m_qywxstatus'] == voa_d_oa_member::WX_STATUS_FOLLOWED) {
			return false;
		}

		/** 读取用户在微信中的信息 */
		$userinfo = array();
		$addr_qy = new voa_wxqy_addressbook();
		$addr_qy->user_get($userid, $userinfo);

		/** 小头像(64) */
		$avatar = '';
		if ($userinfo['avatar'] && preg_match('/^http\s?\:\/\//i', $userinfo['avatar'])) {
			$avatar = preg_replace('/\/\d+$/i', '/', $userinfo['avatar']);
			if ('/' != substr($avatar, -1)) {
				$avatar .= '/';
			}
		}

		try {
			$servm->begin();

			/** 更新用户信息 */
			$data = array(
				'm_face' => $avatar,
				'm_qywxstatus' => $userinfo['status'],
				'm_facetime' => startup_env::get('timestamp')
			);
			$servm->update($data, $mem['m_uid']);
			$mem['m_face'] = $avatar;

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			$this->errmsg(100, 'update failed');
		}

		return true;
	}

	/**
	 * 关联绑定 uid 和 微信unionid
	 * @param number $uid
	 * @param string $unionid
	 * @return boolean
	 */
	public function bind_wechat($uid = 0, $unionid = '') {

		if (!$uid || $unionid == '') {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_WECHAT_ACCOUNT_NULL);
		}

		// 通过uid找到对应用户信息
		$member = $this->serv_member->fetch($uid);
		if (empty($member)) {
			// 找不到用户
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_WECHAT_UID_NOT_EXISTS);
		}

		if (!empty($member['m_unionid']) && $member['m_unionid'] == $unionid) {
			// 已经绑定过相同的微信帐号不用再次绑定
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_WECHAT_BIND_EQUALLY);
		}

		// 通过微信unionid找到用户信息
		$member = $this->serv_member->fetch_by_unionid($unionid);
		if (empty($member)) {
			// unionid未绑定过任何帐号，则进行关联绑定

			$this->serv_member->update(array('m_unionid' => $unionid), $uid);

		} else {
			// 已经绑定过

			if ($member['m_uid'] != $uid) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_WECHAT_BIND_NOT_UNIQUE);
			}

			// 已经绑定过相同用户不再重新绑定，直接返回
			return true;
		}

		return true;
	}

	/**
	 * 退出登录
	 * @return boolean
	 */
	public function member_logout($session) {
		foreach ($this->_cookie_names as $k) {
			$session->remove($k);
			$session->set($k, null, -3600);
		}

		return true;
	}

	/**
	 * 用户登录
	 * @param number $uid 登录用户的id
	 * @param array $cookie_names 定义cookie名
	 * + uid_cookie_name
	 * + lastlogin_cookie_name
	 * + auth_cookie_name
	 * @param number $device 登录设备 1=h5, 2=pc, 3=android, 4=ios
	 * @param array $result <strong style="color:red">(引用结果)</strong>返回的用户信息和cookie数据
	 * @return boolean
	 */
	public function member_login($uid, $device = '', &$result = array(), $cookie_names = array()) {

		if (empty($cookie_names)) {
			$cookie_names = $this->_cookie_names;
		}

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');
		// 获取用户信息
		$member = array();
		if (!$uda_member_get->member_by_uid($uid, $member, true)) {
			return false;
		}

		// 保存登陆的设备类型 start
		$devicetype = '';
		if (!empty($device)) {
			if ($device  == XingeApp::DEVICE_ANDROID) {
				$devicetype = XingeApp::DEVICE_ANDROID;
			} elseif ($device  == XingeApp::DEVICE_IOS) {
				$devicetype = XingeApp::DEVICE_IOS;
			} elseif ($device  == XingeApp::DEVICE_PC) {
				$devicetype = XingeApp::DEVICE_PC;
			} else {
				$devicetype = XingeApp::DEVICE_BROWSER;
			}

			if ($devicetype) {
				$_member = $_memberfield = array();
				$this->update($member, array('mf_devicetype'=>$devicetype), $_member, $_memberfield);
			}
		}
		// 保存登陆的设备类型 end

		// 最后登录时间
		$lastlogin = startup_env::get('timestamp');
		// cookie认证加密字符串
		$auth = $this->generate_auth($member['m_password'], $uid, $lastlogin);

		// 部门列表
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		// 职务列表
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');
		// 系统设置
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');

		//顶级部门id（公司id）
		$top_department_id = 0;
		foreach ($departments as $department) {
			if ($department['cd_upid'] == 0) {
				$top_department_id = $department['cd_id'];
				break;
			}
		}

		// 输出的用户信息
		$member_data = array(
			'department' => isset($departments[$member['cd_id']]) ? $departments[$member['cd_id']]['cd_name'] : '',
			'jobtitle' => isset($jobs[$member['cj_id']]) ? $jobs[$member['cj_id']]['cj_name'] : '',
			'openid' => $member['m_openid'],
			'alphaindex' => $member['m_index'],
			'uid' => $member['m_uid'],
			'wechatunionid' => $member['m_unionid'],
			'jobnumber' => $member['m_number'],
			'realname' => $member['m_username'],
			'mobilephone' => $member['m_mobilephone'],
			'address' => $member['mf_address'],
			'idcard' => $member['mf_idcard'],
			'gender' => $member['m_gender'],
			'active' => $member['m_active'],
			'telephone' => $member['mf_telephone'],
			'email' => $member['m_email'],
			'qq' => $member['mf_qq'],
			'weixinid' => $member['mf_weixinid'],
			'birthday' => $member['mf_birthday'],
			'departmentid' => $member['cd_id'],
			'jobid' => $member['cj_id'],
			'remark' => $member['mf_remark'],
			'qywxstatus' => $member['m_qywxstatus'],
			'face' => voa_h_user::avatar($uid, $member),
			'top_department_id' => $top_department_id,
			'enterprise' => array(
				'domain' =>$settings['domain'],
				'ep_id' => $settings['ep_id'],
				'name' => $settings['sitename'],
				'enumber' => preg_replace('/'.preg_quote('.'.config::get('voa.oa_top_domain')).'$/is', '', $settings['domain'])
			),
		);
		$result = array(
			'auth' => array(
				array(
					'name' => $cookie_names['uid_cookie_name'],
					'value' => $uid,
				),
				array(
					'name' => $cookie_names['lastlogin_cookie_name'],
					'value' => $lastlogin,
				),
				array(
					'name' => $cookie_names['auth_cookie_name'],
					'value' => $auth,
				)
			),
			'data' => $member_data,
			'unionlogin' => null,
		);

		return true;
	}

	/**
	 * 联合登录字符串加密或解密
	 * @param string $string 待处理的字符串
	 * @param string $method 加密还是解密。ENCODE=加密，DECODE=解密
	 * @param string $result <strong style="color:red">(引用结果)</strong>处理后的字符串
	 * @return boolean
	 */
	public function unionlogin_crypt($string = '', $method = 'ENCODE', &$result = '') {

		$key = 'A#09!G@wechat%&@unionid@#!9';
		$comma = "\t";

		$tea = new crypt_xxtea($key);
		$timestamp = startup_env::get('timestamp');
		if ($method == 'DECODE') {
			// 解密
			$data = rbase64_decode($string);
			$data = $tea->decrypt($data);
			if (!is_string($data)) {
				return $this->set_errmsg(voa_errcode_oa_member::UNIONID_DECODE_IS_NOT_STRING);
			}
			$data = explode($comma, $data);
			if (!isset($data[2])) {
				return $this->set_errmsg(voa_errcode_oa_member::UNIONID_DECODE_RANDOM_NOT_EXISTS);
			}
			if (!is_numeric($data[1]) || $timestamp - $data[1] > 7200) {
				return $this->set_errmsg(voa_errcode_oa_member::UNIONID_DECODE_TIMEOUT);
			}
			$result = $data[0];

		} else {
			// 加密
			$random = random(10);
			$result = $tea->encrypt("{$string}{$comma}{$timestamp}{$comma}{$random}");
			$result = rbase64_encode($result);
		}

		return true;
	}
}
