<?php
/**
 * FolderService.class.php
 * @create-time: 2015-07-02
 */
namespace File\Service;

class FolderService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("File/File");
	}

	/**
	 * 新建文件夹
	 * @param array $folder 文件夹信息
	 * @param array $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool 返回结果：true=成功，false=失败
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function add_folder(&$folder, $params, $extend = array ()) {

		// 获取入库参数
		$group_id = (int)$params['group_id'];
		$folder_name = (string)$params['folder_name'];
		$f_parent_id = (int)$params['f_parent_id'];

		// 参数非空判断
		if (empty($group_id) || empty($folder_name) || empty($f_parent_id)) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 分组id有效性验证
		if (!$group_info = $this->_get_by_id($group_id)) {
			$this->_set_error('_ERR_PARENTID_IS_NOT_EXIST');
			return false;
		}

		// 分组id是文件类型
		if ($this->_d->get_f_file() == $group_info['f_level']) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 验证名称长度
		if (cfg('file_name_len') < mb_strlen($folder_name, 'utf8')) {
			$this->_set_error('_ERR_NAME_LENGTH_ERROR');
			return false;
		}

		// 父级id有效性验证
		if (!$parent_info = $this->_get_by_id($f_parent_id)) {
			$this->_set_error('_ERR_PARENTID_IS_NOT_EXIST');
			return false;
		}

		// 父级id是文件类型
		if ($this->_d->get_f_file() == $parent_info['f_level']) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// 判断分级，不能超过10级
		if ($this->__get_sublevel($f_parent_id) == cfg('folder_maxlevel')) {
			$this->_set_error('_ERR_FOLSER_LEVEL');
			return false;
		}

		// 文件夹信息
		$folder = array (
			'group_id'    => $group_id,
			'f_name'      => $folder_name,
			'f_parent_id' => $f_parent_id,
			'm_uid'       => $extend['m_uid'],
			'm_username'  => $extend['m_username'],
			'f_level'     => $this->_d->get_f_folder(),
			'f_status'    => $this->_d->get_st_create(),
			'f_created'   => NOW_TIME
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($folder)) {
			E(L('_ERR_INSERT_ERROR'));
			return false;
		}

		// 拼接返回数据
		$folder['f_id'] = $id;
		return true;
	}

	/**
	 * 获取某文件夹的分级数
	 * @param int $fid 文件夹id
	 * @return int mixed 分级数
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	private function __get_sublevel($fid) {

		// 验证fid有效性
		if (!$this->_get_by_id($fid)) {
			$this->_set_error('_ERR_FILE_IS_NOT_EXIST');
			return false;
		}
		return $this->_d->get_sublevel($fid);
	}

	/**
	 * 重命名文件夹/文件
	 * @param $params 传入参数
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function rename_by_fid($params) {

		// 获取入库参数
		$f_id = (int)$params['f_id'];
		$f_name = (string)$params['f_name'];

		// 分组id和名称不能为空
		if (empty($f_id) || empty($f_name)) {
			$this->_set_error('_ERR_PARAMS_ERROR');
			return false;
		}

		// fid有效性验证
		if (!$f_info = $this->_get_by_id($f_id)) {
			$this->_set_error('_ERR_FILE_IS_NOT_EXIST');
			return false;
		}

		// 记录是分组类型
		if ($this->_d->get_f_group() == (int)$f_info['f_level']) {
			$this->_set_error('_ERR_FID_CONNOT_BE_GROUPID');
			return false;
		}

		// 验证名称长度
		if (cfg('file_name_len') < mb_strlen($f_name, 'utf8')) {
			$this->_set_error('_ERR_NAME_LENGTH_ERROR');
			return false;
		}
		return $this->_d->rename_by_fid($f_id, $f_name);
	}

	/**
	 * 移动文件夹/文件
	 * @param array $params 传入参数
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function move_by_fids($params) {

		// 获取入库参数
		$f_ids = (array)$params['f_ids'];
		$target_id = (int)$params['target_id'];

		// 分组id和名称不能为空
		if (empty($f_ids) || empty($target_id)) {
			$this->_set_error('_ERR_FILE_ID_IS_NOT_EXIST');
			return false;
		}

		$t_info = $this->_get_by_id($target_id);

		// 验证是否存在
		if (empty($t_info)) {
			$this->_set_error('_ERR_FILE_IS_NOT_EXIST');
			return false;
		}

		// 根据id获取文件列表
		$list = $this->_d->list_by_pks($f_ids);
		$levels = array();
		foreach($list as $_v){
			array_push($levels,$_v['f_level']);
		}

		// 记录存在分组类型
		if (in_array($this->_d->get_f_group(), $levels)) {
			$this->_set_error('_ERR_FID_CONNOT_BE_GROUPID');
			return false;
		}

		// 目标文件夹或分组类型是文件类型
		if ($this->_d->get_f_file() == (int)$t_info['f_level']) {
			$this->_set_error('_ERR_FID_CONNOT_BE_FILEID');
			return false;
		}

		// 移除操作
		if (!$this->_d->move_by_fids($target_id, $f_ids, (int)$params['group_id'])) {
			$this->_set_error('_ERR_UPDATE_ERROR');
			return false;
		}
		return true;
	}

	/**
	 * 删除文件夹/文件
	 * @param int $f_id 待删除文件夹/文件id
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function delete_by_fids($f_ids) {

		// id不能为空
		if (empty($f_ids)) {
			$this->_set_error('_ERR_FILE_ID_IS_NOT_EXIST');
			return false;
		}

		// 根据id获取文件列表
		$list = $this->_d->list_by_pks($f_ids);
		$levels = array();
		foreach($list as $_v){
			array_push($levels,$_v['f_level']);
		}

		// 记录存在分组类型
		if (in_array($this->_d->get_f_group(), $levels)) {
			$this->_set_error('_ERR_FID_CONNOT_BE_GROUPID');
			return false;
		}

		// 根据fid读取子级，子级目录存在提示文件夹不为空
		foreach($list as $_val){
			if ($this->_d->count_by_parentid($_val['f_id'])) {
				$this->_set_error('_ERR_FOLDER_IS_NOT_NULL');
				return false;
			}
		}

		// 删除操作
		if (!$this->_d->delete_by_fids($f_ids)) {
			return false;
		}
		return true;
	}

	/**
	 * 根据文件夹$f_id获取列表详情
	 * @param int $f_id 当前文件夹f_id
	 * @param string $fields 查询字段字符串
	 * @param array $page_option 分页
	 * @param array $order_option 排序
	 * @return array 列表详情
	 */
	public function list_file_by_fid($f_id, $f_info, $page_option, $order_option) {

		// 分组id不能为空
		if (empty($f_id)) {
			$this->_set_error('_ERR_GROUP_ID_IS_NOT_EXIST');
			return false;
		}

		return $this->_d->list_file_by_fid($f_id, $page_option, $order_option);
	}

	/**
	 * 根据文件夹$f_id获取列表详情总数
	 * @param int $f_id 当前文件夹f_id
	 * @return int 消息总数
	 */
	public function count_file_by_fid($f_id) {

		return $this->_d->count_file_by_fid($f_id);
	}

	/**
	 * 根据文件夹$f_id获取当前文件/文件夹详情
	 * @param int $f_id 当前文件夹f_id
	 * @return array 文件详情
	 */
	public function get_by_id($f_id) {

		return $this->_get_by_id($f_id);
	}

	/**
	 * 根据$group_id获取当前文件/文件夹详情
	 * @param int $group_id 当前分组id
	 * @return array 指定分组下文件
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function fetch_by_group_id($group_id) {

		return $this->_d->fetch_by_group_id($group_id);
	}

	/**
	 * 根据f_parent_id获取上级文件/文件夹详情
	 * @param int $f_parent_id 当前分组父级id
	 * @return array 父级分组下文件
	 *
	 * @author: liupengwei
	 * @email: liupengwei@vchangyi.com
	 */
	public function fetch_by_f_parent_id($f_parent_id) {

		return $this->_d->fetch_by_f_parent_id($f_parent_id);
	}
}
