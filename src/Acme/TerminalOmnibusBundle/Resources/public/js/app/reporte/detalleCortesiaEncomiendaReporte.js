detalleCortesiaEncomiendaReporte = {
    
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
        
        $('#detalleCortesiaEncomienda_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
        });
        
        if(!core.isMovil()){
            $("#detalleCortesiaEncomienda_estacion").select2({
                allowClear: $("#detalleCortesiaEncomienda_estacion option[value='']").length === 1
            });
            $("#detalleCortesiaEncomienda_estacion").select2("readonly", ($("#detalleCortesiaEncomienda_estacion").attr("readonly") === "readonly"));

            $("#detalleCortesiaEncomienda_empresa").select2({
                allowClear: $("#detalleCortesiaEncomienda_empresa option[value='']").length === 1
            });
            $("#detalleCortesiaEncomienda_empresa").select2("readonly", ($("#detalleCortesiaEncomienda_empresa").attr("readonly") === "readonly"));
        }
    }
};
