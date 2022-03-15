detalleFacturaReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("detalleCajaReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("detalleCajaReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleFactura_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
        });
        
        if(!core.isMovil()){
            $("#detalleFactura_estacion").select2({
                allowClear: $("#detalleFactura_estacion option[value='']").length === 1
            });
            $("#detalleFactura_estacion").select2("readonly", ($("#detalleFactura_estacion").attr("readonly") === "readonly"));

            $("#detalleFactura_empresa").select2({
                allowClear: $("#detalleFactura_empresa option[value='']").length === 1
            });
            $("#detalleFactura_empresa").select2("readonly", ($("#detalleFactura_empresa").attr("readonly") === "readonly"));
        }
    }
};
