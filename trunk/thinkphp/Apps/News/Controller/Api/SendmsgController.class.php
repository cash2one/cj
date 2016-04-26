<?php
/**
 * SendmsgController.class.php
 * xiaodingchen
 */

namespace News\Controller\Api;

class SendmsgController extends AbstractController {

	/**
	 * 发送未读消息提醒
	 * post方式
	 * 请求参数：单一新闻公告ID，单一新闻公告详情URL
	 * 返回值：返回状态值
	 */
	public function Non_reader_msg_post() {

		// 判断数据提交方式
		if (!IS_POST) {
			$this->_set_error('_ERROR_METHOD_NO_POST');
			return false;
		}

		// 判断新闻id是否合法

		$ne_id = I('ne_id');
		$ne_id = intval($ne_id);

		if (!$ne_id) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}

		// 判断公告详情URL

		$url = I('msg_url');

		if (!$url) {
			$this->_set_error('_ERROR_VIEWURL_NULL');
			return false;
		}

		// 判断URL是否合法
		if (!$this->match_url($url)) {
			$this->_set_error('_ERROR_URL_LEGAL');
			return false;
		}

		// 判断新闻是否存在
		$serv_news = D('News/News', 'Service');
		$news = $serv_news->get($ne_id);

		if (!$news) {
			$this->_set_error('_ERROR_NEWS_BEYOND');
			return false;
		}

		// 限制微信消息发送频率
		$now_time = time();
		if (($now_time - $news['send_no_time']) < 60 * 60 * 6) {
			$this->_set_error('_ERROR_MSG_OUT');
			return false;
		}

		// 获取未读人员列表
		$serv_read = D('News/NewsRead', 'Service');
		$m_uids = $serv_read->un_read_list($ne_id);

		if (!$m_uids) {
			$this->_set_error('_ERROR_NON_READER_NULL');
			return false;
		}

		// 获取未读人员信息
		$serv_member = D('Common/Member', 'Service');
		$members = array();
		$users = array();
		$userinfo = array();
		$members = $serv_news->list_by_uids($m_uids);
		// 查看未读人员是否存在
		if (!$members) {
			$this->_set_error('_ERROR_NON_READER_NULL');
			return false;
		}

		$m_openids = array_column($members, 'm_openid');

		// 组合微信消息
		$data = array();
		// 如果是保密的 标题加入保密
		if ($news['is_secret'] == 1) {
			$data['title'] = "【保密】".$news['title'];
		} else {
			$data['title'] = $news['title'];
		}

		$data['description'] = $news['summary'];
		$data['url'] = $this->_view_url($ne_id);
		$picurl = $serv_news->get_attachment($news['cover_id']);
		$data['picurl'] = $picurl;

		// 获取插件agentid
		$cache = &\Common\Common\Cache::instance();
		$plugins = $cache->get('Common.plugin');

		// 遍历所有插件
		foreach ($plugins as $_p) {
			// 如果不是新闻公告, 则取下一个
			if ('news' != rstrtolower($_p['cp_identifier'])) {
				continue;
			}
			$agentid = (int)$_p['cp_agentid'];
		}

		// 发送微信消息
		$service = &\Common\Common\Wxqy\Service::instance();
		$result = $service->post_news($data, $agentid, $m_openids);

		// 查看微信提醒是否发送成功
		if (!$result) {
			$this->_set_error('_ERROR_MSG_LOSER');
			return false;
		}

		// 更新消息发送时间
		$data = array('send_no_time' => $now_time);
		$serv_news->update($ne_id, $data);

		return true;

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

		$pluginid = cfg('PLUGIN_ID');
		$url = $face_base_url . "/frontend/news/view?newsId=".$ne_id."&action=view&pluginid=".$pluginid;

		return $url;
	}

}
