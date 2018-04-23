var user = 'player/user/list'; //用户管理
var admin = 'json/admin.json'; //管理员管理
var adminnum = 'json/adminnum.json'; //系统统计
var anquan = 'log/Security/list'; //安全日志
var bank = 'json/bank.json'; //银行记录
var blacklist = 'json/blacklist.json'; //手机黑名单
var brand = 'json/brand.json'; //奖牌管理
var cardshop = 'json/cardshop.json'; //点卡管理
var chongzhinews = 'json/chongzhinews.json'; //充值记录
var chongzhinewsday = 'json/chongzhinewsday.json'; //充值每日记录
var daoju = 'json/daoju.json'; //道具管理
var feedback = 'system/feedback/list'; //回馈管理
var game = 'player/gamerecourd/list'; //游戏记录
var gameadmin_1 = 'json/gameadmin_1.json';
var gameadmin_2 = 'json/gameadmin_2.json';
var gameadmin_3 = 'json/gameadmin_3.json';
var gameadmin_4 = 'json/gameadmin_4.json';
var gameadmin_5 = 'json/gameadmin_5.json';
var gameeasy = 'json/gameeasy.json';
var gamesys = 'json/gamesys.json';
var giving = 'json/giving.json';
var homeadmin = 'json/homeadmin.json';
var homenum = 'json/homenum.json';
var inventory = 'json/inventory.json';
var jifen = 'json/jifen.json';
var kaxian = 'player/busyline/list';
var orders = 'recharge/order/list';
var money = 'player/gold/list';
var news = 'json/news.json';
var paodian = 'json/paodian.json';
var problem = 'json/problem.json';
var robot = 'json/robot.json';
var rotary = 'json/rotary.json';
var rules = 'json/rules.json';
var shop = 'json/shop.json';
var sum = 'json/sum.json';
var sysadmin = 'system/message/list';
var usereasy = 'json/usereasy.json';
var winlose = 'player/winorlose/list'; //游戏输赢记录
var popularity = 'player/popularity/list'; //游戏输赢记录
var gamewinlose = 'json/gamewinlose.json'; //游戏战绩查询
var userlimit = 'json/userlimit.json'; //用户名限制
var phonecode = 'json/phonecode.json'; //用户手机验证码
var tuiguang = 'json/tuiguang.json'; //推广明细
var tuiguangcw = 'json/tuiguangcw.json'; //财务明细
var juese = 'json/juese.json'; //角色管理

var prop='system/item/list'; //道具管理
var good='system/goods/list'; //商品管理
var notice='gamemt/notice/list'; //公告管理
var active='gamemt/active/list'; //活动管理

function ip2addr(ip, callback) {
    var self = this;
    var url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' + ip;
    $.getScript(url, function () {
        var addr = remote_ip_info.country + '-' + remote_ip_info.province + '-' + remote_ip_info.city;
        if (typeof (callback) == 'function')
            callback.call(self, addr);
    });
}

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null)
        return unescape(r[2]);
    return null;
}


function check_all() {
    var mmm = document.getElementsByClassName("check");
    if (document.getElementById("checkAll").checked) {
        for (var i = 0; i < mmm.length; i++) {
            mmm[i].checked = 1;
        }
    } else {
        for (var a = 0; a < mmm.length; a++) {
            mmm[a].checked = 0;
        }
    }
}

function check_all2() {
    var mmm = document.getElementsByClassName("check2");
    if (document.getElementById("checkAll2").checked) {
        for (var i = 0; i < mmm.length; i++) {
            mmm[i].checked = 1;
        }
    } else {
        for (var a = 0; a < mmm.length; a++) {
            mmm[a].checked = 0;
        }
    }
}

function check_all3() {
    var mmm = document.getElementsByClassName("check3");
    if (document.getElementById("checkAll3").checked) {
        for (var i = 0; i < mmm.length; i++) {
            mmm[i].checked = 1;
        }
    } else {
        for (var a = 0; a < mmm.length; a++) {
            mmm[a].checked = 0;
        }
    }
}

function check_all4() {
    var mmm = document.getElementsByClassName("check4");
    if (document.getElementById("checkAll4").checked) {
        for (var i = 0; i < mmm.length; i++) {
            mmm[i].checked = 1;
        }
    } else {
        for (var a = 0; a < mmm.length; a++) {
            mmm[a].checked = 0;
        }
    }
}

function check_all5() {
    var mmm = document.getElementsByClassName("check5");
    if (document.getElementById("checkAll5").checked) {
        for (var i = 0; i < mmm.length; i++) {
            mmm[i].checked = 1;
        }
    } else {
        for (var a = 0; a < mmm.length; a++) {
            mmm[a].checked = 0;
        }
    }
}

/**
 * 获取游戏类型
 * @returns {string}
 */
function getGameTypes(){
    var html = '';

    $.ajax({
        url: 'gamemt/gametype/list',
        async: false,
        dataType:'json',
        success: function(res) {
            var data = res.data;
            for (var i=0, len=data.length; i<len; i++) {
                html += '<option value="' + data[i].id + '">' + data[i].game_name + '</option>';
            }
        }
    });

    return html;
}

/**
 * 获取游戏模式
 * @returns {string}
 */
function getGameModes(){
    var html = '';

    $.ajax({
        url: 'gamemt/gamemode/list',
        async: false,
        dataType:'json',
        success: function(res) {
            var data = res.data;
            for (var i=0, len=data.length; i<len; i++) {
                html += '<option value="' + data[i].id + '">' + data[i].mode_name + '</option>';
            }
        }
    });

    return html;
}

$(function () {
    //按钮点击
    $(document).on('click', '[data-ctrl-btn]', function (e) {
        var func = $(this).attr('data-ctrl-btn');
        eval('(' + func + ".call(this))");
        e.stopPropagation();
    });

    (function ($) {
        $.fn.dialog = function (opt, func) {
            var self = this.clone().show().get(0);
            if (typeof opt === 'function') {
                func = opt;
            }
            typeof (func) === 'function' && (func.call(self));
            var option = {
                message: self.outerHTML
            };
            option = $.extend(option, opt || {});
            return BootstrapDialog.show(option);
        };

        $.fn.juicer = function (dom, data) {
            var template = $(dom).html();
            var html = juicer(dom, data);
            $(this).html(html);
        }
    })(jQuery);

});

