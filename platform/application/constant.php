<?php

//============================================================
// 返回状态吗
//============================================================
define('RCODE_SUCC', 0);              //成功；非0值全部为失败
define('RCODE_FAIL', 1);              //失败
define('RCODE_DENY', 1001);           //无权限
define('RCODE_REQ_ILLEGAL', 1002);    //非法的请求
define('RCODE_FAIL_SIGN', 1003);      //签名错误
define('RCODE_ARG_ILLEGAL', 1004);    //参数错误
define('RCODE_UNSUPPORT', 1005);      //版本不一致
define('RCODE_BUSY', 1006);           //服务器繁忙
define('RCODE_NEED_LOGIN', 2001);     //需要登录

//============================================================
// Session
//============================================================
define('SES_LOGIN', 'SES_LOGIN');               //用户登录信息
define('SES_PERMISSION', 'SES_PERMISSION');               //用户权限数据

//============================================================
// Redis
//============================================================
define('R_GAME_DB', 0);                         //用户数据库
define('R_AGENT_DB', 1);                         //代理数据库
define('PK_FISH', 'fish');                      //鱼种信息，(hash表)
define('PK_FISH_COEFFICIENT', 'FishCoefficient');  //鱼种信息，(hash表)
define('PK_MODEL', 'model');                    //模块信息，(string)
define('PK_SENSITIVE_WORD', 'sensitive_word');  //敏感字，(string)
define('PK_GIFT_LIST', 'gift_list');  //礼物，(string)
define('R_EMAIL_DB', 4);                        //邮件数据库
define('R_MESSAGE_DB', 5);                      //消息数据库(公告，广播等等)
define('R_BENEFITS_DB', 6);                     //福利数据库(签到，分享)
define('R_USER_TIMEOUT', 1800);                 //用户数据过期时间
define('RK_TOKEN', 'token@');                   //键格式 - token
define('RK_BROADCAST_LIST', 'broadcast_list');      //键格式 - 广播数据(有序集合sorted set score保存广播开始时间)
define('RK_BROADCAST_LIST_1', 'broadcast_list_1'); //202广播
define('RK_ROOM_LIST', 'room_list');        //键格式 - 房间列表 string方式存储
define('RK_NOTICE_LIST', 'notice_list');        //键格式 - 公告数据(有序集合sorted set score保存公告id号)
define('PK_SHARE_RULE', 'share_rule');          //键格式 - 分享奖励规则 基本数据(hash表)
define('RK_ACTIVITY', 'activity');        //键格式 - 活动(hash表)
define('RK_CAROUSEL_LIST', 'carousel_list');        //键格式 - 轮播图数据
define('PK_TASK_RULE', 'task_rule');   //任务规则信息 string类型json
define('PK_ONLINE_RULE', 'online_rule');        //键格式 - 在想奖励规则 基本数据(string表)
define('PK_VIP_RULE', 'vip_rule');              //键格式vip规则
define('PK_USER_EMAIL', 'email@');              //键格式 - 用户邮件数据(有序集合sorted set score保存邮件id号)
define('PK_USER_SYS_EMAIL', 'sys_email');              //键格式 - 系统邮件数据(有序集合sorted set score保存邮件id号)
define('RK_GAME_GOLD_FLOW', 'game_gold_flow');//用户金币流水（list）

//============================================================
// 文件路径配置
//============================================================

define('APP_CFG_NAV', APP_PATH . '/conf/nav.conf.php');     //导航模块数据配置文件路径
define('APP_CFG_ACTLOG', APP_PATH . '/conf/actlogs.conf.php');     //日志记录配置文件路径
define('APP_UPLOAD_DIR', APP_PATH . '/upload_files/');     //日志记录配置文件路径
