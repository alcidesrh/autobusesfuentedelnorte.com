updateuser = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#update_user_user").select2({
                allowClear: $("#update_user_user option[value='']").length === 1
            });
            $("#update_user_user").select2("readonly", ($("#update_user_user").attr("readonly") === "readonly"));
            
            $("#update_user_estacion").select2({
                allowClear: $("#update_user_estacion option[value='']").length === 1
            });
            $("#update_user_estacion").select2("readonly", ($("#update_user_estacion").attr("readonly") === "readonly"));
        }
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
        $("#aceptar").click(updateuser.clickAceptar);
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var form = $("form.form");
        if(core.customValidateForm(form) === true){
            $(form).ajaxSubmit({
                target: form.attr('action'),
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
                            core.getPageForMenu($("#cancelar").attr("href"));
                        });
                    }
                }
           });
        }
    },
    
};
