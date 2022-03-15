crearPiloto = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {

        $("#crear_piloto_command_empresa").select2({
            allowClear: true
        });
        $("#crear_piloto_command_nacionalidad").select2({
            allowClear: false
        });
        $("#crear_piloto_command_sexo").select2({
            allowClear: false
        });
        $('#crear_piloto_command_fechaNacimiento').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-100y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        
        $('#crear_piloto_command_fechaVencimientoLicencia').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-20y",
            endDate: "+20y",
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
        
        $("#aceptar").click(crearPiloto.clickAceptar);
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var pilotoForm = $("#pilotoForm");
        if(core.customValidateForm(pilotoForm) === true){
            $(pilotoForm).ajaxSubmit({
                target: pilotoForm.attr('action'),
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
