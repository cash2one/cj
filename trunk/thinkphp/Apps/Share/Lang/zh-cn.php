<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP 简体中文语言包
 */
return array(


	// sms 错误
	'_ERR_SHARE_MATERIAL_ID' => '100:请传递正确素材id',
	'_ERR_PARAMS' => '100:参数错误',
	'_ERR_SHARE_STATUS' => '100:素材状态参数错误',
	'_ERR_STRLEN_MAX' => '100:素材标题不能超过64个字符',
	'_ERR_FILE_ERROR' => '100:附件格式错误',
	'_ERR_STRLEN_EMPEY' => '100:标题不能为空',


	'_ERR_SMS_ACCOUNT_OR_PASSWD_IS_EMPTY' => '2000:sms 的账号或密码为空',
	'_ERR_SMS_MOBILE_IS_EMPTY' => '2001:sms 手机号码为空',
	'_ERR_SMS_MSG_IS_EMPTY' => '2002:sms 消息内容为空',
	'_ERR_SMS_SUBMIT_ERROR' => '2003:sms 提交错误',
	'_ERR_SMS_SEND_ERROR' => '2004:sms 发送错误 [{$error}]',
	'_ERR_SERVICE_MODEL_UN_INIT' => '2005:Model 未初始化',
	'_ERR_BEFORE_ACTION' => '2006:前置方法 [{$action}] 调用错误',
	'_ERR_AFTER_ACTION' => '2007:后置方法 [{$action}] 调用错误',
	'_ERR_DEFAULT' => '2008:系统繁忙, 请稍后再试',
	'_ERR_PHPRPC_INIT_PARAMS_EMPTY' => '2009:PHPRPC 初始化参数为空',
	'_ERR_WHERE_FIELD_INVALID' => '2010:SQL 查询 WHERE 条件字段错误',
	'_ERR_SET_FIELD_INVALID' => '2011:SQL 查询 SET 字段错误',
	'_ERR_UPLOAD_FILE_SIZE_INVALID' => '2012:上传文件大小不符',
	'_ERR_UPLOAD_FILE_MIME_INVALID' => '2013:上传文件MIME类型不允许',
	'_ERR_UPLOAD_FILE_TYPE_INVALID' => '2014:上传文件类型不允许',
	'_ERR_UPLOAD_FILE_INVALID' => '2015:非法上传文件！',
	'_ERR_UPLOAD_FILE_NOT_NULL' => '2016:没有选择上传文件',
	'_ERR_PHPEXCEL_READABLE_NO' => '2017:无法读取上传的 Excel 文件',
	'_ERR_PHPEXCEL_NOT_VOA_EXCEL_TPL' => '2018:上传的文件不是标准的云工作模板格式，请使用下载的模板',
	'_ERR_PHPEXCEL_DATA_IS_EMPTY' => '2019:没有读取到有效的数据',
	'_ERR_PHPEXCEL_TITLE_IS_EMPTY' => '2020:标题栏行号不存在',
	'_ERR_PHPEXCEL_FILE_NOT_EXISTS' => '2021:文件不存在',
	'_ERR_PHPEXCEL_FILE_CAN_NOT_OPEN' => '2022:权限不足'
);
