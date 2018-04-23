<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="/skin/css/bootstrap.min.css" />
	<link rel="stylesheet" href="/skin/css/font-awesome.min.css" />
	<link rel="stylesheet" href="/skin/css/ace.min.css" />
        <link rel="stylesheet" href="/skin/css/skin.css" />
	<!--[if IE 7]>
	<link rel="stylesheet" href="/skin/css/font-awesome-ie7.min.css" />
	<![endif]-->

	<!--[if lte IE 8]>
	<link rel="stylesheet" href="/skin/css/ace-ie.min.css" />
	<![endif]-->

	<!--[if lt IE 9]>
	<script src="/skin/js/html5shiv.js"></script>
	<script src="/skin/js/respond.min.js"></script>
	<![endif]-->
        <style>
            .header{
                background: -webkit-gradient(linear, left bottom, left top, color-stop(0, #e6e6e6), color-stop(1, #ffffff));
                border-bottom: 1px solid #cccccc;
                border-top:1px solid #ffffff;
                border-left:1px solid #ffffff;
                padding:0em 1.25em;
            }
            .header h1{
                margin:1em 0em;
                padding:0em;
                line-height:1.5em;
                color:#333;
            }
            .modal.fade .modal-dialog {
                -webkit-transition: -webkit-transform .3s ease-out;
                -o-transition:      -o-transform .3s ease-out;
                transition:         transform .3s ease-out;
                -webkit-transform: translate(0, -25%);
                -ms-transform: translate(0, -25%);
                -o-transform: translate(0, -25%);
                transform: translate(0, -25%);
            }
            .modal.in .modal-dialog {
                -webkit-transform: translate(0, 0);
                -ms-transform: translate(0, 0);
                -o-transform: translate(0, 0);
                transform: translate(0, 0);

            }

            .modal-content {
                position: relative;
                background-color: #fff;
                -webkit-background-clip: padding-box;
                background-clip: padding-box;
                border: 1px solid #999;
                border: 1px solid rgba(0, 0, 0, .2);
                border-radius: 6px;
                outline: 0;
                padding:1.5em;
                font-size: 14px;
                -webkit-box-shadow: 0 3px 9px rgba(0, 0, 0, .5);
                box-shadow: 0 3px 9px rgba(0, 0, 0, .5);
            }
            .mt5{margin-top: 5px;}
        </style>
</head>
<body>
    <div class="header">
        <h4 class="page-title"><?php echo $h_title?></h4>
    </div>
    &nbsp;&nbsp;
    <?php if(!isset($pHidden) || !$pHidden):?>
    <a class="btn" href="javascript:history.go(-1);"><i class="icon-last"></i>上一页</a>
    <?php endif;?>
    <?php if(!isset($rHidden) || !$rHidden):?>
    <a class="btn" href="javascript:window.location.reload();"><i class="icon-undo"></i>刷新</a>
    <?php endif;?>