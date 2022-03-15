detalleBusesReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        if(!core.isMovil()){
            
            $("#detalleBuses_empresa").select2({
                allowClear: $("#detalleBuses_empresa option[value='']").length === 1
            });
            $("#detalleBuses_empresa").select2("readonly", ($("#detalleBuses_empresa").attr("readonly") === "readonly"));
            
        }
    }
};
