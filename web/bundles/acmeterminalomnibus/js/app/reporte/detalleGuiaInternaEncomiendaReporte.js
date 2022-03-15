detalleGuiaInternaEncomiendaReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("detalleCortesiaReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("detalleCortesiaReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleGuiaInternaEncomienda_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
        });
        
        if(!core.isMovil()){
            $("#detalleGuiaInternaEncomienda_estacion").select2({
                allowClear: $("#detalleGuiaInternaEncomienda_estacion option[value='']").length === 1
            });
            $("#detalleGuiaInternaEncomienda_estacion").select2("readonly", ($("#detalleGuiaInternaEncomienda_estacion").attr("readonly") === "readonly"));

            $("#detalleGuiaInternaEncomienda_empresa").select2({
                allowClear: $("#detalleGuiaInternaEncomienda_empresa option[value='']").length === 1
            });
            $("#detalleGuiaInternaEncomienda_empresa").select2("readonly", ($("#detalleGuiaInternaEncomienda_empresa").attr("readonly") === "readonly"));
        }

    }
};
