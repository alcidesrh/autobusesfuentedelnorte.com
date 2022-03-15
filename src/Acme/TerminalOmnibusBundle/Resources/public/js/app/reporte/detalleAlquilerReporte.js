detalleAlquilerReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("detalleAlquilerReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("detalleAlquilerReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleAlquiler_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(120, 'd')
        });
        
        if(!core.isMovil()){
            $("#detalleAlquiler_estacion").select2({
                allowClear: $("#detalleAlquiler_estacion option[value='']").length === 1
            });
            $("#detalleAlquiler_estacion").select2("readonly", ($("#detalleAlquiler_estacion").attr("readonly") === "readonly"));    
        }
    }
};
