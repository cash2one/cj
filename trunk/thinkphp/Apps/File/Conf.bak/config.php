<?php
return array (
	//'配置项'=>'配置值'
	'pageminsize'    => 1,
	'pagemaxsize'    => 50,
	'limit'          => 10,
	'msg_limit'      => 5,

	//文件夹名称长度
	'file_name_len'   => 20,
	//文件夹最大分级数
	'folder_maxlevel' => 10,
	//评论内容长度限制
	'contentlength'   => '140',
	//分页条数范围及默认条数
	'limitminsize'    => '1',
	'limitmaxsize'    => '200',
	'mrlimit'         => '100',
	'static_path'     => 'http://'.$_SERVER['HTTP_HOST'].'/file_static',
	// 静态根目录
	'staticdir'       => '/data/attachments/',
	// 允许上传文件的最大值(字节)
	'upload_maxSize'  => 20 * 1024 * 1024,
	// 缩略图最大宽度
	'thumbMaxWidth'   => '400,100',
	// 缩略图最大高度
	'thumbMaxHeight'  => '400,100',
	// 缩略图的文件前缀
	'thumbPrefix'     => 'm_,s_',
	// 最大执行时间
	'max_execute_time'     => 200,
	// 最大可用内存
	'memory_limit'     => '512M'
);
