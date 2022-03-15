ventaBoletoUsuarioReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("ventaBoletoUsuarioReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("ventaBoletoUsuarioReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#COD002_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        if(!core.isMovil()){
            $("#COD002_moneda").select2({
                allowClear: $("#COD002_moneda option[value='']").length === 1
            });

            $("#COD002_estacion").select2({
                allowClear: $("#COD002_estacion option[value='']").length === 1
            });
            $("#COD002_estacion").select2("readonly", ($("#COD002_estacion").attr("readonly") === "readonly"));

            $("#COD002_empresa").select2({
                allowClear: $("#COD002_empresa option[value='']").length === 1
            });
            $("#COD002_empresa").select2("readonly", ($("#COD002_empresa").attr("readonly") === "readonly"));

            $("#COD002_usuario").select2({
                allowClear: $("#COD002_usuario option[value='']").length === 1
            });
            $("#COD002_usuario").select2("readonly", ($("#COD002_usuario").attr("readonly") === "readonly"));            
        }

    }
};
