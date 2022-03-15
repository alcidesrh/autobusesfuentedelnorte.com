detalleTarjetasReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("cuadreVentaBoletoReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("cuadreVentaBoletoReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#detalleTarjetas_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });   
        $('#detalleTarjetas_fecha').datepicker("setDate", new Date());
        
    }
};
