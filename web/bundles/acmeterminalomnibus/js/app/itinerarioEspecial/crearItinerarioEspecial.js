crearItinerarioEspecial = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $('#datetimepicker_crear_itinerario_especial_command_fecha').datetimepicker({
            format: "dd-mm-yyyy hh:ii",
            startDate: core.addDiasAFecha(-1),
            endDate: core.addDiasAFecha(15),
            minuteStep: 5,
            autoclose: true,
            todayBtn: true,
            language: "es"
        });
        
        $("#crear_itinerario_especial_command_ruta").select2({
            allowClear: true
        });
        $("#crear_itinerario_especial_command_tipoBus").select2({
            allowClear: true
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
        $("#aceptar").click(crearItinerarioEspecial.clickAceptar);
        
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var itinerarioEspecialForm = $("#itinerarioEspecialForm");
        if(core.customValidateForm(itinerarioEspecialForm) === true){
            $(itinerarioEspecialForm).ajaxSubmit({
                target: itinerarioEspecialForm.attr('action'),
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
                    core.hideLoading({showLoading:true});
                    if(!core.procesarRespuestaServidor(responseText)){
                        alert("Operación realizada satisfactoriamente.", function() {
                            core.getPageForMenu($("#pathHomeItinerarioEspecial").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
