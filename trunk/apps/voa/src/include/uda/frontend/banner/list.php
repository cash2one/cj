<?php

/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/11/17
 * Time: 17:28
 */
class voa_uda_frontend_banner_list extends voa_uda_frontend_banner_base {

	//banner表
	protected $serv;
	/**
	 * @var array 检索条件
	 */
	protected $_condi = array();

	public function __construct() {

		parent::__construct();
		$this->serv = &service::factory('voa_s_oa_banner');
	}

	public function doit($condi, &$result) {

		// 投票调研主题
		if (!empty($condi['title'])) {
			$this->_condi['title LIKE ?'] = '%' . $condi['title'] . '%';
		}

		// 分类
		if (!empty($condi['handpicktype'])) {
			$this->_condi['handpicktype'] = $condi['handpicktype'];
		}

		$in = $this->serv->list_by_conds($this->_condi, '',array('b_order'=> 'ASC'));
		$this->_doit_formt($in, $result);

		return true;
	}

	/**
	 * 格式化
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	protected function _doit_formt($in, &$out) {

		if (!$in) {
			$out = array();

			return true;
		}
		foreach ($in as $k => $v) {
			$data = $this->_list_title(array('cid' => $v['handpicktype'], 'lid' => $v['lid']));
			$out[$k] = $v;
			$out[$k]['ctype'] = $this->_ctype($v['handpicktype']);
			$out[$k]['created'] = rgmdate($v['created']);
			$out[$k]['title'] = $data['title'];
			$out[$k]['url'] = $data['url'];
		}

		return true;
	}

	/**
	 * 格式化leix
	 * @param $in
	 */
	protected function _ctype($in) {

		switch($in) {
			case 1:
				$out = '活动';
				break;
			case 2:
				$out = '话题';
				break;
			case 3:
				$out = '投票';
				break;
		}

		return $out;
	}

	/**
	 *
	 */
	protected function _list_title($in, &$out=array()) {
		$plugin = array();
		//获取应用id
		foreach($this->_plugin as $val) {
			switch ($val['cp_identifier']) {
				case 'event':
					$plugin[1] = $val['cp_pluginid'];
					break;
				case 'community':
					$plugin[2] = $val['cp_pluginid'];
					break;
				case 'cnvote':
					$plugin[3] = $val['cp_pluginid'];
					break;
			}
		}
		//获取title, url
		switch($in['cid']) {
			case 1:
				$serv = &service::factory('voa_s_oa_event');
				$result = $serv->get($in['lid']);
				$data['title'] = $result['title'];
				$data['url'] = '/admincp/office/event/view/pluginid/'.$plugin[1].'/?acid=' .$in['lid'];
				break;
			case 2:
				$serv = &service::factory('voa_s_oa_community');
				$result = $serv->get($in['lid']);
				$data['title'] = $result['subject'];
				$data['url'] = '/admincp/office/community/view/pluginid/'.$plugin[2].'/?cid=' . $in['lid'];
				break;
			case 3:
				$serv = &service::factory('voa_s_oa_cnvote');
				$result = $serv->get($in['lid']);
				$data['title'] = $result['subject'];
				$data['url'] = '/admincp/office/cnvote/view/pluginid/'.$plugin[3].'/?id=' . $in['lid'];
				break;
		}

		return $data;
	}

}
