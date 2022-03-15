cuadreVentaBoletoReporte = {
    
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
        
        $('#cuadreVentaBoleto_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });   
        $('#cuadreVentaBoleto_fecha').datepicker("setDate", new Date());
        
        if(!core.isMovil()){
            $("#cuadreVentaBoleto_moneda").select2({
                allowClear: $("#cuadreVentaBoleto_moneda option[value='']").length === 1
            });
            $("#cuadreVentaBoleto_moneda").select2("readonly", ($("#cuadreVentaBoleto_moneda").attr("readonly") === "readonly"));

            $("#cuadreVentaBoleto_estacion").select2({
                allowClear: $("#cuadreVentaBoleto_estacion option[value='']").length === 1
            });
            $("#cuadreVentaBoleto_estacion").select2("readonly", ($("#cuadreVentaBoleto_estacion").attr("readonly") === "readonly"));

            $("#cuadreVentaBoleto_empresa").select2({
                allowClear: $("#cuadreVentaBoleto_empresa option[value='']").length === 1
            });
            $("#cuadreVentaBoleto_empresa").select2("readonly", ($("#cuadreVentaBoleto_empresa").attr("readonly") === "readonly"));    
        }
    }
};
