ventaBoletoPrepagadoPorBusesReporte = {
    
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
        
        $('#ventaBoletoPrepagadoOtrasEstacionesPorBuses_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(180, 'd')
        });
        
        if(!core.isMovil()){
            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_moneda").select2({
                allowClear: $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_moneda option[value='']").length === 1
            });

            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_estacion").select2({
                allowClear: $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_estacion option[value='']").length === 1
            });
            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_estacion").select2("readonly", ($("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_estacion").attr("readonly") === "readonly"));

            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_empresa").select2({
                allowClear: $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_empresa option[value='']").length === 1
            });
            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_empresa").select2("readonly", ($("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_empresa").attr("readonly") === "readonly"));

            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_bus").select2({
                allowClear: $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_bus option[value='']").length === 1
            });
            $("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_bus").select2("readonly", ($("#ventaBoletoPrepagadoOtrasEstacionesPorBuses_bus").attr("readonly") === "readonly"));
        }
 
    }
};
