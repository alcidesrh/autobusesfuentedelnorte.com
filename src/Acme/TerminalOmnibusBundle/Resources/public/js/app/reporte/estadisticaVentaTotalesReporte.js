estadisticaVentaTotalesReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#estadisticaVentaTotales_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: new Date(),
            dateLimit : moment.duration(2, 'months')
        });
        $('#estadisticaVentaTotales_rangoFecha').data('daterangepicker').clickApply();
        
        if(!core.isMovil()){
            $("#estadisticaVentaTotales_estacion").select2({
                allowClear: $("#estadisticaVentaTotales_estacion option[value='']").length === 1
            });
            $("#estadisticaVentaTotales_estacion").select2("readonly", ($("#estadisticaVentaTotales_estacion").attr("readonly") === "readonly"));

            $("#estadisticaVentaTotales_empresa").select2({
                allowClear: $("#estadisticaVentaTotales_empresa option[value='']").length === 1
            });
            $("#estadisticaVentaTotales_empresa").select2("readonly", ($("#estadisticaVentaTotales_empresa").attr("readonly") === "readonly"));
        }
    }
};
