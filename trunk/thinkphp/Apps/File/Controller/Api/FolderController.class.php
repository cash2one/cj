<?php
/**
 * FolderController.class.php
 * @create-time: 2015-07-01
 */
namespace File\Controller\Api;

class FolderController extends AbstractController {

	/**
	 * 新建文件夹
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function Folder_create_post() {

		// 文件夹信息
		$folder = array ();

		// 用户提交的参数
		$params = I('request.');

		// 分组权限验证
		$group_id = (int)$params['group_id'];
		if (!$this->_get_permission($group_id, \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 非用户提交的扩展参数
		$extend = array (
			'm_uid'      => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		$serv_folder = D('File/Folder', 'Service');
		// 新增操作失败
		if (!$serv_folder->add_folder($folder, $params, $extend)) {
			$this->_set_error($serv_folder->get_errmsg(), $serv_folder->get_errcode());
			return false;
		}

		// 数据格式化
		$serv_fmt = D('File/Format', 'Service');
		$serv_fmt->folder($folder);

		// 返回数据
		$this->_result = $folder;
		return true;
	}

	/**
	 * 重命名文件夹/文件
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function Rename_by_id_post() {

		// 用户提交的参数
		$params = I('request.');

		// 获取f_id
		$f_id = $params['f_id'];

		// 分组权限验证
		$f_info = $this->_info($f_id);
		$group_id = $f_info['group_id'];
		if (!$this->_get_permission($group_id, \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 编辑操作失败
		$serv_f = D('File/Folder', 'Service');
		if (!$serv_f->rename_by_fid($params)) {
			$this->_set_error($serv_f->get_errmsg(), $serv_f->get_errcode());
			return false;
		}

		return true;
	}

	/**
	 * 移动文件夹/文件
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function Folders_or_files_move_post() {

		// 用户提交的参数
		$params = I('request.');

		// 获取f_id
		$f_ids = (array)$params['f_ids'];
		$target_id = (int)$params['target_id'];

		// 待移动文件夹/文件来自同一个分组，取出第一个文件夹/文件id
		$f_id = $f_ids[0];

		// 判断文件夹或文件分组权限
		$f_info = $this->_info($f_id);

		// 判断权限
		if (!$this->_get_permission($f_info['group_id'], \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 判断目标id的分组权限
		$target_info = $this->_info($target_id);

		// 移动到其他分组
		if ($target_info['group_id'] !== $f_info['group_id']) {

			// 移动到其他分组，传入参数拼接进新的分组id
			$params['group_id'] = $target_info['group_id'];

			// 判断权限
			if (!$this->_get_permission($params['group_id'], \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
				$this->_set_error('_ERR_NO_AUTHORITY');
				return false;
			}
		}

		// 编辑操作失败
		$serv_f = D('File/Folder', 'Service');
		if (!$serv_f->move_by_fids($params)) {
			$this->_set_error($serv_f->get_errmsg(), $serv_f->get_errcode());
			return false;
		}

		return true;
	}

	/**
	 * 删除文件夹/文件
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function Delete_by_ids_post() {

		// 获取f_id
		$f_ids = (array)I('request.f_ids');

		// 参数无效
		$serv_f = D('File/Folder', 'Service');
		$list = $serv_f->list_by_pks($f_ids);

		// 删除id验证
		if (count($f_ids) != count($list)) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 待删除文件夹/文件来自同一个分组，取出第一个文件夹/文件id
		$f_id = $f_ids[0];

		// fid有效性验证
		$f_info = $this->_info($f_id);

		// 判断权限
		if (!$this->_get_permission($f_info['group_id'], \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 编辑操作失败
		if (!$serv_f->delete_by_fids($f_ids)) {
			$this->_set_error($serv_f->get_errmsg(), $serv_f->get_errcode());
			return false;
		}

		return true;
	}

	/**
	 * 列表详情
	 * @return array
	 */
	public function Folder_info_get() {

		// 用户提交的参数
		$params = I('request.');

		// 获取f_id
		$f_id = (int)$params['f_id'];

		// 每页条数
		$limit = I('get.limit');
		$page = I('get.page');

		// 文件夹
		$f_info = $this->_info($f_id);

		// 判断文件夹分组权限
		$group_id = $f_info['group_id'];
		if (!$this->_get_permission($group_id, \File\Model\FilePermissionModel::MT_COLLABORATORS, $this->_login->user['m_uid'])) {
			$this->_set_error('_ERR_NO_AUTHORITY');
			return false;
		}

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值
		if (empty($limit) || $limit < cfg('PAGE_MINSIZE') || $limit > cfg('PAGE_MAXSIZE')) {
			$limit = $this->_plugin->setting['perpage'];
		}

		list($start, $limit, $page) = page_limit($page, $limit);
		$folder_info = D('File/Folder', 'Service');

		// 分页参数
		$page_option = array (
			$start,
			$limit
		);

		// 列表总数
		$count = $folder_info->count_file_by_fid($f_id);
		$list = $folder_info->list_file_by_fid($f_id, $f_info, $page_option, array ('f_created' => "DESC"));

		// 取附件id集合
		$att_ids = array ();
		foreach ($list as &$_v) {
			if (\File\Model\FileModel::F_FILE == $_v['f_level']) {
				array_push($att_ids, $_v['at_id']);
			}
		}

		// 附件列表
		$list_attachment = array ();
		if (!empty($att_ids)) {
			// 取附件列表
			$serv_a = D('Common/CommonAttachment', 'Service');
			$list_attachment = $serv_a->list_by_pks($att_ids);
		}
		// 数据格式化
		$serv_fmt = D('File/Format', 'Service');
		foreach ($list as &$_v) {

			if (\File\Model\FileModel::F_FILE == $_v['f_level'] && !empty($list_attachment)) {
				// 根据键值取相应数据
				$att_info = $this->_seekarr($list_attachment, 'at_id', $_v['at_id']);
				// 拼接附件信息
				$_v['at_filesize'] = $att_info['at_filesize'];
				$_v['at_attachment'] = "/attachment/read/".$_v['at_id'];
			}

			// 格式化
			$serv_fmt->file($_v);
		}

		unset($_v);

		// 组合返回数组
		$res = array (
			"total" => $count,
			"limit" => $limit,
			"data"  => $list,
		);

		$this->_result = $res;
		return $res;
	}
}
