<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); hookscriptoutput('list_mskd');
block_get('6');?><?php include template('common/header'); ?><!--[name]!portalcategory_listtplname![/name]--><?php $list = array();?><?php $wheresql = category_get_wheresql($cat);?><?php $list = category_get_list($cat, $wheresql, $page);?><!-- {var_dump(<?php echo $list;?>);} -->
<!-- main start -->
<div class="dmain">
<div class="mleft">
<div class="mnav">
<h1>首页&gt;<span><?php echo $cat['catname'];?></span></h1>
<div class="mline"></div>
</div>
<div class="mlist">
<ul><?php if(is_array($list['list'])) foreach($list['list'] as $value) { $highlight = article_title_style($value);?><?php $article_url = fetch_article_url($value);?><li><a href="<?php echo $article_url;?>"><?php echo $value['title'];?></a><span><?php echo $value['dateline'];?></span></li>
<?php } ?>
</ul>
<div class="page"><?php echo $list['multi'];?></div>
</div>
</div>
<div class="mright">
<div class="helpsound"></div>
<div class="lanmu">
<div class="lanmutop"><h1>阿拉帮侬忙</h1></div>
<div class="lanmucen"><?php block_display('6');?></div>
<div class="lanmufoot"></div>
</div>
</div>
<div class="clr"></div>
</div>
<!-- main end --><?php include template('common/footer'); ?>