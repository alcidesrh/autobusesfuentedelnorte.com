detalleAgenciaGraficoReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleAgenciaGrafico_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: new Date(),
            dateLimit : moment.duration(2, 'months')
        });
        $('#detalleAgenciaGrafico_rangoFecha').data('daterangepicker').clickApply();

        if(!core.isMovil()){
            $("#detalleAgenciaGrafico_estacion").select2({
                allowClear: $("#detalleAgenciaGrafico_estacion option[value='']").length === 1
            });
            $("#detalleAgenciaGrafico_estacion").select2("readonly", ($("#detalleAgenciaGrafico_estacion").attr("readonly") === "readonly"));

            $("#detalleAgenciaGrafico_empresa").select2({
                allowClear: $("#detalleAgenciaGrafico_empresa option[value='']").length === 1
            });
            $("#detalleAgenciaGrafico_empresa").select2("readonly", ($("#detalleAgenciaGrafico_empresa").attr("readonly") === "readonly"));    
        }
    }
};
