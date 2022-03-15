detalleDepositoAgenciaReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleDepositoAgencia_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
        });    
        
        if(!core.isMovil()){
            $("#detalleDepositoAgencia_estacion").select2({
                allowClear: $("#detalleDepositoAgencia_estacion option[value='']").length === 1
            });
            $("#detalleDepositoAgencia_estacion").select2("readonly", ($("#detalleDepositoAgencia_estacion").attr("readonly") === "readonly"));
        }
        
    }
};
