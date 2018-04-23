function showMsg(msg,callback){
    var color = '';
    if(callback === true){
        color = 'style="color:red;"';
    }
    var e = bootbox.dialog({
        message: "<span "+color+">"+msg+"</span>",
        show:!1,
        width:'400px'
    });
    e.on("show.bs.modal",function() {
        setTimeout(function(){
           e.modal('hide');
        },1000)
    });
    if($.isFunction(callback)){
        e.on("hidden.bs.modal",callback);
    };
    e.modal("show");
}