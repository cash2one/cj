<?php
/**
 * voa_c_api_train_get_view
 * 获取一篇文章
 * $Author$
 * $Id$
 */

class voa_c_api_train_get_view extends voa_c_api_train_abstract {

	public function execute() {

		$ta_id = (int)$this->_get('ta_id');  // 获取文章ID
		$m_uid = (int)$this->_member['m_uid'];  // 获取用户ID
		$device = (int)$this->_get('device');  // 获取设备代号
		// 获取文章信息
		$uda = &uda::factory('voa_uda_frontend_train_action_articleview');
		$article = $uda->view($ta_id, $m_uid);
		if (!$article) { //判断文章是否存在
			return $this->_set_errcode(voa_errcode_oa_train::ARTICLE_NOT_EXIST);
		}

		$this->_result = empty($article) ? array() : $this->_format_data($article, $device, $m_uid);

		//判断有无阅读文章权限
		$department_ids = $uda->get_department_id($m_uid);
		if ($article['is_all'] == voa_d_oa_train_articleright::IS_ALL) { //如果全部人员都可查看
			return true;
		}
		if (in_array($m_uid, $article['contacts'])) {//如果是可查看人员
			return true;
		}
		if (array_intersect($department_ids, $article['deps'])) {//如果是可查看部门
			return true;
		}

		return $this->_set_errcode(voa_errcode_oa_train::ARTICLE_NO_RIGHT);
	}

	/**
	 * 格式化文章
	 * @param array $article 文章
	 * @return array
	 */
	protected  function _format_data($article, $device, $m_uid) {

		$this->_set_date_format($device);
		$result = array();
		if ($article) {
			$result['ta_id'] = $article['ta_id'];
			$result['uid'] = $m_uid;
			$result['device'] = $device;
			$result['title'] = rhtmlspecialchars($article['title']);
			$result['author'] = rhtmlspecialchars($article['author']);
			$result['tc_id'] = $article['tc_id'];
			$result['tc_name'] = rhtmlspecialchars($article['tc_name']);
			$result['created'] = $this->_date_format ? rgmdate($article['created'], $this->_date_format) : $article['created'];
			$result['updated'] = $this->_date_format ? rgmdate($article['updated'], $this->_date_format) : $article['updated'];
			$result['content'] = $this->__parse_content_img($article['content']);
		}

		return $result;
	}

	/**
	 * 将文件内容中的图片用缩略图代替，并提供原图链接
	 * @param $content 文章内容
	 */
	private function __parse_content_img($content) {
		//去掉换行、制表等特殊字符
		$html=preg_replace("/[\t\n\r]+/","",$content);
		//匹配表达式
		$partern='/<img src="([^<>]+)(\/>)/';
		//匹配结果
		preg_match_all($partern, $html, $result);

		//将img的src值用缩略图替代，并提供链接
		if (!empty($result[1])) {
			foreach ($result[1] as $k => $img) {
				$arr = explode('"',$img);
				$src = $arr[0];
				if (!preg_match('/(.)*\/attachment\/read\/\d/',$src)) {
					unset($result[0][$k]);
					continue;
				}
				$arr1 = explode("/", $src);
				$ta_id = end($arr1);
				$src_new = voa_h_attach::attachment_url($ta_id, $this->_plugin_setting['img_width']);
				$new = str_replace($src, $src_new, $result[0][$k]);
				$new = str_replace('/>', ' org="'.$src.'"/>', $new);
				$final[] = $new;
			}
			$content = str_replace($result[0], $final, $content);
		}
		return $content;
	}
}

