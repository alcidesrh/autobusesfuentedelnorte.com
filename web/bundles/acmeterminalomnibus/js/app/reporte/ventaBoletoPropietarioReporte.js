ventaBoletoPropietarioReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("ventaBoletoPropietarioReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("ventaBoletoPropietarioReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#COD001_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
//        $("#COD001_moneda").select2({
//            allowClear: $("#COD001_moneda option[value='']").length === 1
//        });
        
        if(!core.isMovil()){
            $("#COD001_estacion").select2({
                allowClear: $("#COD001_estacion option[value='']").length === 1
            });
            $("#COD001_estacion").select2("readonly", ($("#COD001_estacion").attr("readonly") === "readonly"));

            $("#COD001_empresa").select2({
                allowClear: $("#COD001_empresa option[value='']").length === 1
            });
            $("#COD001_empresa").select2("readonly", ($("#COD001_empresa").attr("readonly") === "readonly"));            
        }

    }
};
