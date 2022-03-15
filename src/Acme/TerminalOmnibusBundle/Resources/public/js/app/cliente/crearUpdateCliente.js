crearUpdateCliente = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $('#cliente_command_fechaNacimiento').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-100y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        
        $('#cliente_command_fechaVencimientoDocumento').datepicker({
            format: "dd/mm/yyyy",
            startDate: "d",
            endDate: "+20y",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        
        if(!core.isMovil()){
            
            $("#cliente_command_tipoDocumento").select2({
                allowClear: true
            });
            
            $("#cliente_command_nacionalidad").select2({
                allowClear: true
            });
            
            $("#cliente_command_sexo").select2({
                allowClear: true
            });
            
            var valuekey1 = sessionStorage.getItem("temp_value_key1");
            if(valuekey1 && $.trim(valuekey1) !== ""){
                sessionStorage.setItem("temp_value_key1", "");
                $("#cliente_command_nombre").val(valuekey1);
            }
            var valuekey2 = sessionStorage.getItem("temp_value_key2");
            if(valuekey2 && $.trim(valuekey2) !== ""){
                sessionStorage.setItem("temp_value_key2", "");
                $("#cliente_command_nit").val(valuekey2);
            }
        }
        
        crearUpdateCliente.checkDetallado();
     },
     
    _conectEvents : function() {
        $('#cliente_command_detallado').bind("click", crearUpdateCliente.checkDetallado);
//        $('#cliente_command_empresa').bind("click", crearUpdateCliente.checkEmpresa);
        $(".validateKeyUp").keyup(crearUpdateCliente.validateInputKeyUp);
    },
    
//    checkEmpresa : function() {
//        var checked = $("#cliente_command_empresa").prop("checked");
//        if(checked){
//            $(".personaDIV").hide();
//        }else{
//            $(".personaDIV").show();
//        }
//        $("#cliente_command_detallado").prop("checked", false);
//        crearUpdateCliente.checkDetallado();
//    },
    
    checkDetallado : function() {
        var checked = $("#cliente_command_detallado").prop("checked");
        if(checked){
            $(".compactoDIV").hide();
            $(".detalladoDIV").show();
        }else{
            $(".detalladoDIV").hide();
            $(".compactoDIV").show();
        }
    },
    
    validateInputKeyUp : function(e){
        console.log("validateInputKeyUp-init");
        var element = $(this);
        if(element.hasClass("forceUpper")){
            if(element.val()){
                element.val(element.val().toUpperCase());
            }
        }
        e.preventDefault();
        var form = $("div[id*='dialog_internal']").find("form");
        if(form.length === 0){
            throw new Error("No se encontro el formulario en el dialogo.");
        }else{
            
            core.customValidateForm(form, element);
        }
    }
};
