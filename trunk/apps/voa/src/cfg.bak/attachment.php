<?php
/**
 * 附件配置文件
 *
 * $Author$
 * $Id$
 */

/** 用户站点信息缓存目录 */
$conf['dir'] = '/data/attachments';
$conf['pemdir'] = '/data/pem';

/** 允许上传的文件类型 */
$conf['file_type'] = array(
	'jpg', 'png', 'jpeg', 'gif', 'zip', 'rar', 'txt', 'doc', 'gz', 'tar', '7z',
	'pdf', 'pem', 'mov', '3gp', 'mp4', 'mp4v', 'm4v', 'mkv', 'avi', 'flv', 'f4v',
	'wmv', 'wm', 'asf', 'asx', 'rm', 'rmvb', 'ra', 'ram', 'mpg', 'mpeg', 'mpe',
	'vob', 'dat'
);

/** 缩微图宽度 */
$conf['thumb_widths'] = array(45, 100, 640);
$conf['thumb_quality'] = 75;
$conf['thumb_fix_ratio'] = 1;
$conf['thumb_fix_width'] = -1;
$conf['thumb_fix_height'] = -1;

/** 允许上传文件的最大值(字节) */
$conf['max_size'] = 10 * 1024 * 1024;

/** 附件浏览基本url:构造 /attachment/read/[at_id] 来访问 */
$conf['attach_url'] = '/attachment/read/';

/** 文件mime头 */
$conf['file_mime'] = array(
	'application/msword' => 'doc',
	'application/pdf' => 'pdf',
	'application/vnd.ms-excel' => 'xls',
	'application/vnd.ms-powerpoint' => 'ppt',
	'application/vnd.ms-works' => 'wps',
	'application/x-gzip' => 'gz',
	'application/x-tar' => 'tar',
	'application/zip' => 'zip',
	'audio/mpeg' => 'mp3',
	'audio/x-wav' => 'wav',
	'image/bmp' => 'bmp',
	'image/gif' => 'gif',
	'image/jpeg' => 'jpg',
	'image/png' => 'png',
	'image/x-icon' => 'ico',
	'text/css' => 'css',
	'text/html' => 'html',
	'text/plain' => 'txt',
	'video/mpeg' => 'mpg'
);
