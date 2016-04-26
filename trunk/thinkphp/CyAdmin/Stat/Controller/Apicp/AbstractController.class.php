<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 上午10:41
 */
namespace Stat\Controller\Apicp;
use Think\Controller\RestController;
use Com\Cookie;

class AbstractController extends \Common\Controller\Apicp\AbstractController {

	/** 今天、昨天、明天的整点时间戳 */
	protected $_today_time = 0;
	protected $_yesterday_time = 0;
	protected $_tomorrow_time = 0;
	/** 一周天数 */
	const WEEK_DAYS = 7;
	/** 默认列表limit */
	const DEAFULT_LIMIT = 10;
	/** 导出时的limit */
	const DOWNLOAD_LIMIT = 500;
	/** 默认页数 */
	const DEAFULT_PAGE = 1;
	/** 默认开始时间戳 */
	protected $_default_start_time = 0;

	// 前置操作
	public function before_action($action = '') {

		$this->_today_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . ' 00:00:00');
		$this->_tomorrow_time = $this->_today_time + 86400;
		$this->_yesterday_time = $this->_today_time - 86400;

		$this->_default_start_time = $this->_tomorrow_time - self::WEEK_DAYS * 86400;

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		return true;
	}


	/**
	 * 下载输出至浏览器
	 * @param $zipname
	 */
	protected function _put_header($zipname) {

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
	protected function _clear($path) {

		$dh = opendir($path);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				unlink($path . $file);
			}
		}

		return true;
	}
}