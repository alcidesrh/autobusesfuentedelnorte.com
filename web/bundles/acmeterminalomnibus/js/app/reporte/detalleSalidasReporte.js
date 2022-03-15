detalleSalidasReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        var diaActual =  new Date();
        $('#detalleSalidas_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: diaActual,
            endDate: diaActual,
            dateLimit : moment.duration(2, 'years')
        });
        $('#detalleSalidas_rangoFecha').data('daterangepicker').clickApply();

        if(!core.isMovil()){
            $("#detalleSalidas_empresa").select2({
                allowClear: $("#detalleSalidas_empresa option[value='']").length === 1
            });
            $("#detalleSalidas_empresa").select2("readonly", ($("#detalleSalidas_empresa").attr("readonly") === "readonly"));
        }   
    }
};
