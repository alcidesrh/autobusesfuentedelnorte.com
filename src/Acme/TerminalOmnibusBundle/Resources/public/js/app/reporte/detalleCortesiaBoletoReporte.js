detalleCortesiaBoletoReporte = {
    
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
        
        $('#detalleCortesiaBoleto_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
        });

        if(!core.isMovil()){
            $("#detalleCortesiaBoleto_estacion").select2({
                allowClear: $("#detalleCortesiaBoleto_estacion option[value='']").length === 1
            });
            $("#detalleCortesiaBoleto_estacion").select2("readonly", ($("#detalleCortesiaBoleto_estacion").attr("readonly") === "readonly"));

            $("#detalleCortesiaBoleto_empresa").select2({
                allowClear: $("#detalleCortesiaBoleto_empresa option[value='']").length === 1
            });
            $("#detalleCortesiaBoleto_empresa").select2("readonly", ($("#detalleCortesiaBoleto_empresa").attr("readonly") === "readonly"));
        }   
    }
};
