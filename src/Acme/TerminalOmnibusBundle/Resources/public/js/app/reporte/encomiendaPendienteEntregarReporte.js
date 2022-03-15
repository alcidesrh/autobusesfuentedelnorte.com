encomiendaPendienteEntregarReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("cajaReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("cajaReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        if(!core.isMovil()){
            $("#encomiendaPendienteEntregar_estacion").select2({
                allowClear: $("#encomiendaPendienteEntregar_estacion option[value='']").length === 1
            });
            $("#encomiendaPendienteEntregar_estacion").select2("readonly", ($("#encomiendaPendienteEntregar_estacion").attr("readonly") === "readonly"));

            $("#encomiendaPendienteEntregar_empresa").select2({
                allowClear: $("#encomiendaPendienteEntregar_empresa option[value='']").length === 1
            });
            $("#encomiendaPendienteEntregar_empresa").select2("readonly", ($("#encomiendaPendienteEntregar_empresa").attr("readonly") === "readonly"));
        }
    }
};
