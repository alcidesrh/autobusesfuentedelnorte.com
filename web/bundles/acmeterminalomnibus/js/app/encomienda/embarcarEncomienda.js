embarcarEncomienda = {
    
    funcionesAddOnload : function() {
//        console.debug("embarcarEncomienda.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("embarcarEncomienda.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $("#embarcar_encomienda_command_salida").select2({
            allowClear: true
        });
     },
     
    _conectEvents : function() {
        
       
    }
    
};
