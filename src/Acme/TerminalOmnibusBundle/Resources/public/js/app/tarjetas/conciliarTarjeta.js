conciliarTarjeta = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        
        
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
        
        $("#aceptar").click(conciliarTarjeta.clickAceptar); 
        
        $("a.showSalidaDetails").click(conciliarTarjeta.showSalidaDetails);
        $("a.showTarjetaDetails").click(conciliarTarjeta.showTarjetaDetails);
        $("a.showDetails").click(conciliarTarjeta.showDetails);
        $("a.ponerEnRevision").click(conciliarTarjeta.ponerEnRevision);
        $("a.conciliarSuccess").click(conciliarTarjeta.conciliarSuccess);
        $("a.conciliarDiferencias").click(conciliarTarjeta.conciliarDiferencias);
    },
    
    conciliarDiferencias : function(e) {
        console.log("conciliarDiferencias-init");
        e.preventDefault();
        e.stopPropagation();
        
        var description = $(this).parent().find("textarea.description").val();
        if(description === null || $.trim(description) === ""){
            alert("Debe especificar una observación en la conciliación cuando existen diferencias.");
            return;
        }
        core.request({
            url : $(this).attr("href"),
            type: "POST",
            dataType: "html",
            async: false,
            extraParams : { description : description },
            successCallback: function(responseText){
                if(!core.procesarRespuestaServidor(responseText)){
                    alert("Operación realizada satisfactoriamente.", function() {
                        core.getPageForMenu($("#pathConciliarTarjeta").attr("value"));
                    });
                }
            }
        });
    },
    
    conciliarSuccess : function(e) {
        console.log("conciliarSuccess-init");
        e.preventDefault();
        e.stopPropagation();
        core.request({
            url : $(this).attr("href"),
            type: "POST",
            dataType: "html",
            async: false,
            successCallback: function(responseText){
                if(!core.procesarRespuestaServidor(responseText)){
                    alert("Operación realizada satisfactoriamente.", function() {
                        core.getPageForMenu($("#pathConciliarTarjeta").attr("value"));
                    });
                }
            }
        });
    },
    
    ponerEnRevision : function(e) {
        console.log("ponerEnRevision-init");
        e.preventDefault();
        e.stopPropagation();
        
        core.request({
            url : $(this).attr("href"),
            type: "GET",
            dataType: "html",
            async: false,
            successCallback: function(responseText){
                if(!core.procesarRespuestaServidor(responseText)){
                    alert("Operación realizada satisfactoriamente.", function() {
                        core.getPageForMenu($("#pathConciliarTarjeta").attr("value"));
                    });
                }
            }
        });
    },
    
    showTarjetaDetails : function(e) {
        console.log("showTarjetaDetails-init");
        e.preventDefault();
        e.stopPropagation();
        
        core.request({
            url : $(this).attr("href"),
            type: "GET",
            dataType: "html",
            async: false,
            successCallback: function(responseText){
                if(!core.procesarRespuestaServidor(responseText)){
                    console.debug(responseText);
                    core.showMessageDialog({
                        title: "Consultar Tarjeta",
                        fullscreen: true,
                        compact: true,
                        text: responseText,
                        defaultButtonOFF: true,
                        buttons: {
                            Cancelar: {
                                primary: true,
                                type: "info",
                                click: function() {
                                    $("body").css("overflow-y", "auto");
                                    this.dialog2("close");					
                                }
                            }				    
                        }
                    });
                }
            }
        });
    },
    
    showSalidaDetails : function(e) {
        console.log("showSalidaDetails-init");
        e.preventDefault();
        e.stopPropagation();
        
        core.request({
            url : $(this).attr("href"),
            type: "GET",
            dataType: "html",
            async: false,
            successCallback: function(responseText){
                if(!core.procesarRespuestaServidor(responseText)){
                    console.debug(responseText);
                    core.showMessageDialog({
                        title: "Consultar Salida",
                        fullscreen: true,
                        compact: true,
                        text: responseText,
                        defaultButtonOFF: true,
                        buttons: {
                            Cancelar: {
                                primary: true,
                                type: "info",
                                click: function() {
                                    $("body").css("overflow-y", "auto");
                                    this.dialog2("close");					
                                }
                            }				    
                        }
                    });
                }
            }
        });
    },
    
    showDetails : function(e) {
        console.log("showDetails-init");
        e.preventDefault();
        e.stopPropagation();
        
        core.request({
            url : $(this).attr("href"),
            type: "GET",
            dataType: "html",
            async: false,
            successCallback: function(responseText){
                if(!core.procesarRespuestaServidor(responseText)){
                    console.debug(responseText);
                    core.showMessageDialog({
                        title: "Detalles de Corte de Venta",
                        fullscreen: true,
                        compact: true,
                        text: responseText,
                        defaultButtonOFF: true,
                        buttons: {
                            Cancelar: {
                                primary: true,
                                type: "info",
                                click: function() {
                                    $("body").css("overflow-y", "auto");
                                    this.dialog2("close");					
                                }
                            }				    
                        }
                    });
                }
            }
        });
    },
    
    clickAceptar: function(e) {
        console.log("clickAceptar-init");
        e.preventDefault();
        e.stopPropagation();
        
        var tarjetaForm = $("#tarjetaForm");
        if(core.customValidateForm(tarjetaForm) === true){
            $(tarjetaForm).ajaxSubmit({
                target: tarjetaForm.attr('action'),
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
                            core.getPageForMenu($("#pathHomeTarjeta").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
