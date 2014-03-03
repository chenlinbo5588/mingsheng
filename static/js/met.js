jq(function(){
	jq('.asktop2 li').click(function(){
		jq('.asktop2 li').removeClass('askcur');
		jq(this).addClass('askcur');
		var id=jq(this).attr('lang');
		jq('.bmlogos').hide();
		jq('.ask_'+id).show();
	});
});
jq(function(){
	jq('#zxwd li').click(function(){
		jq('#zxwd li').removeClass('askcur');
		jq(this).addClass('askcur');
		var id=jq(this).attr('lang');
		jq('.showno').hide();
		jq('.zxwd_'+id).show();
	});
});