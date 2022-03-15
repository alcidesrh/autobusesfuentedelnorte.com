encomiendaPropietario = {
    
    funcionesAddOnload : function() {
//        console.debug("cajaReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("cajaReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#encomiendaPropietario_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        if(!core.isMovil()){
            $("#encomiendaPropietario_estacion").select2({
                allowClear: $("#encomiendaPropietario_estacion option[value='']").length === 1
            });
            $("#encomiendaPropietario_estacion").select2("readonly", ($("#encomiendaPropietario_estacion").attr("readonly") === "readonly"));

            $("#encomiendaPropietario_empresa").select2({
                allowClear: $("#encomiendaPropietario_empresa option[value='']").length === 1
            });
            $("#encomiendaPropietario_empresa").select2("readonly", ($("#encomiendaPropietario_empresa").attr("readonly") === "readonly"));    
        }
        
    }
};
