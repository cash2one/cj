<?php
/**
 * ueditor.php
 * ueditor 编辑器上传需要的参数配置项
 * 此处是全局设置，如应用内使用，请对应将个性的设置值进行覆盖
 * 具体配置说明，详见 http://fex.baidu.com/ueditor/#server-server_config
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/** 编辑器静态资源url */
$conf['ueditor_home_url'] = '/misc/ueditor/';

/** 全局编辑器默认配置 */
$conf['ueditor'] = array(
		/* 上传图片配置 */
		'imageActionName' => 'uploadimage',// 不要更改！
		'imageFieldName' => 'upfile',// 不要更改！
		'imageMaxSize' => 2048000,
		'imageAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp',),
		'imageCompressEnable' => true,
		'imageCompressBorder' => 1600,
		'imageInsertAlign' => 'none',
		'imageUrlPrefix' => '',
		'imagePathFormat' => 'auto',

		/* 涂鸦图片上传配置 */
		'scrawlActionName' => 'uploadscrawl',// 不要更改！
		'scrawlFieldName' => 'upfile',// 不要更改！
		'scrawlPathFormat' => 'auto',
		'scrawlMaxSize' => 2048000,
		'scrawlUrlPrefix' => '',
		'scrawlInsertAlign' => 'none',

		/* 截图工具上传配置 */
		'snapscreenActionName' => 'uploadimage',// 不要更改！
		'snapscreenPathFormat' => 'auto',
		'snapscreenUrlPrefix' => '',
		'snapscreenInsertAlign' => 'none',

		/* 抓取远程图片配置 */
		'catcherLocalDomain' => array('127.0.0.1', 'localhost', 'img.baidu.com',),
		'catcherActionName' => 'catchimage',// 不要更改！
		'catcherFieldName' => 'source',
		'catcherPathFormat' => 'auto',
		'catcherUrlPrefix' => '',
		'catcherMaxSize' => 2048000,
		'catcherAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp',),

		/* 上传视频配置 */
		'videoActionName' => 'uploadvideo',// 不要更改！
		'videoFieldName' => 'upfile',// 不要更改！
		'videoPathFormat' => 'auto',
		'videoUrlPrefix' => '',
		'videoMaxSize' => 102400000,
		'videoAllowFiles' => array(
				'.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg', '.ogg',
				'.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid',
		),

		/* 上传文件配置 */
		'fileActionName' => 'uploadfile',// 不要更改！
		'fileFieldName' => 'upfile',// 不要更改！
		'filePathFormat' => 'auto',
		'fileUrlPrefix' => '',
		'fileMaxSize' => 51200000,
		'fileAllowFiles' => array(
				'.png', '.jpg', '.jpeg', '.gif', '.bmp', '.flv', '.swf', '.mkv', '.avi',
				'.rm', '.rmvb', '.mpeg', '.mpg', '.ogg', '.ogv', '.mov', '.wmv', '.mp4',
				'.webm', '.mp3', '.wav', '.mid', '.rar', '.zip', '.tar', '.gz', '.7z',
				'.bz2', '.cab', '.iso', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx',
				'.pdf', '.txt', '.md', '.xml',
		),

		/* 列出指定目录下图片 */
		'imageManagerActionName' => 'listimage',// 不要更改！
		'imageManagerListPath' => '/ueditor/php/upload/image/',
		'imageManagerListSize' => 20,
		'imageManagerUrlPrefix' => '',
		'imageManagerInsertAlign' => 'none',
		'imageManagerAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp',),

		/* 列出指定目录下的文件配置 */
		'fileManagerActionName' => 'listfile',// 不要更改！
		'fileManagerListPath' => '/ueditor/php/upload/file/',
		'fileManagerUrlPrefix' => '',
		'fileManagerListSize' => 20,
		'fileManagerAllowFiles' => array(
				'.png', '.jpg', '.jpeg', '.gif', '.bmp', '.flv', '.swf', '.mkv', '.avi',
				'.rm', '.rmvb', '.mpeg', '.mpg', '.ogg', '.ogv', '.mov', '.wmv', '.mp4',
				'.webm', '.mp3', '.wav', '.mid', '.rar', '.zip', '.tar', '.gz', '.7z',
				'.bz2', '.cab', '.iso', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx',
				'.pdf', '.txt', '.md', '.xml',
		),
);
