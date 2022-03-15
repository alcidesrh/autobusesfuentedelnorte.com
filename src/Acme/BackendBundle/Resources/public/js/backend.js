backend = {
    
     funcionesAddOnload : function() {
        console.debug("backend.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("backend.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $lista = $(".sonata-ba-list-field-color");
        jQuery.each($lista, function() {
            console.debug($(this));
            var value = $.trim($(this).text());
            $(this).css("background-color", "#"+value);
            $(this).css("color", "#FFFFFF");
        });;
        
     },
    
    _conectEvents : function() {
        
          
    },
    
    
}