boletoAnuladoUsuarioReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("boletoAnuladoUsuarioReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("boletoAnuladoUsuarioReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#COD003_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(120, 'd')
        });
        
        if(!core.isMovil()){
            $("#COD003_moneda").select2({
                allowClear: $("#COD003_moneda option[value='']").length === 1
            });

            $("#COD003_estacion").select2({
                allowClear: $("#COD003_estacion option[value='']").length === 1
            });
            $("#COD003_estacion").select2("readonly", ($("#COD003_estacion").attr("readonly") === "readonly"));

            $("#COD003_empresa").select2({
                allowClear: $("#COD003_empresa option[value='']").length === 1
            });
            $("#COD003_empresa").select2("readonly", ($("#COD003_empresa").attr("readonly") === "readonly"));

            $("#COD003_usuario").select2({
                allowClear: $("#COD003_usuario option[value='']").length === 1
            });
            $("#COD003_usuario").select2("readonly", ($("#COD003_usuario").attr("readonly") === "readonly"));            
        }
            

    }
};
