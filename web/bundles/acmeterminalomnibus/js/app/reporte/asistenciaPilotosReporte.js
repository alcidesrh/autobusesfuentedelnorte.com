asistenciaPilotosReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#asistenciaPilotos_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        if(!core.isMovil()){
            $("#asistenciaPilotos_empresa").select2({
                allowClear: $("#asistenciaPilotos_empresa option[value='']").length === 1
            });
            $("#asistenciaPilotos_empresa").select2("readonly", ($("#asistenciaPilotos_empresa").attr("readonly") === "readonly"));
        }
        
    }
};
