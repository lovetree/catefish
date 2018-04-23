function initNav() {
    $('.sidebar-nav li a').click(function (e) {
        $('iframe[name=right]').attr('src', $(this).attr('href'));
        e.preventDefault();
    });
}

$(function () {


    $.post('user/info', function (json) {
        var html = '';
        if (json.result == 0) {
            //data.data.info
            for (var i = 0; i < json.data.nav.length; i++) {
                html += '<a href="#dashboard-menu' + i + '" class="nav-header collapsed" data-toggle="collapse" aria-expanded="false"><i class="icon-dashboard"></i>' + json.data.nav[i].name + '</a>';
                if (json.data.nav[i].sub.length > 0) {
                    html += '<ul id="dashboard-menu' + i + '" class="nav nav-list collapse" aria-expanded="false" style="height: 0px;">';
                    for (var j = 0; j < json.data.nav[i].sub.length; j++) {
                        if(json.data.nav[i].sub[j].url  == ''){
                            continue;
                        }
                        html += '<li><a target="right" href="' + json.data.nav[i].sub[j].url + '">' + json.data.nav[i].sub[j].label + '</a></li>';
                    }
                    html += '</ul>';
                }
            }
            $(".sidebar-nav").html(html);
            $('[data-ctrl-var=admin_name]').text(json.data.info.username);
            $('#sidebar-nav a.nav-header').click(function () {
                $(this).siblings('ul').each(function () {
                    $(this).removeClass('in');
                })
            });
        } else {
            window.parent.location.href = 'sign-in.html';
        }
        initNav();
    }, 'json');

});

