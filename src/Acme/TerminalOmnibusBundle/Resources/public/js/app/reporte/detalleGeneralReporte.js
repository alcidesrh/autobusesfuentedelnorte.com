detalleGeneralReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("detalleCajaReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("detalleCajaReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleGeneralEncomienda_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: new Date(),
            endDate: new Date(),
            dateLimit : moment.duration(1, 'years')
        });
        $('#detalleGeneralEncomienda_rangoFecha').data('daterangepicker').clickApply();
        
        if(!core.isMovil()){
            $("#detalleGeneralEncomienda_empresa").select2({
                allowClear: $("#detalleGeneralEncomienda_empresa option[value='']").length === 1
            });
            $("#detalleGeneralEncomienda_empresa").select2("readonly", ($("#detalleGeneralEncomienda_empresa").attr("readonly") === "readonly"));

            $("#detalleGeneralEncomienda_estacionOrigen").select2({
                allowClear: $("#detalleGeneralEncomienda_estacionOrigen option[value='']").length === 1
            });
            $("#detalleGeneralEncomienda_estacionOrigen").select2("readonly", ($("#detalleGeneralEncomienda_estacionOrigen").attr("readonly") === "readonly"));

            $("#detalleGeneralEncomienda_estacionDestino").select2({
                allowClear: $("#detalleGeneralEncomienda_estacionDestino option[value='']").length === 1
            });
            $("#detalleGeneralEncomienda_estacionDestino").select2("readonly", ($("#detalleGeneralEncomienda_estacionDestino").attr("readonly") === "readonly"));   
        }
    }
};
