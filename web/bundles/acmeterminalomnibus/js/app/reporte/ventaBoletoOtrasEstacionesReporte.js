ventaBoletoOtrasEstacionesReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("ventaBoletoOtrasEstacionesReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("ventaBoletoOtrasEstacionesReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#COD005_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        if(!core.isMovil()){
            $("#COD005_moneda").select2({
                allowClear: $("#COD005_moneda option[value='']").length === 1
            });

            $("#COD005_estacion").select2({
                allowClear: $("#COD005_estacion option[value='']").length === 1
            });
            $("#COD005_estacion").select2("readonly", ($("#COD005_estacion").attr("readonly") === "readonly"));

            $("#COD005_empresa").select2({
                allowClear: $("#COD005_empresa option[value='']").length === 1
            });
            $("#COD005_empresa").select2("readonly", ($("#COD005_empresa").attr("readonly") === "readonly"));

            $("#COD005_usuario").select2({
                allowClear: $("#COD005_usuario option[value='']").length === 1
            });
            $("#COD005_usuario").select2("readonly", ($("#COD005_usuario").attr("readonly") === "readonly"));            
        }

    }
};
