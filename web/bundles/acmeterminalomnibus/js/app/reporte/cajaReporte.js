cajaReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("cajaReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("cajaReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#caja_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });        
        $('#caja_fecha').datepicker("setDate", new Date());
        
        if(!core.isMovil()){
            $("#caja_moneda").select2({
                allowClear: $("#caja_moneda option[value='']").length === 1
            });
            $("#caja_estacion").select2({
                allowClear: $("#caja_estacion option[value='']").length === 1
            });
            $("#caja_estacion").select2("readonly", ($("#caja_estacion").attr("readonly") === "readonly"));

            $("#caja_empresa").select2({
                allowClear: $("#caja_empresa option[value='']").length === 1
            });
            $("#caja_empresa").select2("readonly", ($("#caja_empresa").attr("readonly") === "readonly"));

            $("#caja_usuario").select2({
                allowClear: $("#caja_usuario option[value='']").length === 1
            });
            $("#caja_usuario").select2("readonly", ($("#caja_usuario").attr("readonly") === "readonly"));            
        }

    }
};
