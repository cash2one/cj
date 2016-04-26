<?php
/**
 * 新闻公告
 * voa_errcode_oa_news
 * OA travel 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_news {

	const NEWS_NOT_EXIST = '1006000:新闻公告不存在';
	const DELETE_NEWS_FAILED = '1006001:删除新闻公告失败';
	const NE_ID_ERROR = '1006002:公告ID不合法';
	const CONTENT_ERROR = '1006003:内容不合法';
	const TITLE_ERROR = '1006004:标题不合法';
	const CD_IDS_ERROR = '1006005:部门不合法';
	const U_IDS_ERROR = '1006006:人员不合法';
	const COVER_ID_ERROR = '1006005:封面ID不合法';
	const LIMIT_ERROR = '1006006:分页数不合法';
	const PAGE_ERROR = '1006005:分页不合法';
	const NCA_ID_ERROR = '1006006:分类ID不合法';
	const M_UID_ERROR = '1006007:用户ID不合法';
	const NEWS_NO_RIGHT ='1006008:没有查看该公告的权限';
	const CATEGORY_NAME_ERROR ='1006009:类型名不合法';
	const ORDERID_ERROR ='1006010:排序ID不合法';
	const NO_READ_DATA = '1006011:暂无已阅读数据';
	const CATEGORIES_NOT_EXIST = '1006012:类型不存在';
	const NO_ISSUE_ERROR = '1006013:没有权限发布公告';
	const M_UID_CHECK = '1000614:审批人员不合法';
	const NEWS_NO_EDIT = '1000615:没有编辑该公告权限';
	const NEWS_CHECK = '1000616:已审批过该公告';
	const ERR_LIKE_IP = '1000617:IP地址错误';
	const ERR_LIKE_UID = '1000618:用户ID出错';
	const ERR_LIKE_NEID = '1000619:新闻ID出错';
	const ERR_TIME_DES = '1000620:点赞时间间隔不能小于15秒！';
	const NEWS_NO_USER_RIGHT = '1000621:您没有权限查看';
	const ERR_SEND_NO_READ = '1000622:未读提醒发送时间小于6小时';
	const ERR_NO_READER = '1000623:未读人员不存在';
}
