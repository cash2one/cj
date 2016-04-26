<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/18
 * Time: 下午3:21
 */

namespace PubApi\Controller\Apicp;

use Com;
use Common\Common\Pager;
use Common\Common\Wxqy;
use Common\Common\Department;


class MemberController extends AbstractController {

	const ACTIVE = 1; // 启用状态
	const UNACTIVE = 0; // 禁用状态
	/** 规则 (开启) */
	const ALLOW = 1;
	/** checkbox 规则 */
	const CHECK_OPEN = 'open';
	/** 用户账号使用状态 */
	protected $_active_status = array(
		self::ACTIVE,
		self::UNACTIVE,
	);
	public $mender = array('未知', '男', '女');

	/** 微信状态 */
	// 未关注
	const WX_STATUS_UNFOLLOW = 4;
	// 已关注
	const WX_STATUS_FOLLOWED = 1;
	// 已禁用
	const WX_STATUS_FREEZE = 2;
	protected $_wx_status = array(
		self::WX_STATUS_UNFOLLOW,
		self::WX_STATUS_FOLLOWED,
		self::WX_STATUS_FREEZE,
	);
	/** 是部门搜索人员 还是关键字搜搜人员 */
	const SEARCH_DEP = 1;
	const SEARCH_KEYWORD = 2;
	protected $_cdid_or_kw = self::SEARCH_DEP;

	/**
	 * 人员列表
	 * @return bool
	 */
	public function List_get() {

		// 获取部门id参数
		$cd_id = (int)I('get.cd_id');
		$kw = I('get.kw');
		$page = I('get.page');
		$limit = I('get.limit');
		$status = I('get.status');

		$condi = array();

		// 分 部门搜索 和 关键字搜索
		if (!empty($cd_id) && empty($kw)) {
			if ($cd_id < 1) {
				$this->_result = array();

				return true;
			}

			$this->_cdid_or_kw = self::SEARCH_DEP;
		} elseif (!empty($kw) && empty($cd_id)) {

			// 搜索
			$serv_search = D('Common/MemberSearch', 'Service');
			$mem_so_list = $serv_search->list_by_keyword($kw, 500);
			$m_uids = array_column($mem_so_list, 'm_uid');

			if (empty($m_uids)) {
				$this->_result = array();

				return true;
			}

			$condi['m_uid'] = $m_uids;
			$this->_cdid_or_kw = self::SEARCH_KEYWORD;
		}

		// 分页
		if (empty($page)) {
			$page = 1;
		}
		if (empty($limite)) {
			$limit = 10;
		}
		$start = ($page - 1) * $limit;

		$serv_mem = D('Common/Member', 'Service');

		// 判断是否查询关注状态
		if ($status !== '' && in_array($status, $this->_wx_status)) {
			switch ($status) {
				case self::WX_STATUS_FREEZE:
					$condi['m_active'] = self::UNACTIVE;
					break;
				case self::WX_STATUS_FOLLOWED:
					$condi['m_active'] = self::ACTIVE;
					$condi['m_qywxstatus'] = self::WX_STATUS_FOLLOWED;
					break;
				case self::WX_STATUS_UNFOLLOW:
					$condi['m_active'] = self::ACTIVE;
					$condi['m_qywxstatus'] = self::WX_STATUS_UNFOLLOW;
			}
		}

		// 如果根据部门查询
		if ($this->_cdid_or_kw == self::SEARCH_DEP) {

			// 判断部门是否存在，并且非全公司
			if ($this->_departments[$cd_id] && $this->_departments[$cd_id]['cd_upid'] != 0) {
				// 获取下面所有子部门
				$dp_ids = Department::instance()->list_childrens_by_cdid($cd_id, true);

				//获取部门数据
				$this->_member_in_dep($dp_ids, $m_uids);
				//获取成员数据
				if (empty($m_uids)) {
					$this->_result = array();

					return true;
				}
				$condi['m_uid'] = $m_uids;
			}
		}

		$members = $serv_mem->list_by_conds($condi, array($start, $limit));
		$count = $serv_mem->count_by_conds($condi);

		$this->_list_fm($members);
		$pages = ceil($count / $limit);

		$this->_result = array(
			'list' => $members,
			'count' => $count,
			'page' => $page,
			'pages' => $pages,
			'limit' => $limit,
		);

		return true;
	}

	/**
	 * 查看人员详情
	 * @return bool
	 */
	public function View_get() {

		$m_uid = I('get.m_uid');

		// 判断提交是否为空
		if (empty($m_uid)) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		}
		// 提交的id 不得为多个
		if (is_array($m_uid)) {
			E('_ERR_VIEW_UID_CAN_NOT_ARRAY');

			return false;
		}

		// 获取用户信息
		$serv_mem = D('Common/Member', 'Service');
		$user_data = $serv_mem->get_by_conds(array('m_uid' => $m_uid));
		$user_data = $this->_format_user_data($user_data);
		// 附属字段
		$this->_get_custom($m_uid, $custom);

		// 人员信息是否为空
		if (empty($user_data)) {
			E('_ERR_EMPTY_USER_DATA');

			return false;
		}

		// 返回的数据
		$result = array();
		$result['user_data'] = $user_data;
		$result['custom'] = $custom;

		$this->_result = $result;

		return true;
	}

	/**
	 * 删除人员接口
	 * @return bool
	 */
	public function Delete_post() {

		// 获取提交的ID
		$m_uids = I('post.');

		// 去重
		$m_uids = array_unique($m_uids);

		// 判断是否为空
		if (empty($m_uids)) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		} else {

			// 获取用户userid
			$serv_mem = D('Common/Member', 'Service');
			$user_data = $serv_mem->list_by_conds(array('m_uid' => $m_uids));
			if (empty($user_data)) {
				E('_ERR_EMPTY_POST_UID');

				return false;
			}

			// 没有成功的人员
			$_fail_data = '';
			// 删除操作
			foreach ($user_data as $_u_data) {
				if (!$serv_mem->delete_member($_u_data['m_openid'], $_u_data['m_uid'])) {
					$_fail_data .= $_u_data['m_username'] . ' ';
				};
			}

			// 更新部门人数
			$this->_update_department_num();
			// 删除失败报错
			if (!empty($_fail_data)) {
				E(L('_ERR_DELETE_FAIL', array('username' => $_fail_data)));
			}
		}

		$this->_result = array(
			'操作成功',
		);

		return true;
	}

	/**
	 * 邀请关注
	 * @return bool
	 */
	public function Invite_post() {

		$m_uid = I('post.m_uid');

		if (empty($m_uid)) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		}

		// 根据人员id 和 未关注状态 查询
		$serv_mem = D('Common/Member', 'Service');
		$users = $serv_mem->list_by_conds(array('m_uid' => $m_uid));
		if (empty($users)) {
			E('_ERR_EMPTY_USER_DATA');

			return false;
		}

		// 初始化微信接口方法
		//$qywx_ab = $this->_wxqy_addr();
		//$result = array();

		// 发送邀请邮件，不返回发送失败错误提示，因为不影响前台显示
		$send_mail = new Com\MailCloud();
		// 主题
		$subject = $this->_setting['sitename'] . cfg('SUBJECT_FOR_INVITE_FOLLOW');
		// 域名协议
		$scheme = cfg('PROTOCAL');
		// 模板名称
		$tpls = cfg('TPLS');
		// 邮件模板赋值
		$vars = array(
			'%sitename%' => array($this->_setting['sitename']),
			'%qrcode_url%' => array('<img src="' . $this->_setting['qrcode'] . '" />'),
			'%pc_url%' => array($scheme . $this->_setting['domain'] . '/pc'),
			'%download_url%' => array('<a href="' . $scheme . $this->_setting['domain'] . '/frontend/index/download">点击下载</a>'),
		);

		foreach ($users as $_user) {
			// 跳过已经关注的人
			if ($_user['m_qywxstatus'] == self::WX_STATUS_FOLLOWED) {
				continue;
			}

			if (!empty($_user['m_email'])) {
				$send_mail->send_tpl_mail($tpls['INVITE_FOLLOW'], array($_user['m_email']), $subject, $vars);
			} else {
                // 注意: 此功能微信已经停止使用
				//遍历用户根据open_id发送邀请
//				try {
//					$qywx_ab->user_invite($result, $_user['m_openid']);
//				} catch (\Think\Exception $e) {
//
//					if ($result['errcode'] == 60118) {
//						E('_ERR_ADD_EMAIL_BEFORE_INVITE');
//
//						return false;
//					}
//				}
			}
		}

		//输出结果
		$this->_result = array(
			'发送邀请成功',
		);

		return true;
	}

	/**
	 * 移动人员到别的部门
	 * @return bool
	 */
	public function Move_post() {

		$post = I('post.');

		// 判断提交的数据
		if (empty($post['m_uid'])) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		} elseif (empty($post['cd_id'])) {
			E('_ERR_EMPTY_CD_ID');

			return false;
		}

		$m_uid = $post['m_uid'];
		$cd_id = $post['cd_id'];

		// 获取员工信息
		$serv_mem = D('Common/Member', 'Service');
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		// 查询人员ID
		$user_data = $serv_mem->list_by_conds(array('m_uid' => $m_uid));

		// 判断人员是否为空
		if (empty($user_data)) {
			E('_ERR_EMPTY_POST_UID_OR_IN_NOW');

			return false;
		}

		// 微信部门ID
		$qywx_dep = array();
		foreach ($cd_id as $_dep) {
			$qywx_dep[] = (int)$this->_departments[$_dep]['cd_qywxid'];
			if (!isset($this->_departments[$_dep])) {
				E('_ERR_EMPTY_DEP_DATA');

				return false;
			}
		}

		// 更新至微信
		$qywx = &\Common\Common\Wxqy\Service::instance();
		$addrbook = new Wxqy\Addrbook($qywx);
		// 新人员部门关联数组
		$mem_dep = array();
		foreach ($user_data as $_data) {
			// 更新至微信
			$edit_data = array(
				'department' => $qywx_dep,
				'userid' => $_data['m_openid'],
			);

			// 如果失败
			if (!$addrbook->user_update($result, $edit_data)) {
				continue;
			};

			// 更新member表部门数据
			$serv_mem->update_by_conds(array('m_uid' => $_data['m_uid']), array('cd_id' => $cd_id[0]));
			// 删除原来关联
			$serv_mem_dep->delete_by_conds(array('m_uid' => $_data['m_uid']));
			// 写入关联数组
			foreach ($cd_id as $_dep) {
				$mem_dep[] = array(
					'cd_id' => (int)$_dep,
					'm_uid' => $_data['m_uid'],
				);
			}
		}

		// 写入关联表
		if (!empty($mem_dep)) {
			$serv_mem_dep->insert_all($mem_dep);
		}
		// 更新部门人数
		$this->_update_department_num();

		$this->_result = array(
			'操作成功',
		);

		return true;
	}

	/**
	 * 启用/禁用人员
	 * @return bool
	 */
	public function Ban_post() {

		$m_uid = I('post.m_uid');
		$active = I('post.active');

		// 判断提交值
		if (empty($m_uid) || is_array($m_uid)) {
			E('_ERR_EMPTY_AND_ISARRAY_UID');

			return false;
		}

		if (!in_array($active, $this->_active_status)) {
			E('_ERR_EMPTY_ACTIVE_STATUS');

			return false;
		}

		// 获取用户信息
		$serv_mem = D('Common/Member', 'Service');
		$user_data = $serv_mem->get_by_conds(array('m_uid' => $m_uid));

		// 用户信息不为空
		if (empty($user_data)) {
			E('_ERR_EMPTY_USER_DATA');

			return false;
		}

		$qywx = $this->_wxqy_addr();
		$qywx_data = array(
			'userid' => $user_data['m_openid'],
			'enable' => $active,
		);
		if (!$qywx->user_update($result, $qywx_data)) {
			E('_ERR_WXQY_UPDATE');

			return false;
		}
		// 更改状态
		$serv_mem->update_by_conds(array('m_uid' => $m_uid), array('m_active' => $active));

		$this->_result = array(
			'操作成功',
		);

		return true;
	}

	/**
	 * 人员浏览权限
	 * @return bool
	 */
	public function Browse_post() {

		// 功能暂去
		return true;
		$uids = I('post.m_uid');
		$mb_uids = I('post.mb_m_uid');
		$mb_cdids = I('post.mb_cd_id');

		// 要设置的人员ID不能为空
		if (empty($uids)) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		}
		// 权限范围不能为空
		if (empty($mb_uids) && empty($mb_cdids)) {
			E('_ERR_EMPTY_MB_UID_CDID');

			return false;
		}

		// 去重
		$mb_uids = array_unique($mb_uids);
		$mb_cdids = array_unique($mb_cdids);

		// 整理要入库的 权限ID
		$up_uids = implode(',', $mb_uids);
		$up_cdids = implode(',', $mb_cdids);
		if (empty($up_cdids)) {
			$up_cdids = '';
		}
		if (empty($up_uids)) {
			$up_uids = '';
		}

		// 查询已经有的权限设置
		$serv_mb = D('Common/MemberBrowsepermission', 'Service');
		$existent = $serv_mb->list_by_conds(array('m_uid' => $uids));

		// 查询是否 有需要更新
		if (!empty($existent)) {
			foreach ($existent as $_ex_data) {
				// 如果提交的uid 已经存在设置
				if (in_array($_ex_data['m_uid'], $uids)) {
					$up_conds = array('m_uid' => $_ex_data['m_uid']);
					$up_data = array('mb_m_uid' => $up_uids, 'mb_cd_id' => $up_cdids);
					$serv_mb->update_by_conds($up_conds, $up_data);

					// 剔除更新过的 uid
					$_key = array_search($_ex_data['m_uid'], $uids);
					unset($uids[$_key]);
				}
			}
		}

		// 新建权限范围
		if (!empty($uids)) {
			$temp = array();
			foreach ($uids as $_uid) {
				$temp[] = array(
					'm_uid' => $_uid,
					'mb_m_uid' => $up_uids,
					'mb_cd_id' => $up_cdids,
				);
			}

			$serv_mb->insert_all($temp);
		}

		$this->_result = array(
			'操作成功',
		);

		return true;
	}

	/**
	 * 添加人员
	 * @return bool
	 */
	public function Add_post() {

		$post = I('post.');

		$serv_mem = D('Common/Member', 'Service');
		$result = array();
		if ($serv_mem->add_member($post, $result) && isset($post['department'])) {
			// 更新部门人数
			$this->_update_department_num();
		};

		return true;
	}

	/**
	 * 编辑人员初始数据
	 * @return bool
	 */
	public function Getedit_get() {

		$m_uid = I('get.m_uid');
		if (empty($m_uid)) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		}

		$serv_mem = D('Common/Member', 'Service');
		$udata = $serv_mem->get_by_conds(array('m_uid' => $m_uid));
		if (empty($udata)) {
			E('_ERR_EMPTY_USER_DATA');

			return false;
		}
		$serv_mem_field = D('Common/MemberField', 'Service');
		$mem_field = $serv_mem_field->get_by_conds(array('m_uid' => $m_uid));

		// 获取人员属性规则
		$cache_field = $this->_get_field();

		$fixed = array();
		$custom = array();
		// 固定属性
		foreach ($cache_field['fixed'] as $_key => $_rule) {
			$fixed[$_key] = array(
				'name' => $_rule['name'],
				'value' => '',
				'open' => $_rule['open'],
				'number' => $_rule['number'],
				'required' => $_rule['required'],
			);
		}
		// 自定义属性
		foreach ($cache_field['custom'] as $_key => $_rule) {
			// 在自定义属性里去掉直属上级
			if ($_key == 'leader') {
				// 加入固定属性 leader
				$fixed['leader'] = array(
					'name' => '直属领导',
					'value' => '',
					'open' => $_rule['open'],
					'number' => $_rule['number'],
					'required' => $_rule['required'],
				);
				unset($cache_field['custom'][$_key]);
				continue;
			}
			// 如果是开启的
			if ($_rule['open'] == self::ALLOW) {
				$custom[$_key] = array(
					'name' => $_rule['name'],
					'value' => '',
					'open' => $_rule['open'],
					'number' => $_rule['number'],
					'required' => $_rule['required'],
				);
			}
		}

		// 数据库对应的微信端字段
		$field = array(
			'm_username' => 'name',
			'm_email' => 'email',
			'm_openid' => 'userid',
			'm_gender' => 'gender',
			'm_mobilephone' => 'mobile',
			'm_weixin' => 'weixinid',
		);

		// 匹配数据库对应的微信字段
		$new_udata = array();
		foreach ($udata as $_key => $_val) {
			if (isset($field[$_key])) {
				$new_udata[$field[$_key]] = $_val;
			}
		}

		// 赋默认值 (固定属性)
		foreach ($fixed as $_key => &$_rule) {
			if (!empty($new_udata[$_key])) {
				// 性别
				if ($_key == 'gender') {
					switch ($new_udata[$_key]) {
						case self::G_UNKNOWN :
							$_rule['value'] = self::C_G_UNKNOWN;
							break;
						case self::MALE :
							$_rule['value'] = self::C_MALE;
							break;
						case self::FMALE :
							$_rule['value'] = self::C_FMALE;
							break;
					}
					continue;
				}

				$_rule['value'] = $new_udata[$_key];
			}
		}

		// 部门 直属领导默认数据
		if (!empty($mem_field['mf_leader'])) {
			$leaders = explode(',', $mem_field['mf_leader']);
			$leaders_data = $serv_mem->list_by_conds(array('m_uid' => $leaders));
			if (!empty($leaders_data)) {
				foreach ($leaders_data as $_data) {
					$fixed['leader']['value'][] = array(
						'm_uid' => $_data['m_uid'],
						'm_username' => $_data['m_username'],
						'selected' => (bool)true,
					);
				}
			}
		}
		// 查询部门关联
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$mem_dep = $serv_mem_dep->list_by_conds(array('m_uid' => $m_uid));
		$cd_ids = array_column($mem_dep, 'cd_id');
		// 获取部门数据
		$serv_dep = D('Common/CommonDepartment', 'Service');
		$cd_data = $serv_dep->list_by_conds(array('cd_id' => $cd_ids));
		foreach ($cd_data as $_cd_data) {
			$fixed['department']['value'][] = array(
				'id' => $_cd_data['cd_id'],
				'name' => $_cd_data['cd_name'],
				'isChecked' => (bool)true,
			);
		}

		// 自定义属性
		foreach ($custom as $_key => &$_rule) {
			if (isset($mem_field['mf_' . $_key])) {
				$_rule['value'] = $mem_field['mf_' . $_key];
			}
		}

		// 如果有职位,并且原来职位不为空
		if (isset($custom['position'])) {
			if (!empty($udata['cj_id'])) {
				$serv_job = D('Common/CommonJob', 'Service');
				$cj_data = $serv_job->get_by_conds(array('cj_id' => $udata['cj_id']));
				if (!empty($cj_data)) {
					$custom['position']['value'] = $cj_data['cj_name'];
				}
			}
		}

		$this->_result = array(
			'fixed' => $fixed,
			'custom' => $custom,
		);

		return true;
	}

	/**
	 * 编辑人员
	 * @return bool
	 */
	public function Edit_post() {

		$post = I('post.');
		if (empty($post['m_uid'])) {
			E('_ERR_EMPTY_POST_UID');

			return false;
		}

		$serv_mem = D('Common/Member', 'Service');
		$result = array();
		if ($serv_mem->edit_member($post, $result) && isset($post['department'])) {
			// 更新部门人数
			$this->_update_department_num();
		};

		return true;
	}

	/**
	 * 导出接口
	 */
	public function Dump_get() {

		$params = I('get.');
		$cd_id = $params['cd_id'];
		$search = $params['search'];

		$limit = $params['limit'];

		// 页码 每页数量 默认值
		if (empty($params['limit'])) {
			$limit = 1000;
		}

		$serv_mem = D('Common/Member', 'Service');
		$serv_mem_search = D('Common/MemberSearch', 'Service');
		$serv_mem_field = D('Common/MemberField', 'Service');

		// 过滤掉未开启的
		$fields_list = $this->_get_field();
		if (!empty($fields_list)) {
			$custom = $fields_list['custom'];
			foreach ($custom as $_field_name => $_cache) {
				if ($_cache['open'] != 1) {
					unset($custom[$_field_name]);
				}
			}
		}
		//自定义字段值和字段名
		foreach ($custom as $key_field => $_field) {
			$field[] = $key_field;
			$field_name[] = $_field['name'];
		}

		//计算总数
		if (!empty($params['search'])) {
			//搜索导出
			$count = $serv_mem_search->count_by_keyword_status($params['search'], $params['status']);
		} elseif (!empty($params['cd_id'])) {
			//判断部门是否存在，并且非全公司
			if (isset($this->_departments[$cd_id]) && $this->_departments[$cd_id]['cd_upid'] != 0) {
				// 获取下面所有子部门
				$dp_ids = Department::instance()->list_childrens_by_cdid($cd_id, true);
			} else {
				$dp_ids[] = $cd_id;
			}
			//按部门导出
			if (count($dp_ids) == 1 && $this->_departments[$cd_id]['cd_upid'] == 0) {
				//导出所有
				if (isset($params['status'])) {
					$conds_s['m_qywxstatus'] = $params['status'];
					$count = $serv_mem->count_by_conds($conds_s);
				} else {
					$count = $serv_mem->count();
				}
			} else {
				$count = $serv_mem->count_by_cdid_status($dp_ids, $params['status']);
			}
		} else {
			//导出所有
			$count = $serv_mem->count();
		}

		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'member' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		//循环次数
		$times = ceil($count / $limit);

		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit);
			// 分页参数
			$page_option = array($start, $limit);

			//判断条件搜索记录
			if (!empty($params['search'])) {
				//搜索导出
				$mem_search_list = $serv_mem_search->list_by_keyword_status($search, $params['status'], $page_option);
				$uid_list = array_column($mem_search_list, 'm_uid');
			} elseif (!empty($params['cd_id'])) {
				//按部门导出
				if (count($dp_ids) == 1 && $this->_departments[$cd_id]['cd_upid'] == 0) {
					//导出所有
					if (isset($params['status'])) {
						$conds_li['m_qywxstatus'] = $params['status'];
						$user_list = $serv_mem->list_by_conds($conds_li);
					} else {
						$user_list = $serv_mem->list_all();
					}
					if (!empty($user_list)) {
						foreach ($user_list as $__uids) {
							$uid_list[] = $__uids['m_uid'];
						}
					}
				} else {
					$uid_list = $serv_mem->list_by_cdid_status($dp_ids, $params['status'], $limit, $page_option);
				}
			} else {
				//导出所有
				$uid_list = $serv_mem->list_by_conds_dump($params['status'], $limit, $page_option);
			}
			//获取当前人的人员信息
			$conds_cdid['m_uid'] = $uid_list;
			$serv_mem_dep = D('Common/MemberDepartment', 'Service');
			$cd_info = $serv_mem_dep->list_by_conds($conds_cdid);

			foreach($cd_info as $_inf) {
				$mem_cdid[$_inf['m_uid']][] = $_inf['cd_id'];
			}
			//查询这些id的扩展
			$info_list = $serv_mem_field->list_field_by_uid($uid_list, $field);
			//导出人员信息表
			$result = $this->get_member($info_list, $field, $field_name, $i, $mem_cdid);
			if ($result) {
				$zip->addFile($result, 'member' . $i . '.xls');
			}
			unset($page_option, $start);
		}
		//默认文件
		if ($count == 0) {
			// 标题栏样式定义
			$options = array(
				'title_text_color' => 'FFf5f5f5',
				'title_background_color' => 'FF000099',
			);
			// 下载的文件名
			$filename = 'member';
			// 标题文字 和 标题栏宽度
			$title_width = array();

			// 获取属性规则
			$field = $this->_get_field();
			// 如果字段是开启
			$title_string = array();
			foreach ($field as $_fixed_or_custom) {
				foreach ($_fixed_or_custom as $_field) {
					if ($_field[self::CHECK_OPEN] == self::ALLOW) {
						$title_string[] = $_field['name'];
					}
				}
			}

			// 默认数据
			$row_data = array();

			// 载入 Excel 类
			$excel = new \Com\Excel();
			$excel->make_excel_download($filename, $title_string, $title_width, $row_data, $options);
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return false;
	}


	/**
	 * 获取人员属性规则
	 * @return array|mixed
	 */
	protected function _get_field() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		return $setting['fields'];
	}

	/**
	 * 人员信息表
	 * @param $list array 人员信息列表
	 * @param $field array 开启的自定义字段名
	 * @param $field_name 开启的自定义字段值
	 * @param $n int 文件个数
	 * @param $mem_cdid array 人员部门数组
	 * @return bool
	 */
	public function get_member($list, $field, $field_name, $n, $mem_cdid) {

		$excel = new \Com\Excel();
		$wid = count($field) + 7;
		// xls 横坐标
		for ($i = 0; $i <= $wid; $i ++) {
			if ($i < 26) {
				$letter[] = chr($i + 65);
			} else {
				$ascii = floor($i / 26) - 1;
				$letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
			}
		}
		foreach($letter as $A) {
			//标题颜色
			$excel->getActiveSheet()->getStyle($letter[0].'1:'.$letter[$wid-1].'1')->getFont()->getColor()->setARGB('FFf5f5f5');
			$excel->getActiveSheet()->getStyle($letter[0].'1:'.$letter[$wid-1].'1')->getFill()->getStartColor()->setARGB('FF000099');
			$excel->getActiveSheet()->getStyle($letter[0].'1:'.$letter[$wid-1].'1')->getFill()->setFillType('solid');
		}

		//固定字段
		$data[0] = array('姓名', '账号', '性别', '手机号', '微信', '邮箱', '部门');

		$data[0] = array_merge($data[0], $field_name);
		//获取所有上级领导人id
		$leader_list = array();
		$position_list = array();
		foreach ($list as $_leader) {
			//领导人
			if ($_leader['mf_leader'] != 0) {
				$leader_list = array_merge($leader_list, explode(',', $_leader['mf_leader']));
			}
			//职位
			if ($_leader['cj_id'] != 0) {
				$position_list[] = $_leader['cj_id'];
			}
		}
		//领导人
		if (!empty($leader_list)) {
			//去重
			$leader_list = array_unique($leader_list);
			$serv_mem = D('Common/Member', 'Service');
			$conds_leader['m_uid'] = $leader_list;
			$mem_info = $serv_mem->list_by_conds($conds_leader);
			//以m_uid做键
			$mem_info = array_column($mem_info, 'm_username', 'm_uid');
		}
		//职位
		if (!empty($position_list)) {
			//去重
			$position_list = array_unique($position_list);
			$serv_job = D('Common/CommonJob', 'Service');
			$conds_position['cj_id'] = $position_list;
			$position_list = $serv_job->list_by_conds($conds_position);
			//以cj_id做键
			$position_list = array_column($position_list, 'cj_name', 'cj_id');
		}
		$i = 1;
		foreach ($list as $key_mem => $val) {
			//固定字段
			$data[] = array(
				$val['m_username'],
				$val['m_openid'],
				$this->mender[$val['m_gender']],
				$val['m_mobilephone'],
				$val['m_weixin'],
				$val['m_email'],
				$this->get_long_cd_name($mem_cdid[$val['m_uid']]),
			);
			//自定义字段
			foreach ($field as $_fields) {
				if ($_fields == 'leader') {
					$str_leader = '';
					//m_uid换成人名
					if ($val['mf_' . $_fields] == 0) {
						$str_leader = '';
					} else {
						$str_leader = explode(',', $val['mf_' . $_fields]);
						foreach ($str_leader as &$_list_leader) {
							$_list_leader = $mem_info[$_list_leader];
						}
						$str_leader = implode(',', $str_leader);
					}
					array_push($data[$i], $str_leader);
				} elseif ($_fields == 'position') {//如果是职位
					if ($val['cj_id'] == 0) {
						array_push($data[$i], '');
					} else {
						array_push($data[$i], $position_list[$val['cj_id']]);
					}
				} else {
					array_push($data[$i], $val['mf_' . $_fields]);
				}
			}
			$i ++;
		}

		// 填充表格信息
		for ($i = 1; $i <= count($data); $i ++) {
			$j = 0;
			foreach ($data[$i - 1] as $key => $value) {
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
				$j ++;
			}
		}
		//var_dump($data);die;
		// 创建Excel输入对象
		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="member' . $n . '.xls"');
		header("Content-Transfer-Encoding:binary");

		$path = get_sitedir() . 'excel/';
		if (!is_dir($path)) {
			mkdir($path);
		}

		$write->save($path . "member" . $n . ".xls");
		$filepath = $path . 'member' . $n . '.xls';

		return $filepath;
	}

	/**
	 * 下载输出至浏览器
	 * @param $zipname
	 */
	private function __put_header($zipname) {

		if (!file_exists($zipname)) {
			exit("下载失败");
		}

		$file = fopen($zipname, "r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . filesize($zipname));
		Header("Content-Disposition: attachment; filename=" . basename($zipname));
		echo fread($file, filesize($zipname));
		$buffer = 1024;
		while (!feof($file)) {
			$file_data = fread($file, $buffer);
			echo $file_data;
		}

		fclose($file);
	}

	/**
	 * 清理产生的临时文件
	 */
	private function __clear($path) {

		$dh = opendir($path);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				unlink($path . $file);
			}
		}

		return true;
	}

	/**
	 * 初始化微信接口
	 * @return Wxqy\Addrbook
	 */
	private function _wxqy_addr() {

		$qywx = &\Common\Common\Wxqy\Service::instance();
		$qywx_ab = new Wxqy\Addrbook($qywx);

		return $qywx_ab;
	}

	/**
	 * 根据部门id转换成层级部门名称
	 * @param $cd_ids array 部门id
	 * @return $long_name string 格式后的层级部门
	 */
	public function get_long_cd_name($cd_ids) {

		//获取顶级部门
		Department::instance()->get_top_cdid($top_id);
		foreach ($cd_ids as $_cd_id) {
			$p_cdids = array();
			//取每个部门的所有上级部门
			Department::instance()->list_parent_cdids($_cd_id, $p_cdids);
			$p_cdids = array_values($p_cdids);
			$p_cdids[] = $_cd_id;
			$parentids[] = $p_cdids;
		}
		//排除顶级部门
		foreach ($parentids as $_top_key => $_top) {
			foreach ($_top as $__key => $__top) {
				if ($__top == $top_id) {
					unset($parentids[$_top_key][$__key]);
				}
			}
		}
		//以id为键
		$name_list = array_column($this->_departments, 'cd_name', 'cd_id');

		//id更换为名称
		foreach ($parentids as $_key => $_val) {
			sort($parentids[$_key]);
			foreach ($_val as $_cdname) {
				$names[$_key][] = $name_list[$_cdname];
			}
		}
		//拼接每个部门数组
		foreach ($names as $_cd_name) {
			$str_name[] = implode('/', $_cd_name);
		}

		//拼接平级部门
		$long_name = implode(';', $str_name);

		return $long_name;
	}

}
