<?php
/**
 * voa_errcode_oa_showroom
 * OA travel 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_showroom {

	const DELETE_ARTICLE_FAILED = '1005000:删除文章失败';
	const DELETE_CATEGORY_FAILED = '1005001:删除目录失败';
	const DELETE_CATEGORY_FAILED1 = '1005002:目录下有文章，不能删除';
	const EDIT_ARTICLE_FAILED = '1005003:编辑文章失败';
	const ADD_ARTICLE_FAILED = '1005004:新增文章失败';
	const ARTICLE_TITLE_LENGTH_ERROR = '1005005:文章标题长度应该介于 %s 到 %s 个字符之间';
	const ARTICLE_TITLE_LENGTH_MAX_ERROR = '1005006:文章标题长度不能超过 %s 个字符';
	const AUTHOR_LENGTH_ERROR = '1005005:作者名字长度应该介于 %s 到 %s 个字符之间';
	const AUTHOR_LENGTH_MAX_ERROR = '1005006:作者名字长度不能超过 %s 个字符';
	const CATEGORY_TITLE_LENGTH_ERROR = '1005005:目录标题长度应该介于 %s 到 %s 个字符之间';
	const CATEGORY_LENGTH_MAX_ERROR = '1005006:目录标题长度不能超过 %s 个字符';
	const ARTICLE_NOT_EXIST = '1005007:文章不存在';
	const ARTICLE_NO_RIGHT ='1005008:没有查看该文章的权限';
	const CATEGORY_ID_ERROR ='1005009:目录ID不合法';

}
