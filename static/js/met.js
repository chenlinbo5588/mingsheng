jq(function(){
    
    jq('.asktop2 li').bind("mouseenter",function(e){
        jq(this).parents("ul").find("li").removeClass('askcur');
        jq(this).addClass("askcur");
        
        var id=jq(this).attr('lang');
		jq('.bmlogos').hide();
		jq('.ask_'+id).show();
    });
    
    jq('.asktop2 li').bind("mouseleave",function(e){
        
    });
    
    /**
	jq('.asktop2 li').click(function(){
		jq('.asktop2 li').removeClass('askcur');
		jq(this).addClass('askcur');
		
	});
    */
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