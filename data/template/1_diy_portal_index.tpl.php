<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); hookscriptoutput('index');
block_get('14,7,9,11,16,13,15,5,12');?><?php include template('common/header'); ?><div class="mfirst">
<div class="mhdp">
        <?php block_display('14');?>    </div>
<div class="mmskd">
<h2><a class="moreLink" href="/portal.php?mod=list&amp;catid=2">&gt;&nbsp;更多</a></h2>
<div class="mscon">
<h3><?php block_display('7');?></h3>
<div class="hot2"></div>
<div class="hotline"></div>
<div class="mlist mllist mlisth"><?php block_display('9');?></div>


</div>
</div>
<div class="clr"></div>
</div>
<div class="msecond">
<div class="secleft">
<h2><a class="moreLink" href="/portal.php?mod=list&amp;catid=3">&gt;&nbsp;更多</a></h2>
<div class="mscons">
<div class="mlist mlisthome"><?php block_display('11');?></div>
 
<div class="viewbg view-idx">
                                <?php block_display('16');?></div>
<div class="clr"></div>
</div>
<div class="asklc">
            <a href="/portal.php?mod=view&amp;aid=12" class="guide guide1">发帖须知</a>
            <a href="/portal.php?mod=list&amp;catid=6" class="guide guide2">常见问题</a>
            <a href="/portal.php?mod=list&amp;catid=7" class="guide guide3">部门职责</a>
            <a href="/forum.php" class="guide guide4">镇街连线</a>
            <a href="/forum.php" class="guide guide5">我要提问</a>
            <!--<img src="images/asklc.jpg" />-->
            
        
        </div>
</div>
<div class="secright">
<div class="bncdsy">
            <a class="hotlink first" href="javascript:void(0);">&nbsp;</a>
            <a class="hotlink" href="/portal.php?mod=view&amp;aid=9">热线呼叫</a>
            <a class="hotlink" href="/forum.php?gid=1">部门连线</a>
            <a class="hotlink" href="/forum.php?gid=72">镇街连线</a>
            <a class="hotlink" href="/portal.php?mod=list&amp;catid=8">政务微博</a>
            <a class="hotlink" href="/portal.php?mod=view&amp;aid=10">微信留言</a>
            <a class="hotlink" href="/portal.php?mod=view&amp;aid=11">微博留言</a>
        </div>
</div>
<div class="clr"></div>
</div>
<div class="bluebg"></div>
<div class="third">
<div class="zxask">
<a href="/forum.php" class="a1">&nbsp;</a>
<a href="/portal.php?mod=view&amp;aid=15" class="a2">&nbsp;</a>
<a href="/forum.php" class="a3">&nbsp;</a>
</div>

<div class="secleft">
<div class="asktop" id="zxwd">
<ul>
<li class="askcur" lang="1">网友正在问</li>
                <li lang="2">部门正在答</li>
</ul>
<div class="clr"></div>
<div class="rightlc"><img src="images/rightlc.jpg" /></div>
</div>
<div class="askul">
<div class="showno zxwd_1"><?php block_display('13');?></div>
<div class="showno zxwd_2"><?php block_display('15');?></div>
<div class="clr"></div>
<div class="person">
<p class="per1">
                    <?php if($_G['uid']) { ?>
                        <a href="/home.php?mod=spacecp&amp;ac=profile">个人中心</a>
                    <?php } else { ?>
                        <a href="/home.php?mod=space&amp;uid=1">个人中心</a>
                    <?php } ?></p>
<p class="per2"><a href="/portal.php?mod=view&amp;aid=12">发帖须知</a></p>
<p class="per3">e热线：63013581</p>
<div class="psoso">
                    <form action="/search.php" name="formsearch" target="_blank" onsubmit="if(kw.value==''){alert('请输入搜索的关键词');return false;}">
                    <input type="hidden" name="mod" value="forum">
                    <input type="hidden" name="searchid" value="1">
                    <input type="hidden" name="searchsubmit" value="yes">
                    <input class="pkey" type="text" name="kw" />
                    <input class="psub" type="submit" value="" />
                    </form>
                </div>
</div>
</div>
</div>
<div class="secright w240">
<div class="wxbgtop"><a href="#" class="moreLink">&nbsp;更多</a></div>
<div class="wxbgcen">
<div class="bgtj">
<p>总发帖：34256   用户数：343245</p>
<p>在受理：43245  总回复：43232</p>
</div>
<div class="fttj"><img src="images/lspic1.jpg" /></div>
</div>
</div>
<div class="clr"></div>
</div>
<div class="msad"><a href="/weibo.html"><img src="images/lspic2.jpg" /></a></div>
<div class="bluebg"></div>
<div class="four">
<div class="zxask wxsytop">
<!--<a href="#" class="a3 moreLink">&gt;&nbsp;更多</a>-->
</div>
<div class="wxsycen">
<div class="asktop asktop2">
<ul>
<li class="askcur" lang="1">部 门</li>
<li lang="2">镇 街</li>
<li lang="3">政务微博</li>
</ul>
<div class="clr"></div>
</div>
<div class="bmlogos ask_1"><?php block_display('5');?></div>
<div class="bmlogos ask_2"><?php block_display('12');?></div>
<div class="bmlogos ask_3">
建设中.....
</div>
</div>
</div>
<!-- {block/zxbm} --><?php include template('common/footer'); ?>