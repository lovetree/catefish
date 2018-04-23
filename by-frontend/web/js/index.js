window.onload=function() {
    $('#fla-img').animate({right:'65px'},1000);
    //alert(num);
};
//首页最新游戏
    var wid=0;
    var num=0;
    $.ajax({
        url: 'gameicon/list',
        type: 'POST',
        dataType: 'json',
        success: function (reg) {
            if (reg.result == 0) {
                var data = reg.data, html = '';
                if (!data) {
                    return false;
                }
                html += '<img src="images/6_06.png" class="fla-l img-t2">';
                if (data.length < 4 || data.length == 4) {
                    $('.img-btn').hide();
                }
                for (var i = 0; i < data.length; i++) {
                    if (i == (data.length - 1)) {
                        html += '<a href="' + data[i].download_url + '"><dl>' +
                            '<dt><img src="' + data[i].img_url + '"></dt>' +
                            '<dd class="fon5">' + data[i].name + '</dd></dl></a>';
                    } else {
                        html += '<a href="' + data[i].download_url + '"><dl>' +
                            '<dt><img src="' + data[i].img_url + '"></dt>' +
                            '<dd class="fon5">' + data[i].name + '</dd></dl></a>' +
                            '<img src="images/sy_15.png" class="fla-l img-t">';
                    }
                }
                if (data.length == 4 || data.length > 4) {
                    html += '<img src="images/6_06.png" class="fla-l img-t2">';
                }
                html += '<div class="flex"></div>';
                $('#box-w').append(html);
                num=$('#box-w').find('a').size();
                wid=48*2+198*num+84*(num-1);
                $('#box-w').width(wid);
            } else {
                alert(reg.msg);
            }
        }
    });


    //首页最新游戏 自动轮播
    function btnlef() {
        var lef=Math.abs($('#box-w').position().left);
        if(lef==0){
            return false;
        }
        if(lef<1111){
            $('#box-w').animate({left:"0px"},500);
            return false;
        }
        $('#box-w').animate({left:-(lef-1144)},500);
    }

    function btnrit() {
        var lef=Math.abs($('#box-w').position().left);
        var wid2=wid-lef;
        if(wid2==1111||wid2<1111){
            return false;
        }
        if(1111<wid2&&wid2<2222){
            var ww=wid2-1111;
            $('#box-w').animate({left:-(lef+ww)},500);
            return false;
        }
        $('#box-w').animate({left:-(lef+1139)},500);
    }


    //轮播图
        var counts=0;
        $.ajax({
            url:'carousel/list',
            type:'POST',
            dataType:'json',
            success:function (reg) {
                if(reg.result==0){
                    var data=reg.data,html='',html2='';
                    if(!data){
                        return false;
                    }
                    counts=data.length-1;
                    for(var i=0;i<data.length;i++){
                        html+='<a href="'+data[i].jump_url+'"><img src="'+data[i].img_url+'"></a>';
                        if(i==0){
                            html2+='<li class="hover"></li>';
                        }else{
                            html2+='<li></li>';
                        }
                    }
                    $('#flashScroll').append(html);
                    $('#flash3But').append(html2);

                    //flash 动画轮播效果
                    var _index=0;
                    var flashInter=null;
                    $("ul#flash3But li").mouseover(function(){
                        clearInterval(flashInter);//关闭定时器
                        _index=$(this).index();
                        $(this).addClass("hover").siblings("li").removeClass("hover");
                        $("#flashScroll").stop().animate({left:-_index*506},500);

                    });

                    $("ul#flash3But li").mouseout(function(){
                        autoPlay();
                    });

                    //自动轮播的方法
                    function autoPlay(){
                        flashInter=setInterval(function(){
                            _index++;
                            if(_index>counts){_index=0;}
                            $("ul#flash3But li").eq(_index).addClass("hover").siblings("li").removeClass("hover");
                            $("#flashScroll").stop().animate({left:-_index*506},500);
                        },2000)

                    }
                    autoPlay();
                }else{
                    alert(reg.msg);
                }
            }
        });

        //游戏公告
        $.ajax({
            url:'news/list',
            type:'POST',
            dataType:'json',
            success:function (reg) {
                if(reg.result==0){
                    var data=reg.data,html='';
                    if(!data){
                        return false;
                    }
                    for(var i=0;i<data.length;i++){
                        if(i<7){
                            if((data[i].type=='公告') || (data[i].type=='活动')){
                                html+='<p><a href="newsnotice.html?id='+data[i].id+'">'+data[i].type+'&nbsp;&nbsp;|&nbsp;&nbsp;'+data[i].title+'</a><span class="fla-r cor4">'+data[i].publish_time+'</span></p>';
                            }else if(data[i].type == '攻略'){
                                html+='<p><a href="gameStr-detail.html?id='+data[i].id+'">'+data[i].type+'&nbsp;&nbsp;|&nbsp;&nbsp;'+data[i].title+'</a><span class="fla-r cor4">'+data[i].publish_time+'</span></p>';
                            }
                        }
                    }
                    $('#new-d').append(html);
                }else{
                    alert(reg.msg);
                }
            }
        });


/*qq咨询*/
$("#open-fixed").hide();
// $("#parent").show();

$("#close").click(function(){
    $("#parent").hide(500);
    $("#open-fixed").show(500);

});
$("#open-fixed").click(function(){
    $(this).hide(500);
    $("#parent").show(500);
});


$(function(){
//        折叠扇
    $(".content").hide();
    $(".active").show();
    $(".lists").click(function(){
        $(".content").hide(500);
        $(this).parent().children(".content").show(500);
    });


});