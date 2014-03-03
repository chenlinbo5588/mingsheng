<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); 
0
|| checktplrefresh('./template/default/portal/portal_attachment.htm', './template/default/common/header_ajax.htm', 1393856046, '1', './data/template/1_1_portal_portal_attachment.tpl.php', './template/default', 'portal/portal_attachment')
|| checktplrefresh('./template/default/portal/portal_attachment.htm', './template/default/common/footer_ajax.htm', 1393856046, '1', './data/template/1_1_portal_portal_attachment.tpl.php', './template/default', 'portal/portal_attachment')
;?>
<?php ob_end_clean();
ob_start();
@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");
@header("Content-type: text/xml; charset=".CHARSET);
echo '<?xml version="1.0" encoding="'.CHARSET.'"?>'."\r\n";?><root><![CDATA[<?php if($_GET['type'] == 'attach') { ?>
<table cellpadding="0" cellspacing="0" id="attach_list_<?php echo $attach['attachid'];?>" summary="post_attachbody" border="0" width="100%">
<tbody>
<tr>
<td class="attswf">
<p id="attach<?php echo $attach['attachid'];?>">
<span><?php echo $attach['filetype'];?> <a href="javascript:;" class="xi2" id="attachname<?php echo $attach['attachid'];?>" isimage="<?php if($attach['isimage']) { ?>1<?php } else { ?>0<?php } ?>" onclick="<?php if($attach['isimage']) { ?>insertImage('<?php echo $bigimg;?>')<?php } else { ?>insertFile('<?php echo $attach['filename'];?>', 'portal.php?mod=attachment&id=<?php echo $attach['attachid'];?>')<?php } ?>;doane(event);"><?php echo $attach['filename'];?></a></span>
</p>
</td>
<td class="atds"><?php echo $attach['filesize'];?></td>
<td class="attc"><a href="javascript:;" class="d" onclick="deleteAttach(<?php echo $attach['attachid'];?>, 'portal.php?mod=attachment&id=<?php echo $attach['attachid'];?>&op=delete');;return false;" title="删除">删除</a></td>
</tr>
</tbody>
</table>
<?php } else { ?>
<a href="javascript:;" class="opattach">
<span class="opattach_ctrl">
<span onclick="insertImage('<?php echo $bigimg;?>');" class="cur1">插入大图</span><span class="pipe">|</span>
<span onclick="insertImage('<?php echo $smallimg;?>', '<?php echo $bigimg;?>');" class="cur1">小图</span>
</span>
<img src="<?php if($smallimg) { ?><?php echo $smallimg;?><?php } else { ?><?php echo $bigimg;?><?php } ?>" onclick="insertImage('<?php echo $bigimg;?>');" class="cur1" />
</a>
<label for="setconver<?php echo $attach['attachid'];?>" class="cur1 xi2">
<input type="radio" name="setconver" id="setconver<?php echo $attach['attachid'];?>" class="pr" value="1" onclick="setConver(this.getAttribute('coverstr'))" coverstr='<?php echo $coverstr;?>'>设为封面
</label>
<span class="pipe">|</span>
<span class="cur1 xi2" onclick="deleteAttach('<?php echo $attach['attachid'];?>', 'portal.php?mod=attachment&id=<?php echo $attach['attachid'];?>&op=delete');">删除</span>

<?php } ?>
<script type="text/javascript">
parent.$('attach_ids').value += ',' + '<?php echo $attach['attachid'];?>';
<?php if($coverstr) { ?>
if(parent.$('conver').value == '') {
parent.$('conver').value = '<?php echo $coverstr;?>';
}
<?php } ?>
</script><?php echo output_ajax(); ?>]]></root><?php exit;?>