detallePortalReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detallePortal_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: new Date(),
            dateLimit : moment.duration(2, 'years')
        });
        $('#detallePortal_rangoFecha').data('daterangepicker').clickApply();   
        
        if(!core.isMovil()){
            $("#detallePortal_portal").select2({
                allowClear: $("#detallePortal_portal option[value='']").length === 1
            });
            $("#detallePortal_portal").select2("readonly", ($("#detallePortal_portal").attr("readonly") === "readonly"));

            $("#detallePortal_empresa").select2({
                allowClear: $("#detallePortal_empresa option[value='']").length === 1
            });
            $("#detallePortal_empresa").select2("readonly", ($("#detallePortal_empresa").attr("readonly") === "readonly"));    
        }
    }
};
