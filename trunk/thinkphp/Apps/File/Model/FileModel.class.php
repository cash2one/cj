<?php
/**
 * FileModel.class.php
 * @create-time: 2015-07-01
 */
namespace File\Model;

class FileModel extends AbstractModel {

	//分级：分组
	const F_GROUP = 1;

	//分级：文件夹
	const F_FOLDER = 2;

	//分级：文件
	const F_FILE = 3;

	// 获取分组分级
	public function get_f_group() {

		return self::F_GROUP;
	}

	// 获取文件夹分级
	public function get_f_folder() {

		return self::F_FOLDER;
	}

	// 获取文件分级
	public function get_f_file() {

		return self::F_FILE;
	}

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'f_';
	}

	/**
	 * 修改文件夹/文件名称
	 * @param int $f_id 文件夹/文件id
	 * @param string $f_name 名称
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function rename_by_fid($f_id, $f_name) {

		// 更新sql语句
		$sql = "UPDATE __TABLE__ SET `f_name`=? ,`f_status`=? ,`f_updated`=? WHERE `f_id`=? AND `f_status`<?";

		// 更新条件参数
		$f_params = array (
			$f_name,
			$this->get_st_update(),
			NOW_TIME,
			$f_id,
			$this->get_st_delete()
		);

		// 执行入库操作
		if (!$this->_m->update($sql, $f_params)) {
			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 移动文件夹/文件
	 * @param int $group_id 分组id
	 * @param int $target_id 目标文件夹或分组id
	 * @param int $f_id 要移动的文件夹/文件id
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function move_by_fids($target_id, $f_ids, $group_id) {

		// 移动到其他组，设置group_id
		if ($group_id) {

			$sql = "UPDATE __TABLE__ SET `group_id`=? ,`f_parent_id`=? ,`f_status`=? ,`f_updated`=? WHERE `f_id` IN (?) AND `f_status`<?";

			// 移动到其他组条件参数
			$f_params = array (
				$group_id,
				$target_id,
				$this->get_st_update(),
				NOW_TIME,
				$f_ids,
				$this->get_st_delete()
			);
		} else {

			$sql = "UPDATE __TABLE__ SET `f_parent_id`=? ,`f_status`=? ,`f_updated`=? WHERE `f_id` IN (?) AND `f_status`<?";

			// 本组移动条件参数
			$f_params = array (
				$target_id,
				$this->get_st_update(),
				NOW_TIME,
				$f_ids,
				$this->get_st_delete()
			);
		}

		// 执行数据库操作
		if (!$this->_m->update($sql, $f_params)) {
			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 批量删除文件
	 * @param int $fid 文件夹id
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function delete_by_fids($f_ids) {

		// 删除sql语句
		$sql = "UPDATE __TABLE__ SET `f_status`=? ,`f_deleted`=? WHERE `f_id` IN (?) AND `f_status`<?";

		// 删除条件参数
		$f_params = array (
			$this->get_st_delete(),
			NOW_TIME,
			$f_ids,
			$this->get_st_delete()
		);

		// 执行入库操作
		if (!$this->_m->update($sql, $f_params)) {
			E(L('_ERR_UPDATE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * 根据文件夹/文件/创建人名称搜索
	 * @param string $name 文件夹或文件名称/创建人名称
	 * @param int $fid 分组id
	 * @param array $order_option 排序
	 * @return array|bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function list_by_name_gid($name, $fid = 0, $order_option = array ()) {

		// 查询sql语句
		$sql = "SELECT * FROM __TABLE__";

		// 搜索条件sql
		$wheres = array ("`f_level`!=? AND `f_status`<?");

		// 查询条件参数
		$params = array (
			$this->get_f_group(),
			$this->get_st_delete()
		);

		// 分组id不为空，拼接查询条件
		if ($fid != 0) {
			$wheres[] = "`group_id`=?";
			$params[] = $fid;
		}

		// 拼接搜索条件sql、查询条件参数
		if (!empty($name)) {
			$wheres[] = "(f_name LIKE ? OR m_username LIKE ?)";
			$params[] = "%".$name."%";
			$params[] = "%".$name."%";
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql." WHERE ".implode(" AND ", $wheres)."{$orderby}", $params);
	}

	/**
	 * 根据文件夹id查询是否有子目录
	 * @param int $fid 分组id
	 * @return int 子目录数量
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function count_by_parentid($fid) {

		$sql = "SELECT COUNT(*) FROM __TABLE__ WHERE `f_parent_id`=? AND `f_status`<?";

		// 查询条件参数
		$params = array (
			$fid,
			$this->get_st_delete()
		);

		return $this->_m->result($sql, $params);
	}

	/**
	 * 获取某文件夹的分级数
	 * @param int $fid 文件夹id
	 * @return int mixed 分级数
	 */
	public function get_sublevel($fid = 0) {

		$sql = "SELECT * FROM __TABLE__ WHERE `f_id`=? AND `f_status`<?";

		$params = array (
			$fid,
			$this->get_st_delete()
		);

		$result = $this->_m->fetch_row($sql, $params);

		$f_parent_id = $result['f_parent_id'];

		static $dig = 1;

		// 如果有子类
		if ($f_parent_id && $dig < cfg('folder_maxlevel')) {
			$dig ++;
			$this->get_sublevel($f_parent_id);
		}

		return $dig;
	}

	/**
	 * 删除分组id所有信息
	 * @param int $fid 分组id
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function delete_all($fid) {

		// 分组id
		$f_id = (int)$fid;
		$group_inf = array (
			$this->get_st_delete(),
			NOW_TIME,
			$f_id,
			$this->get_st_delete(),
		);

		$sql = "UPDATE __TABLE__ SET f_status=?, f_deleted=? WHERE group_id=? AND f_status<?";
		if (!$this->_m->execsql($sql, $group_inf)) {
			E(L('_ERR_DELETE_ERROR'));
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
	public function list_file_by_fid($f_id, $page_option = array (), $order_option = array ()) {

		$sql = "SELECT * FROM __TABLE__ WHERE f_parent_id=? AND f_status<?";

		// 查询条件
		$params = array (
			$f_id,
			$this->get_st_delete()
		);

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql."{$orderby}{$limit}", $params);
	}

	/**
	 * 根据文件夹$f_id获取列表详情总数
	 * @param int $f_id 当前文件夹f_id
	 * @return int 消息总数
	 */
	public function count_file_by_fid($f_id) {

		$sql = "SELECT COUNT(*) FROM __TABLE__ WHERE f_parent_id=? AND f_status<?";

		// 查询条件
		$params = array (
			$f_id,
			$this->get_st_delete()
		);

		return $this->_m->result($sql, $params);
	}

	/**
	 * 判断分组名称是否有效
	 * @param array $group_name 引用返回信息
	 * @return boolean
	 *
	 * @author: wangpengpeng
	 * @email: wangpengpeng@vchangyi.com
	 */
	public function veryfy($group_name) {

		$group_inf = array (
			$group_name,
			$this->get_f_group(),
			$this->get_st_delete()
		);

		$sql = "SELECT COUNT(*) FROM __TABLE__  WHERE f_name=? AND f_level=? AND f_status<?";
		if ($this->_m->result($sql, $group_inf)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取分组下文件和文件夹列表
	 * @param int $fid 分组id
	 * @return array
	 *
	 * @author: wpp
	 * @email: wpp@vchangyi.com
	 */
	public function list_file($f_id) {

		$sql = "SELECT * FROM __TABLE__ WHERE group_id=? AND f_level!=? AND f_status<?";

		// 查询条件
		$params = array (
			$f_id,
			$this->get_f_group(),
			$this->get_st_delete()
		);
		return $this->_m->fetch_array($sql, $params);
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

		$sql = "SELECT `f_id`, `f_name`, `f_level`, `f_parent_id` FROM __TABLE__ WHERE group_id=? AND f_status<?";

		// 查询条件
		$params = array (
			$group_id,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql, $params);
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

		$sql = "SELECT `f_name`, `f_level`, `f_parent_id` FROM __TABLE__ WHERE f_id=? AND f_level<? AND f_status<?";

		// 查询条件
		$params = array (
			$f_parent_id,
			self::F_FILE,
			$this->get_st_delete()
		);

		$file_row = $this->_m->fetch_row($sql, $params);
		$f_parent_id = $file_row['f_parent_id'];
		$f_level = $file_row['f_level'];

		// 向前压入数据
		static $file_names = array();
		array_unshift($file_names,$file_row['f_name']);

		// 如果当前不是分组
		if ($f_level != $this->get_f_group()) {
			$this->fetch_by_f_parent_id($f_parent_id);
		}

		return $file_names;
	}

}
