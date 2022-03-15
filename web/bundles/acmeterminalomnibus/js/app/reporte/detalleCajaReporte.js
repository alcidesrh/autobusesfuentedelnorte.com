detalleCajaReporte = {
    
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
        
        $('#detalleCaja_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });   
        $('#detalleCaja_fecha').datepicker("setDate", new Date());
        
        if(!core.isMovil()){
            $("#detalleCaja_moneda").select2({
                allowClear: $("#detalleCaja_moneda option[value='']").length === 1
            });
            $("#detalleCaja_estacion").select2({
                allowClear: $("#detalleCaja_estacion option[value='']").length === 1
            });
            $("#detalleCaja_estacion").select2("readonly", ($("#detalleCaja_estacion").attr("readonly") === "readonly"));

            $("#detalleCaja_empresa").select2({
                allowClear: $("#detalleCaja_empresa option[value='']").length === 1
            });
            $("#detalleCaja_empresa").select2("readonly", ($("#detalleCaja_empresa").attr("readonly") === "readonly"));

            $("#detalleCaja_usuario").select2({
                allowClear: $("#detalleCaja_usuario option[value='']").length === 1
            });
            $("#detalleCaja_usuario").select2("readonly", ($("#detalleCaja_usuario").attr("readonly") === "readonly"));    
        }
        
    }
};
