crearAutorizacionInternaMultiple = {
    
    funcionesAddOnload : function() {
//        console.debug("crearAutorizacionCortesiaMultiple.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearAutorizacionCortesiaMultiple.funcionesAddOnload-end");
    },
			
    _init : function() {
        
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
        
        $("#aceptar").click(crearAutorizacionInternaMultiple.clickAceptar); 
        
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var autorizacionInternaForm = $("#autorizacionInternaForm");
        if(core.customValidateForm(autorizacionInternaForm) === true){
            $(autorizacionInternaForm).ajaxSubmit({
                target: autorizacionInternaForm.attr('action'),
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
                            core.getPageForMenu($("#cancelar").attr('href'));
                        });
                    }
                }
           });
        }
    },
    
};
