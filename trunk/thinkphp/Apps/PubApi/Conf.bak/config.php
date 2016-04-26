<?php

return array(
	// '配置项'=>'配置值'
	'PAGE_MINSIZE' => 1,
	'PAGE_MAXSIZE' => 50,
	'LIMIT_DEF' => 10,

	// 静态根目录
	'STATICDIR'=> '/data/attachments/',
	// 允许上传文件的最大值(字节)
	'UPLOAD_MAXSIZE' => 20 * 1024 * 1024,
	// 缩微图
	'THUMB' => array(
		'MAX_WIDTH' => '400,100', // 缩略图最大宽度
		'MAX_HEIGHT' => '400,100', // 缩略图最大高度
		'PREFIX' => 'm_,s_' // 缩略图的文件前缀
	),

	// 允许上传的文件后缀
	'FILE_EXTS'=> array(
		'jpg', 'png', 'jpeg', 'ico', 'gif', 'txt', 'doc', 'pdf', 'xls', 'ppt',
		'wps', 'css', 'html'
	),
	// 允许上传的文件类型
	'FILE_TYPES'=> array(
		'image/jpeg', 'image/png', 'image/x-icon', 'image/gif', 'text/plain',
		'application/msword', 'application/pdf', 'application/vnd.ms-excel',
		'application/vnd.ms-powerpoint', 'application/vnd.ms-works', 'text/css',
		'text/html'
	),

);
