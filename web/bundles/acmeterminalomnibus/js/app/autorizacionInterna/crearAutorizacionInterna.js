crearAutorizacionInterna = {
    
    funcionesAddOnload : function() {
//        console.debug("crearAutorizacionInterna.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearAutorizacionInterna.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        var href = $("#pathGeneratePin").val();
        var html = $('<a class="btn changePin" href="'+ href +'"><i class="icon-refresh"></i>Cambiar pin</div></a>');
        $("#crear_autorizacion_interna_command_codigo").parent().append(html);
        
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
        
        $(".changePin").bind("click", crearAutorizacionInterna.changePin); 
        $("#aceptar").click(crearAutorizacionInterna.clickAceptar); 
    },
    
    changePin : function(event) {
//        console.debug("changePin-init");
        $.get($(this).attr("href") , function( data ) {
            $("#crear_autorizacion_interna_command_codigo" ).val(data);
        });
        event.preventDefault();
//        console.debug("changePin-end");
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
                            core.getPageForMenu($("#pathHomeAutorizacionInterna").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
