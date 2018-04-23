<?php
$tpl = '<?php';
$tpl .= "\n";

$tpl .= '<!DOCTYPE html>';
$tpl .= '<html lang="en">';//html begin
$tpl .= '<head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
        <title>' . $title . '</title>
        <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="css/theme.css">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <link rel="stylesheet" type="text/css" href="css/tables.css" />
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/j.dt.min.js"></script>
        <script type="text/javascript" src="js/moment.js"></script>
        <style>
            table#tab input,table#tab textarea{
                margin-top:5px;
            }
            table#tab td{ height: 70px;}
            table#tab .pad-l{padding-left:30px;}
            table#tab .sel{float:left; border-radius:0px; margin-bottom:5px; max-width:200px; margin-right:30px;}
            table#tab .wid{width:100px; margin:0 10px 5px 0; float:left; vertical-align:middle;}
            table#tab .delspan{cursor:pointer; font-size:12px; font-weight:normal;}
            table#tab td.seltd{ border: 1px solid #ccc;}
            table#tab div.box{ max-height:150px; overflow-y:auto; padding:10px 0 5px 10px;}
            table#tab span.inser{border: 1px solid #ccc; font-size:12px; padding:3px 10px; float:right; cursor:pointer;}
            table#tab textarea.tex{ height:70px; line-height:20px;  padding:5px 7px; resize:none; border-radius:0px;}
        </style>
    </head>';

$tpl .= "\n";
$tpl .= '<body class="" style="background: #FFFFFF;">';//body begin
$tpl .= '<div class="header">
            <h1 class="page-title">'.$title.'</h1>
        </div>';

$tableHeads = '';
foreach ($columns as $column){
    $tableHeads .= "\t\t\t\t\t\t\t\t<th>" .($column['column_comment'] ?$column['column_comment']: $column['column_name']) . '</th>'."\n";
}
$tpl .= '<div class="container-fluid">
            <div class="row-fluid">
                <div class="block span6">

                    <table class="table display block-body collapse in" id="example">		
                        <input type="button" name="btnadd" id="btnadd" class="input_btn_h input-table" value="新增" data-ctrl-btn="newAction"/>
                        <input type="button" name="btndelete" id="btndelete" class="input_btn_h input-table" value="删除" data-ctrl-btn="deleteAction"/>
                        <input type="button" name="btntype" id="btntype" class="input_btn_h input-table" value="查询" />
                        <thead class="theadtbody">
                            <tr>
                                <th><input type="checkbox" id="checkAll" onclick="check_all();"></th>
                                '.$tableHeads.'
                                <th>管理</th>
                            </tr>
                        </thead>
                        <tbody class="theadtbody"></tbody>
                    </table>
                </div>
            </div>
        </div>';
$tpl .= '<footer><hr></footer>';
$tpl .= '</div>';
unset($tableHeads);

$editorStr = '';
for($i=0,$len=count($columns); $i<$len; $i++){
    if($columns[$i]['column_name'] == 'id'){
        continue;
    }
    
    $column = $columns[$i]['column_comment'] ?$columns[$i]['column_comment']: $columns[$i]['column_name'];
    $editorStr .= '<tr>';
    $editorStr .= '<td>'.$column.'：<input class="form-control" name="'.$columns[$i]['column_name'].'" type="text" placeholder="'.$column.'" ></td>'."\n";
    $editorStr .= '</tr>';
    $editorStr .= "\n\n";
}
$tpl .= '<div style="display: none;" class="edit-box">
        <form method="post" onsubmit="return submitF(this)">
            <input type="hidden" name="id"/>
            <table id="tab" border="0" width="100%">
                '.$editorStr.'            
                <tr>
                    <td colspan="2" style="padding-top:20px;">
                        <div class="form-group">
                            <input type="submit" class="form-control" value="提交">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>';

unset($editorStr);

$tpl .= '</body>';//body end
$tpl .= '</html>';//html end
$tpl .= "\n";
$tpl .= '<script src="js/jquery.form.js"></script>';
$tpl .= "\n";
$tpl .= '<script type="text/javascript" src="js/bootstrap.minv3.js"></script>';
$tpl .= "\n";
$tpl .= '<script type="text/javascript" src="js/bootstrap-dialog.min.js"></script>';
$tpl .= "\n";
$tpl .= '<script type="text/javascript" src="js/main.js"></script>';
$tpl .= "\n";
$tpl .= '<script type="text/javascript" src="js/date.js"></script>';
$tpl .= "\n";
$tpl .= '<script type="text/javascript">';//script begin
$tpl .= "\n";
$mDataStr = '';
$cDataStr = '';
foreach ($columns as $column){
    $mDataStr .= "\t\t\t{'mDataProp':'".$column['column_name']."'},\n";
    $cDataStr .= "\t\t\t$(row).data('data-".$column['column_name']."', data.".$column['column_name'].");\n";
}
$tpl .= '$(function () {
        var table = $(\'#example\').dataTableAjax("'.$urlList.'", {
            \'bLengthChange\': false,
            \'iDisplayLength\': 10,
            \'searching\': true,
            \'processing\': true,
            \'serverSide\': true,
            "autoWidth": false,
            \'ordering\': false,
            \'aoColumns\': [
                {\'mDataProp\': \'id\', "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).html("<input type=\'checkbox\' name=\'line-check\' class=\'check\' value=\'" + oData.id + "\'>");
                    }
                },
            '.$mDataStr.'
                {\'mDataProp\': \'manager\', "render": function (nTd, sData, oData, iRow, iCol) {
                        return \'&nbsp;<button data-ctrl-btn="editAction">编辑</button>\';
                    }}
            ],
            createdRow: function (row, data, dataIndex) {
                '.$cDataStr.'
            },
            postData: {
                \'query\': function () {
                    return $(\'input[aria-controls=example][type=search]\').val();
                }
            },
        });
        $(\'input[aria-controls=example][type=search]\').unbind();
        $(\'input[aria-controls=example][type=search]\').keyup(function (e) {
            if (e.keyCode === 13) {
                table.reload();
            }
        });
        $(\'input[type=button][id=btntype]\').click(function () {
            table_reload();
        });

        window.table_reload = function () {
            $(\'input[aria-controls=example][type=search]\').focus();
            table.reload();
        };
    });';

unset($mDataStr, $cDataStr);
$tpl .= "\n\n";

$tpl .= 'function deleteAction() {
        var list = [];
        $(\'input[name=line-check]:checked\').each(function () {
            list.push(this.value);
        });
        if (list.length == 0) {
            return;
        }
        $.post(\''.$urlDel.'\', {
            id: list
        }, function (json) {
            if (json.result == 0) {
                alert(\'删除成功\');
                table_reload();
            } else {
                alert(json.msg);
            }
        }, \'json\');
    }';

$tpl .= "\n\n";
$tpl .= 'function newAction(){
        editAction(true);
    }';

$tpl .= "\n\n";

$varStr = '';
$updateStr = '';
foreach ($columns as $column){
    $varStr .= "\n" . 'var msg_'.$column['column_name'].' = $(this).parents(\'tr\').data(\'data-'.$column['column_name'].'\');';
    $updateStr .= "\n" . '$(this).find(\'input[name='.$column['column_name'].']\').attr(\'value\', msg_'.$column['column_name'].');';
}

$tpl .= 'function editAction(bNew){
        '.$varStr.'
        dialog = $(\'.edit-box form\').dialog({
            title: \''.$title.' - \' + (bNew ? \'新增\' : \'修改\'),
            nl2br: false
        }, function () {
            $(this).attr(\'id\', \'edit_message\');
            if(!bNew){
                '.$updateStr.'
            }
        });
        var $form = $(\'form[id=edit_message]\');
        $form.find(\'[widget-date]\').each(function(){
            var id = \'widget-date-\' +  $(this).attr(\'widget-date\');
            $(this).attr(\'id\', id);
            new Calender({id: id});
        });
    }';

$tpl .= '
//创建、更新标志
var isNew = false;
//弹窗对象
var dialog = null;
function submitF(obj){
    $form = $(obj);
    var num=0;
    $form.find(\'input[type=text]\').each(function(){
        var txt=$(this).val();
        if($.trim(txt)==""){
            num++;
        }
    });
    if(num>0){
        alert(\'请将信息填写完整！\');
        return false;
    }

    $form.ajaxSubmit({
        url: \''.$urlSave.'\',
        dataType: \'json\',
        success: function (json) {
            if (json.result == 0) {
                alert(isNew ? \'创建成功\' : \'修改成功\');
                dialog.close();
                table_reload();
            } else {
                alert(json.msg);
            }
        }
    });
    return false;
}
';

unset($varStr, $updateStr);
$tpl .= "\n\n";
$tpl .= '</script>';//script end

return $tpl;











    

    
