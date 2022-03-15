cuadreEncomiendaReporte = {
    
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
        
        $('#cuadreEncomienda_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });   
        $('#cuadreEncomienda_fecha').datepicker("setDate", new Date());

        if(!core.isMovil()){
            $("#cuadreEncomienda_estacion").select2({
                allowClear: $("#cuadreEncomienda_estacion option[value='']").length === 1
            });
            $("#cuadreEncomienda_estacion").select2("readonly", ($("#cuadreEncomienda_estacion").attr("readonly") === "readonly"));

            $("#cuadreEncomienda_empresa").select2({
                allowClear: $("#cuadreEncomienda_empresa option[value='']").length === 1
            });
            $("#cuadreEncomienda_empresa").select2("readonly", ($("#cuadreEncomienda_empresa").attr("readonly") === "readonly"));
        }
    }
};
