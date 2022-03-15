autorizacionInterna = {
    
     funcionesAddOnload : function() {
        console.debug("autorizacionInterna.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("autorizacionInterna.funcionesAddOnload-end");
    },
			
    _init : function() {
       
       var href = $(".generatePinHidden").val();
       var html = $('<a class="btn sonata-action-element changePin" href="'+ href +'"><i class="icon-refresh"></i>Cambiar pin</div></a>');
       $("input[id*='codigo']").parent().append(html);
     },
    
    _conectEvents : function() {
        
        $(".changePin").bind("click", this.changePin);
          
    },
    
    changePin : function(event) {
        console.debug("changePin-init");
        $.get($(this).attr("href") , function( data ) {
            $( "input[id*='codigo']" ).val(data);
        });
        event.preventDefault();
        console.debug("changePin-end");
    },
    
}