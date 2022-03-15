tarifasBoletosReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("boletoAnuladoUsuarioReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("boletoAnuladoUsuarioReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#tarifasBoletos_estacionOrigen").select2({
                allowClear: $("#tarifasBoletos_estacionOrigen option[value='']").length === 1
            });
            $("#tarifasBoletos_estacionOrigen").select2("readonly", ($("#tarifasBoletos_estacionOrigen").attr("readonly") === "readonly"));  
            
            $("#tarifasBoletos_claseBus").select2({
                allowClear: $("#tarifasBoletos_claseBus option[value='']").length === 1
            });
            $("#tarifasBoletos_claseBus").select2("readonly", ($("#tarifasBoletos_claseBus").attr("readonly") === "readonly")); 
        }
     },
     
    _conectEvents : function() {
        

    }
};
