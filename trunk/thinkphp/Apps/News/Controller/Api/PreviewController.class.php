<?php
/**
 * PreviewController.class.php
 * $author$xubinshan
 */

namespace News\Controller\Api;
use Common\Common\Wxqy\Service;

class PreviewController extends AbstractController {

	/**
	 * 预览公告
	 * @return bool|void
	 */
	public function Preview_info_get() {

		$ne_id = I('get.ne_id', '', 'intval');

		if (!$ne_id) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}

		// 当前用户
		$m_uid = $this->_login->user['m_uid'];

		// 判断新闻是否存在
		$serv_news = D('News/News', 'Service');
		$detail = $serv_news->get_preview($ne_id);

		// 如果公告不存在
		if (empty($detail)) {
			$this->_set_error('_ERROR_NEWS_BEYOND');
			return false;
		}

		// 该公告已经发布
		if ($detail['is_publish'] == 1) {
			$this->_set_error('_ERROR_NEWS_SEND');
			return false;
		}

		$preview = D('News/NewsCheck', 'Service');
		$show = $preview->did_show($ne_id, $m_uid);

		// 判断审核权限
		if(!$show){
			$this->_set_error('_ERROR_NO_PRIVILEGE');
			return false;
		}

		// 判断新闻是否已预览回复
		if (!$preview->is_check($show)) {
			$this->_set_error('_ERROR_NEWS_CHECK');
			return false;
		}

		// 格式化数据
		$preview->format_check($detail);

		// 返回数据
		return $this->_response($detail);
	}

	/**
	 * 预览回复
	 * @return bool
	 */
	public function Preview_update_post() {

		$ne_id = I('post.ne_id', '', 'intval');
		$note = I('post.note', '', 'htmlspecialchars');

		// 公告不合法
		if (empty($ne_id)) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}

		// 回复内容不能为空
		if (empty($note)) {
			$this->_set_error('_ERROR_NO_NOTE');
			return false;
		}

		// 当前用户ID
		$m_uid = $this->_login->user['m_uid'];

		$serv_check = D('News/NewsCheck', 'Service');
		$show = $serv_check->did_show($ne_id, $m_uid);

		// 不存在审核信息
		if (empty($show)) {
			$this->_set_error('_ERROR_NO_CHECK');
			return false;
		}

		// 用户提交的参数
		$params = array(
			'check_note' => $note,
			'is_check' => 3 // 审核通过
		);

		// 更新回复数据
		if (!$serv_check->update_data($show, $params)) {
			$this->_set_error('_ERROR_NEWS_CHECK');
			return false;
		}

		// 发送消息
		$this->send_msg($note, $ne_id);

		return true;
	}

	/**
	 *  预览人回复后 发给公告作者
	 * @param $content
	 * @param $ne_id
	 */
	protected function send_msg($note, $ne_id) {

		$serv_news = D('News/News', 'Service');
		//$serv_mem = D('Common/Member', 'Service');
		$detail = $serv_news->get_uid_by_ne_id($ne_id);

		$author = $serv_news->get_by_uid($detail['m_uid']);

		// 当然回复人的m_username
		$username = $this->_login->user['m_username'];

		// 消息描述
		$description =  "公告标题：【{$detail['title']}】".
						"\n预览人：{$username}".
						"\n回复说明：".$note;

		$url = $this->_view_url($ne_id);

		// 数据组装
		$data = array(
			'title' => '您收到一条预览回复',
			'description' => $description,
			'url' => $url,
			'picurl' => ''
		);

		$to_users = array($author['m_openid']);

		$post = &Service::instance();

		// 插件应用信息
		$plugins = $this->get_plugin_id('news');

		// 发送消息
		$post->post_news($data, $plugins['cp_agentid'], $to_users);
	}

	/**
	 * 详情URL地址
	 * @param $ne_id
	 * @return string
	 */
	protected function _view_url($ne_id) {

		// 插件应用信息
		$plugins = $this->get_plugin_id('news');

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];

		$url = $face_base_url . "/frontend/news/view?newsId=".$ne_id."&action=preview&pluginid=".$plugins['cp_pluginid'];

		return $url;
	}
}
