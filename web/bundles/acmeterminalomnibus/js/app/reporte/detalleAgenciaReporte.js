detalleAgenciaReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleAgencia_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: new Date(),
            dateLimit : moment.duration(2, 'years')
        });
        $('#detalleAgencia_rangoFecha').data('daterangepicker').clickApply();   
        
        if(!core.isMovil()){
            $("#detalleAgencia_estacion").select2({
                allowClear: $("#detalleAgencia_estacion option[value='']").length === 1
            });
            $("#detalleAgencia_estacion").select2("readonly", ($("#detalleAgencia_estacion").attr("readonly") === "readonly"));

            $("#detalleAgencia_empresa").select2({
                allowClear: $("#detalleAgencia_empresa option[value='']").length === 1
            });
            $("#detalleAgencia_empresa").select2("readonly", ($("#detalleAgencia_empresa").attr("readonly") === "readonly"));    
        }
    }
};
