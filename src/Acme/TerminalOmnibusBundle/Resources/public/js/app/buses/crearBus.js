crearBus = {
    
    funcionesAddOnload : function() {
//      console.debug("crearBus.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearBus.funcionesAddOnload-end");
    },
			
    _init : function() {

        $("#crear_bus_command_empresa").select2({
            allowClear: true
        });
        $("#crear_bus_command_tipo").select2({
            allowClear: true
        });
        $("#crear_bus_command_marca").select2({
            allowClear: true
        });
        $("#crear_bus_command_estado").select2({
            allowClear: true
        });       
        $('#crear_bus_command_fechaVencimientoTarjetaOperaciones').datepicker({
            format: "dd/mm/yyyy",
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
        
        $("#aceptar").click(crearBus.clickAceptar);
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var busForm = $("#busForm");
        if(core.customValidateForm(busForm) === true){
            $(busForm).ajaxSubmit({
                target: busForm.attr('action'),
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
                            core.getPageForMenu($("#cancelar").attr("href"));
                        });
                    }
                }
           });
        }
    }
};
