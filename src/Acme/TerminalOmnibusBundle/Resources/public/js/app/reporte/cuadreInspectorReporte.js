cuadreInspectorReporte = {
    
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
        
        $('#cuadreInspector_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });   
        $('#cuadreInspector_fecha').datepicker("setDate", new Date());
        
        if(!core.isMovil()){
            $("#cuadreInspector_usuario").select2({
                allowClear: $("#cuadreInspector_usuario option[value='']").length === 1
            });
            $("#cuadreInspector_usuario").select2("readonly", ($("#cuadreInspector_usuario").attr("readonly") === "readonly"));
        }
    }
};
