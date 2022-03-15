actualizarAlquiler = {
    
    funcionesAddOnload : function() {
//        console.debug("actualizarAlquiler.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("actualizarAlquiler.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $('#actualizar_alquiler_command_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        $("#actualizar_alquiler_command_empresa").select2({
            allowClear: $("#actualizar_alquiler_command_empresa option[value='']").length === 1
        });
        $("#actualizar_alquiler_command_empresa").select2("readonly", ($("#actualizar_alquiler_command_empresa").attr("readonly") === "readonly"));
        
        $("#actualizar_alquiler_command_piloto").select2({
            allowClear: $("#actualizar_alquiler_command_piloto option[value='']").length === 1
        });
        $("#actualizar_alquiler_command_piloto").select2("readonly", ($("#actualizar_alquiler_command_piloto").attr("readonly") === "readonly"));
        
        $("#actualizar_alquiler_command_pilotoAux").select2({
            allowClear: $("#actualizar_alquiler_command_pilotoAux option[value='']").length === 1
        });
        $("#actualizar_alquiler_command_pilotoAux").select2("readonly", ($("#actualizar_alquiler_command_pilotoAux").attr("readonly") === "readonly"));
        
        $("#actualizar_alquiler_command_bus").select2({
            allowClear: $("#actualizar_alquiler_command_bus option[value='']").length === 1
        });
        $("#actualizar_alquiler_command_bus").select2("readonly", ($("#actualizar_alquiler_command_bus").attr("readonly") === "readonly"));
        
     },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
//            console.debug("clic");
//            console.debug($(this));
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
//                console.debug(confirmed);
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
         
        $("#aceptar").click(actualizarAlquiler.clickAceptar); 
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var alquilerForm = $("#alquilerForm");
        if(core.customValidateForm(alquilerForm) === true){
            $(alquilerForm).ajaxSubmit({
                target: alquilerForm.attr('action'),
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
                            core.getPageForMenu($("#pathHomeAlquiler").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
