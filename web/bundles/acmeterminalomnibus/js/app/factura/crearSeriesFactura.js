crearSeriesFactura = {
    
    funcionesAddOnload : function() {
//        console.debug("crearItinerarioEspecial.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearItinerarioEspecial.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $("#series_factura_command_estacion").select2({
            allowClear: true
        });
        $("#series_factura_command_empresa").select2({
            allowClear: true
        });
        $("#series_factura_command_servicioEstacion").select2({
            allowClear: true
        });
        $("#series_factura_command_impresora").select2({
            allowClear: true
        });
        
        $('#series_factura_command_fechaEmisionResolucionFactura').datepicker({
            format: "dd-mm-yyyy",
            startDate: "-10y",
            endDate: "+10y",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        
         $('#series_factura_command_fechaVencimientoResolucionFactura').datepicker({
            format: "dd-mm-yyyy",
            startDate: "-10y",
            endDate: "+10y",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        
     },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
        
        $("#aceptar").click(crearSeriesFactura.clickAceptar);
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var serieFacturaForm = $("#serieFacturaForm");
        if(core.customValidateForm(serieFacturaForm) === true){
            $(serieFacturaForm).ajaxSubmit({
                target: serieFacturaForm.attr('action'),
                type : "POST",
                dataType: "html",
                cache : false,
                async:false,
                beforeSubmit: function() { 
                    core.showLoading({showLoading:true});
                },
               error: function() {
                    core.hideLoading({showLoading:true});
               },
               success: function(responseText) {
//                    console.log("submitHandler....success");
//                    console.debug(responseText);  
                    core.hideLoading({showLoading:true});
                    if(!core.procesarRespuestaServidor(responseText)){
//                        console.log("procesarRespuestaServidor....okook");
                        alert("Operación realizada satisfactoriamente.", function() {
                            core.getPageForMenu($("#pathHomeSerieFactura").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
