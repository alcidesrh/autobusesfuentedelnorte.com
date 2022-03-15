ventaBoletoPrepagadoReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("ventaBoletoPrepagadoReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("ventaBoletoPrepagadoReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#COD004_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        if(!core.isMovil()){
            $("#COD004_moneda").select2({
                allowClear: $("#COD004_moneda option[value='']").length === 1
            });

            $("#COD004_estacion").select2({
                allowClear: $("#COD004_estacion option[value='']").length === 1
            });
            $("#COD004_estacion").select2("readonly", ($("#COD004_estacion").attr("readonly") === "readonly"));

            $("#COD004_empresa").select2({
                allowClear: $("#COD004_empresa option[value='']").length === 1
            });
            $("#COD004_empresa").select2("readonly", ($("#COD004_empresa").attr("readonly") === "readonly"));

            $("#COD004_usuario").select2({
                allowClear: $("#COD004_usuario option[value='']").length === 1
            });
            $("#COD004_usuario").select2("readonly", ($("#COD004_usuario").attr("readonly") === "readonly"));            
        }

    }
};
