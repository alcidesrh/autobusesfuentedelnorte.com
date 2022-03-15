empresa = {
    
     funcionesAddOnload : function() {
        console.debug("empresa.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("empresa.funcionesAddOnload-end");
    },
			
    _init : function() {
        $("input.colpick").colpick({
            layout:'hex',           
            colorScheme:'dark',
            onChange:function(hsb,hex,rgb,fromSetColor) {
                if(!fromSetColor){
                    $("input.colpick").val(hex);
                    $("input.colpick").css("border-right-color", "#"+hex);
                    $("input.colpick").css("border-right-style", "solid");
                    $("input.colpick").css("border-right-width", "20px");
                }
            }
        }).keyup(function(){
            $(this).colpickSetColor(this.value);
        });
        
        if($("input.colpick").val()){
            var value = $("input.colpick").val();
            $("input.colpick").css("border-right-color", "#"+value);
            $("input.colpick").css("border-right-style", "solid");
            $("input.colpick").css("border-right-width", "20px");
        }
        
     },
    
    _conectEvents : function() {
        
          
    },
    
    
}