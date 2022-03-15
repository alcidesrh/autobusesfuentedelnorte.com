registrarEncomiendaEspecial = {
    
    funcionesAddOnload : function() {
//        console.debug("entregarEncomienda.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("entregarEncomienda.funcionesAddOnload-end");
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
        $("#aceptar").click(registrarEncomiendaEspecial.clickAceptar);
    },
   
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var encomiendaForm = $("#encomiendaForm");
        if(core.customValidateForm(encomiendaForm) === true){
            $(encomiendaForm).ajaxSubmit({
                target: encomiendaForm.attr('action'),
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
                            core.getPageForMenu(encomiendaForm.attr('action'));
                        });
                    }
                }
            });
                
       
        }
    }
};
