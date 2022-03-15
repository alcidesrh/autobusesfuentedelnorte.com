registrarDepositoAgencia = {
    
    funcionesAddOnload : function() {
//        console.debug("crearAlquiler.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearAlquiler.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#registrar_deposito_agencia_command_estacion").select2({
                allowClear: $("#registrar_deposito_agencia_command_estacion option[value='']").length === 1
            });
            $("#registrar_deposito_agencia_command_estacion").select2("readonly", ($("#registrar_deposito_agencia_command_estacion").attr("readonly") === "readonly"));
        }
        
        $('#registrar_deposito_agencia_command_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-7d",
            endDate: "+7d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
       
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
         
        $("#aceptar").click(registrarDepositoAgencia.clickAceptar); 
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var depositoAgenciaForm = $("#depositoAgenciaForm");
        if(core.customValidateForm(depositoAgenciaForm) === true){
            $(depositoAgenciaForm).ajaxSubmit({
                target: depositoAgenciaForm.attr('action'),
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
                            core.getPageForMenu($("#pathHomeDepositoAgencia").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
