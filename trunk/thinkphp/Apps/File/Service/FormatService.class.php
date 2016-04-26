<?php
/**
 * FormatService.class.php
 * @create-time: 2015-07-01
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */
namespace File\Service;

class FormatService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 分组数据格式化
	 * @param array &$data 待格式化数据
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function group(&$data) {

		$format_data = array ();

		isset($data['f_id']) ? $format_data['group_id'] = $data['f_id'] : '';
		isset($data['f_name']) ? $format_data['group_name'] = $data['f_name'] : '';
		isset($data['f_description']) ? $format_data['group_description'] = $data['f_description'] : '';
		isset($data['f_parent_id']) ? $format_data['group_parent_id'] = $data['f_parent_id'] : '';
		isset($data['m_uid']) ? $format_data['member_uid'] = $data['m_uid'] : '';
		isset($data['m_username']) ? $format_data['member_username'] = $data['m_username'] : '';
		isset($data['f_created']) ? $format_data['group_created'] = rgmdate($data['f_created']) : '';
		isset($data['p_m_type']) ? $format_data['permission_member_type'] = $data['p_m_type'] : '';

		// 返回数据
		$data = $format_data;
		return true;
	}

	/**
	 * 文件夹数据格式化
	 * @param array &$data 待格式化数据
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function folder(&$data) {

		$format_data = array ();

		isset($data['f_id']) ? $format_data['folder_id'] = $data['f_id'] : '';
		isset($data['f_name']) ? $format_data['folder_name'] = $data['f_name'] : '';
		isset($data['f_level']) ? $format_data['folder_level'] = $data['f_level'] : '';
		isset($data['f_parent_id']) ? $format_data['folder_parent_id'] = $data['f_parent_id'] : '';
		isset($data['m_uid']) ? $format_data['member_uid'] = $data['m_uid'] : '';
		isset($data['m_username']) ? $format_data['member_username'] = $data['m_username'] : '';
		isset($data['f_created']) ? $format_data['folder_created'] = rgmdate($data['f_created']) : '';

		// 返回数据
		$data = $format_data;
		return true;
	}

	/**
	 * 文件数据格式化
	 * @param array &$data 待格式化数据
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function file(&$data) {

		$format_data = array ();

		isset($data['f_id']) ? $format_data['file_id'] = $data['f_id'] : '';
		isset($data['f_name']) ? $format_data['file_name'] = $data['f_name'] : '';
		isset($data['f_level']) ? $format_data['file_level'] = $data['f_level'] : '';
		isset($data['f_parent_id']) ? $format_data['file_parent_id'] = $data['f_parent_id'] : '';
		isset($data['f_parent_name']) ? $format_data['file_parent_name'] = $data['f_parent_name'] : '';
		isset($data['m_uid']) ? $format_data['member_uid'] = $data['m_uid'] : '';
		isset($data['m_username']) ? $format_data['member_username'] = $data['m_username'] : '';
		isset($data['at_filesize']) ? $format_data['file_size'] = $data['at_filesize'] : '';
		isset($data['at_attachment']) ? $format_data['at_attachment'] = $data['at_attachment'] : '';
		isset($data['f_content']) ? $format_data['file_content'] = $data['f_content'] : '';
		isset($data['f_created']) ? $format_data['file_created'] = rgmdate($data['f_created']) : '';

		// 返回数据
		$data = $format_data;
		return true;
	}

	/**
	 * 文件格式数据格式化
	 * @param array &$data 待格式化数据
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function file_type(&$data) {

		$format_data = array ();

		isset($data['t_id']) ? $format_data['filetype_id'] = $data['t_id'] : '';
		isset($data['t_name']) ? $format_data['filetype_name'] = $data['t_name'] : '';
		isset($data['t_icon']) ? $format_data['filetype_icon_site'] = $data['t_icon'] : '';

		// 返回数据
		$data = $format_data;
		return true;
	}

	/**
	 * 评论信息数据格式化
	 * @param array &$data 待格式化数据
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function comment(&$data) {

		$format_data = array ();

		isset($data['id']) ? $format_data['comment_id'] = $data['id'] : '';
		isset($data['m_uid']) ? $format_data['member_uid'] = $data['m_uid'] : '';
		isset($data['m_username']) ? $format_data['member_username'] = $data['m_username'] : '';
		isset($data['m_face']) ? $format_data['member_face'] = $data['m_face'] : '';
		isset($data['reply_id']) ? $format_data['reply_c_id'] = $data['reply_id'] : '';
		isset($data['content']) ? $format_data['comment_content'] = $data['content'] : '';
		isset($data['created']) ? $format_data['comment_created'] = $data['created'] : '';

		// 返回数据
		$data = $format_data;
		return true;
	}

	/**
	 * 用户信息格式化
	 * @param array $data 待格式化数据
	 * @return bool
	 *
	 * @author: huanw
	 * @email: wanghuan@vchangyi.com
	 */
	public function member(&$data) {

		$format_data = array ();

		isset($data['m_uid']) ? $format_data['reply_member_uid'] = $data['m_uid'] : '';
		isset($data['m_username']) ? $format_data['reply_member_username'] = $data['m_username'] : '';
		isset($data['m_face']) ? $format_data['reply_member_face'] = $data['m_face'] : '';

		// 返回数据
		$data = $format_data;
		return true;
	}
}
