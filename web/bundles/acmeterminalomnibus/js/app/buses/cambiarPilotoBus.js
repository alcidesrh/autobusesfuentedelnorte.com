cambiarPilotoBus = {
    
    funcionesAddOnload : function() {
//        console.debug("cambiarPilotoBus.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("cambiarPilotoBus.funcionesAddOnload-end");
    },
			
    _init : function() {

        $("#cambiar_piloto_bus_command_piloto").select2({
            allowClear: true
        });
        $("#cambiar_piloto_bus_command_pilotoAux").select2({
            allowClear: true
        });
     },
     
    _conectEvents : function() {

    }
};
