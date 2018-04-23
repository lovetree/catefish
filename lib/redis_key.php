<?php
//============================================================
// Redis
//============================================================
defined('R_USER_TIMEOUT') ? :define('R_USER_TIMEOUT', 1800);                 //用户数据过期时间
defined('R_NOTICE_TIMEOUT') ? :define('R_NOTICE_TIMEOUT', 300);                //公告广播数据过期时间
defined('R_GOODS_TIMEOUT') ? :define('R_GOODS_TIMEOUT', 300);                 //商城商品数据过期时间
defined('RK_TOKEN') ? :define('RK_TOKEN', 'token@');                   //键格式 - token
defined('R_GAME_DB') ? :define('R_GAME_DB', 0);                         //用户数据库
defined('R_AGENT_DB') ? :define('R_AGENT_DB', 1);                         //代理数据库
defined('R_SESSION_DB') ? :define('R_SESSION_DB', 2);                      //session据库
defined('R_SMS_DB') ? :define('R_SMS_DB', 3);                          //短信数据库
defined('R_EMAIL_DB') ? :define('R_EMAIL_DB', 4);                        //邮件数据库
defined('R_MESSAGE_DB') ? :define('R_MESSAGE_DB', 5);                      //消息数据库(公告，广播等等)
defined('R_BENEFITS_DB') ? :define('R_BENEFITS_DB', 6);                     //福利数据库(签到，分享，任务)
defined('R_REPORT') ? :define('R_REPORT', 7);                          //上报数据
defined('RK_USER_INFO') ? :define('RK_USER_INFO', 'info@');                //键格式 - 用户基本数据(hash表)
defined('PK_USER_SMS') ? :define('PK_USER_SMS', 'sms@');                  //键格式 - 用户短信数据(有序集合sorted set score保存短信发送次数)
defined('PK_USER_EMAIL') ? :define('PK_USER_EMAIL', 'email@');              //键格式 - 用户邮件数据(有序集合sorted set score保存邮件id号)
defined('PK_USER_SYS_EMAIL') ? :define('PK_USER_SYS_EMAIL', 'sys_email');              //键格式 - 系统邮件数据(有序集合sorted set score保存邮件id号)
defined('PK_USER_EXLOG') ? :define('PK_USER_EXLOG', 'exlog@');              //键格式 - 用户兑换记录
defined('RK_NOTICE_LIST') ? :define('RK_NOTICE_LIST', 'notice_list');        //键格式 - 公告数据(有序集合sorted set score保存公告id号)
defined('RK_CAROUSEL_LIST') ? :define('RK_CAROUSEL_LIST', 'carousel_list');        //键格式 - 轮播图数据
defined('RK_ACTIVITY') ? :define('RK_ACTIVITY', 'activity');        //键格式 - 活动(hash表)
defined('RK_ROOM_LIST') ? :define('RK_ROOM_LIST', 'room_list');        //键格式 - 房间列表 string方式存储
defined('RK_BROADCAST_LIST') ? :define('RK_BROADCAST_LIST', 'broadcast_list');      //键格式 - 广播数据(有序集合sorted set score保存广播开始时间)
defined('RK_USER_BROADCAST') ? :define('RK_USER_BROADCAST', 'broadcast@');          //键格式 - 用户广播数据上一次获取的数据记录(hash表，string方式存储， 数据以json方式存储 已id号为键值)
defined('RK_BROADCAST_LIST_1') ? :define('RK_BROADCAST_LIST_1', 'broadcast_list_1');
defined('RK_USER_BROADCAST_LIST') ? :define('RK_USER_BROADCAST_LIST', 'user_broadcast_list'); //键格式 - 用户广播数据(有序集合sorted set score保存广播开始时间)
defined('RK_USER_EXPLOITS_WIN') ? :define('RK_USER_EXPLOITS_WIN', 'exp_win@');         //键格式 - 用户战绩数据 - 胜场
defined('RK_USER_EXPLOITS_LOSE') ? :define('RK_USER_EXPLOITS_LOSE', 'exp_lose@');       //键格式 - 用户战绩数据 - 败场
defined('RK_USER_EXPLOITS_TOTAL') ? :define('RK_USER_EXPLOITS_TOTAL', 'exp_total@');     //键格式 - 用户战绩数据 - 总场
defined('PK_RANK_GOLD_DATE') ? :define('PK_RANK_GOLD_DATE', 'rank_gold_date');      //键格式 - 金币排行榜更新日期
defined('PK_RANK_CREDIT_DATE') ? :define('PK_RANK_CREDIT_DATE', 'rank_credit_date');        //键格式 - 钻石排行榜更新日期
defined('PK_RANK_EMERALD_DATE') ? :define('PK_RANK_EMERALD_DATE', 'rank_emerald_date');      //键格式 - 绿宝石排行榜更新日期
defined('PK_RANK_WINNER_DATE') ? :define('PK_RANK_WINNER_DATE', 'rank_winner_date');        //键格式 - 赢家排行榜更新日期
defined('RK_RANK_GOLD') ? :define('RK_RANK_GOLD', 'rank_gold');            //键格式 - 金币排行榜   list方式存储（排名从大到小）， 数据以json方式存储，包含用户ID(uid)，昵称(nickname)，金币(amount)
defined('RK_RANK_CREDIT') ? :define('RK_RANK_CREDIT', 'rank_credit');            //键格式 - 钻石排行榜   list方式存储（排名从大到小）
defined('RK_RANK_EMERALD') ? :define('RK_RANK_EMERALD', 'rank_emerald');            //键格式 - 绿宝石排行榜   list方式存储（排名从大到小）
defined('RK_RANK_WINNER') ? :define('RK_RANK_WINNER', 'rank_winner');        //键格式 - 赢家排行榜   list方式存储（排名从大到小）， 数据以json方式存储，包含用户ID(uid)，昵称(nickname)，金币(amount)
defined('RK_GAME_LIST') ? :define('RK_GAME_LIST', 'game_list');            //键格式 - 游戏列表     string方式存储， 数据以json方式存储，包含用户游戏ID(gid)，版本号(version)，名称(name)，类型(mode)
#defined('') ? :define('RK_NOTICE_LIST', 'notice_list');        //键格式 - 广播公告列表  string方式存储， 数据以json方式存储，包含ID(nid)，内容(content)，类型(type)
defined('RK_GOODS_LIST') ? :define('RK_GOODS_LIST', 'goods_list@');          //键格式 - 商品列表     string方式存储， 数据以json方式存储，包含ID(gid)，名称(name)，图片(img_url)，描述(desc)，价格(price)，版本号(version)
defined('PK_USER_SIGN') ? :define('PK_USER_SIGN', 'sign@');                //键格式 - 用户每月签到记录，只记录单月记录，删除其他月份记录
defined('PK_SIGN_RULE') ? :define('PK_SIGN_RULE', 'sign_rule');            //键格式 - 签到奖励规则 基本数据(hash表)
defined('PK_USER_SHARE') ? :define('PK_USER_SHARE', 'share@');              //键格式 - 用户分享记录 基本数据(hash表)
defined('PK_USER_SHARE_INVITE') ? :define('PK_USER_SHARE_INVITE', 'share_invite@');       //键格式 - 用户分享临时表 基本数据(string，具有有效期)
defined('PK_SHARE_RULE') ? :define('PK_SHARE_RULE', 'share_rule');          //键格式 - 分享奖励规则 基本数据(hash表)
defined('PK_ONLINE_RULE') ? :define('PK_ONLINE_RULE', 'online_rule');        //键格式 - 在想奖励规则 基本数据(string表)
defined('PK_USER_ONLINE') ? :define('PK_USER_ONLINE', 'online@');            //键格式online
defined('PK_VIP_RULE') ? :define('PK_VIP_RULE', 'vip_rule');              //键格式vip规则
defined('PK_FISH_SIGN') ? :define('PK_FISH_SIGN', 'fish_sign');              //捕鱼签到，(hash表)
defined('PK_VIP_DAY') ? :define('PK_VIP_DAY', 'vip_day');              //捕鱼vip每日奖励，(hash表)
defined('PK_REPORT_USERINFO') ? :define('PK_REPORT_USERINFO', 'report_userinfo');   //用户信息上报
defined('PK_TASK_RULE') ? :define('PK_TASK_RULE', 'task_rule');   //任务规则信息 string类型json
defined('PK_TASK_DAY') ? :define('PK_TASK_DAY', 'task_day');              //每日任务奖励，(hash表)
defined('PK_FISH') ? :define('PK_FISH', 'fish');              //鱼种信息，(hash表)
defined('PK_MODEL') ? :define('PK_MODEL', 'model');                    //模块信息，(string)
defined('PK_SENSITIVE_WORD') ? :define('PK_SENSITIVE_WORD', 'sensitive_word');  //敏感字，(string)
defined('RK_AL_USER_AGENT') ? :define('RK_AL_USER_AGENT', 'al_user_agent@');//用户代理信息(hash)
defined('RK_AS_AGENT_PARENT') ? : define('RK_AS_AGENT_PARENT', 'as_agent_parent@');//上级代理链(string)
defined('RK_PLAT_VER') ? : define('RK_SYS_CONFIG', 'sys_config');//系统配置(hash)
defined('RK_GS_ORDER_FLAG') ? : define('RK_GS_ORDER_FLAG', 'gs_order_flag@');//订单计数(string)
defined('RK_BZ_INVITE_USERS') ? : define('RK_BZ_INVITE_USERS', 'bz_invite_user');//用户推荐关系(set)
defined('RK_GL_GOLD_LOG') ? : define('RK_GL_GOLD_LOG', 'rk_gl_gold_log');//金币流水(list)

