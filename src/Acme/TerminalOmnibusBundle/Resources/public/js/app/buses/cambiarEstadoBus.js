cambiarEstadoBus = {
    
    funcionesAddOnload : function() {
//        console.debug("cambiarEstadoBus.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("cambiarEstadoBus.funcionesAddOnload-end");
    },
			
    _init : function() {

        $("#cambiar_estado_bus_command_estado").select2({
            allowClear: true
        });
     },
     
    _conectEvents : function() {

    }
};
