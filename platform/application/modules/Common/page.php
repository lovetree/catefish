<div class="dataTables_info" id="example_info" role="status" aria-live="polite">
    显示第 <?php echo ($pageInfo['page']-1) * $pageInfo['pageSize'] + 1?> 至 <?php echo $pageInfo['page'] * $pageInfo['pageSize']?> 项结果,共 <?php echo $pageInfo['total']?> 项
</div>
<?php
$page = $pageInfo['page'];
$count = $pageInfo['pageCount'];
$url = $pageInfo['pageUrl'];
$size = $pageInfo['pageSize'];
?>
<?php if($pageInfo['pageCount'] > 1):?>
<div class="dataTables_paginate paging_simple_numbers" id="example_paginate">
    <a <?php if($pageInfo['page'] > 1):?>href="<?php echo $pageInfo['pageUrl'] . '?page=' . ($pageInfo['page']-1);?>"<?php endif;?> class="paginate_button previous <?php echo $pageInfo['page'] == 1 ? 'disabled':''?>" aria-controls="example" data-dt-idx="0" tabindex="0" id="example_previous">上页</a>
    <span>
<!--        <?php if($pageInfo['pageCount'] <=5):?>
            <?php for($i=1;$i<=$pageInfo['pageCount'];$i++){ ?>
            <a <?php if($i != $pageInfo['page']):?>href="<?php echo $pageInfo['pageUrl'] . '?page=' . $i;?>"<?php endif;?>class="paginate_button <?php echo $i == $pageInfo['page'] ? 'current':''?>" aria-controls="example" data-dt-idx="<?php echo $i;?>" tabindex="0">
                <?php echo $i;?>
            </a>
            <?php } ?>
        <?php endif;?>
        -->
        <?php if(($page - 1) > 1):?>
        <a href="<?php echo $url . "?page=1";?>" class="paginate_button" aria-controls="example" data-dt-idx="1" tabindex="0">1</a>
        <span>…</span>
        <?php endif;?>
        <?php if($page!=1):?>
        <a href="<?php echo $url . "?page=" . ($page-1)?>" class="paginate_button" aria-controls="example" data-dt-idx="1" tabindex="0"><?php echo $page-1?></a>
        <?php endif;?>
        <a class="paginate_button current" aria-controls="example" data-dt-idx="1" tabindex="0"><?php echo $page?></a>
        
        <?php if($page < $count):?>
        <a href="<?php echo $url . "?page=" . ($page+1)?>" class="paginate_button" aria-controls="example" data-dt-idx="1" tabindex="0"><?php echo $page+1?></a>
        <?php endif;?>
        <?php if(($count - $page) > 1):?>
        <span>…</span>
        <a href="<?php echo $url . "?page=" . $count?>" class="paginate_button" aria-controls="example" data-dt-idx="1" tabindex="0"><?php echo $count?></a>
        <?php endif;?>
    </span>
    <a <?php if($count > $page):?>href="<?php echo $url . "?page=" . ($page+1);?>"<?php endif;?> class="paginate_button next <?php echo $count == $page ? 'disabled':''?>" aria-controls="example" data-dt-idx="7" tabindex="0" id="example_next">下页</a>
</div>
<?php endif;?>
<!--
<a class="paginate_button " aria-controls="example" data-dt-idx="2" tabindex="0">2</a>
<a class="paginate_button " aria-controls="example" data-dt-idx="3" tabindex="0">3</a>
<a class="paginate_button " aria-controls="example" data-dt-idx="4" tabindex="0">4</a>
<a class="paginate_button " aria-controls="example" data-dt-idx="5" tabindex="0">5</a>-->