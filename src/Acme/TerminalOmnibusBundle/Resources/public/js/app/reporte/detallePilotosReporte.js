detallePilotosReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        if(!core.isMovil()){
            
            $("#detallePilotos_empresa").select2({
                allowClear: $("#detallePilotos_empresa option[value='']").length === 1
            });
            $("#detallePilotos_empresa").select2("readonly", ($("#detallePilotos_empresa").attr("readonly") === "readonly"));
            
        }
    }
};
