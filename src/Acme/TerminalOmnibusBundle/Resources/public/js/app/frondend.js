frondend = {
    
    listaImpresorasDisponibles : '',
    
    funcionesAddOnload : function() {
//        console.debug("frondend.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        
        core.htmlBase = $("#content").html();
        
        topMenu.funcionesAddOnload();
        $(document).keyup(frondend.checkKeyUp);
        frondend.checkEndLoading();
        
//	console.debug("frondend.funcionesAddOnload-end");
    },
    
    checkEndLoading : function(){
        console.log("checkEndLoading.init");
        var pja = $("#pja").val();
        if(eval(pja)){
            var qz = document.getElementById('qz');
            if(qz){
                qz.onLoad = function(){
                    console.log("onLoadApplet.end");
                    frondend.endLoad();
                };  
                setTimeout(function(){
                    frondend.endLoad();
                }, 5000);
            }else{
                frondend.endLoad();
            }
        }else{
            frondend.endLoad();
        }
    },
    
    _endLoaded : false,
    endLoad : function(){
        console.log("checkEndLoading.ok");
        if(frondend._endLoaded === false){
            frondend._endLoaded = true;
            var loadingSystemDIV = document.getElementById("loadingSystemDIV");
            if(loadingSystemDIV !== null){
                loadingSystemDIV.remove();
            }
            frondend.loadImpresorasDisponibles();
        }
    },
    
    timeCheckNotificaciones : 300000, //5 minutos
    lastTimeMove : null,		
    _init : function() {
        frondend.checkExpiredPassword(function (){
           frondend.checkNotificaciones();
        });
        frondend.checkNotificaciones();
        setInterval(frondend.checkNotificaciones, frondend.timeCheckNotificaciones);
        
        $("body").click(function() {
            core.reportActivity();
        });
        $("body").mousemove(function(e) {
//            e.preventDefault();
//            e.stopPropagation();
//            console.debug("body-mousemove-init");
            if(frondend.lastTimeMove === null){
//                console.log("xxxx1");
                frondend.lastTimeMove = Date.now();
                core.reportActivity();
            }else{
                var diff = Date.now() - frondend.lastTimeMove;
                if(diff > 1000){ //1 segundo
//                    console.log("xxxx2");
                    frondend.lastTimeMove = Date.now();
                    core.reportActivity();
                }
            }
        });
        if(!core.isMovil()){
            console.log("seting checkInactivity");
            setInterval(core.checkInactivity, core.timeExpired);
        }
        core.checkEstacion();
    },
    
    _conectEvents : function() {
        $("li a.lateralIzq").click(function(e) {
//            console.debug("clic menu lateral izquierdo...");
//            console.debug($(this));
            e.preventDefault();
            e.stopPropagation();
            core.getPageForMenu($(this).attr('href'), true);
        });
        $(".linkHelpTopMenu").click(function(e) {
//            console.debug("perfilUsuario-init");
            e.preventDefault();
            e.stopPropagation();
            core.getPageForMenu($(this).attr('href'), true);
        });
        $(".logout").click(function(e) {
//            console.debug("logout-init");
            e.preventDefault();
            e.stopPropagation();
            core.request({
                url : $(this).attr('href'),
		ajaxRequest:false,
                showLoading: true
            });
        });
        $("#cambiarContrasenaTopMenu").click(frondend.loadSubPage);
        $("#impresoraState").click(frondend.clicImpresoraState);
    },
    
    clicImpresoraState : function(e){
        e.preventDefault();
        e.stopPropagation();
        var lista = frondend.listaImpresorasDisponibles;
        if($.trim(lista) === ""){
            lista = "No se han detectado impresoras.";
        }else{
            lista = lista.replace(/,/g, "<BR>");
        }
        lista += "<BR><BR>";
        var text = '<div class="container-fluid"><div class="row-fluid"><div class="span12">' + lista + 
            '</div></div></div>';
        core.showMessageDialog({
            title : "IMPRESORAS DISPONIBLES",
            text: text,
            buttons: {
                Recargar: function() {
                    frondend.loadImpresorasDisponibles();				
                },
                Cerrar: function() {
                    this.dialog2("close");					
                }
            }
        });
    },
    
    loadImpresorasDisponibles : function(){
        console.log("loadImpresorasDisponibles-init");
        var pja = $("#pja").val();
        if(eval(pja)){
          if(core.checkInstallCustomQZprint() === true && customQZprint.isLoaded()){
              console.log("loadImpresorasDisponibles from QZprint");
              core.showNotification({text : "Iniciando la detección de las impresoras."});
              customQZprint.findPrinters(function(listaImpresoras){
                  frondend.listaImpresorasDisponibles = listaImpresoras;
                  frondend.finishLoadImpresoras();
              });
          }else{
              console.log("No se pudo determinar el componente de impresión.");
              core.showNotification({text : "No se pudo determinar el componente de impresión (Java)."});
          }
        }else{
          if(core.checkInstalljsPrintSetup() === true){
              console.log("loadImpresorasDisponibles from jsPrintSetup");
              core.showNotification({text : "Iniciando la detección de las impresoras."});
              frondend.listaImpresorasDisponibles = jsPrintSetup.getPrintersList();
              frondend.finishLoadImpresoras();
          }else{
              core.showNotification({text : "No se pudo determinar la lista de impresoras."});
              console.log("No se pudo determinar el componente de impresión.");
          }
        }
    },
    
    finishLoadImpresoras : function(){
        $("#impresoraState").attr("src", $("#impresoraOnline").attr("href"));
        core.showNotification({text : "Finalizó la detección de las impresoras."});
    },
    
    checkKeyUp : function (e){
//        console.log("checkKeyUp-init");
//        console.log("Tecla: " + e.which + " : " + String.fromCharCode(e.which));
        if(e.shiftKey && (e.which === 49 || e.which === 50)){
            console.log("procesando-init");
            var text = $(".select2-input.select2-focused").val();
            text = text.replace(/!/g, ""); 
            text = text.replace(/@/g, "");
            text = text.replace(/#/g, "");
            text = text.replace(/$/g, "");
            text = text.replace(/"/g, "");
            $(".select2-input.select2-focused").val(text);
            console.debug(text);
            var items = $("input[id^='s2id']:disabled");
            $.each(items, function(index, item) {
                var item = $(item);
                console.debug(item);
                var input = item.parent().parent().find(".autocheck");
                console.debug(input);
                if(input.length !== 0){
                    input.select2("close");
                    console.log("clicc...");
                    if(e.which === 49){
                        sessionStorage.setItem("temp_value_key1", text);
                    }else if(e.which === 50){
                        sessionStorage.setItem("temp_value_key2", text);
                    }
                    item.parent().parent().find(".btn.add").click();
                    
                }
            });
            return false;
        }
    },

    checkExpiredPassword : function(successCallback) {
        core.request({
            url : $("#pathCheckExpiredPassword").val(),
            method: "GET",
            dataType: "json",
            async: true,
            showLoading: false,
            successCallback: function(data){
               if(data.dias <= 1){
                    alert("Su contraseña expira hoy, debe cambiarla.", function (){
                        $("#cambiarContrasenaTopMenu").click();
                    });
               }
               else if(data.dias < 20){
                    confirm("Su contraseña expira en " + data.dias + " días. Desea cambiarla ahora?", function (confirmed){
                        if(confirmed === true){
                            $("#cambiarContrasenaTopMenu").click();
                        }
                    });
               }
               
               if(successCallback && $.isFunction(successCallback)){
                    successCallback();
               }
            }
        });
    },
    totalNotificaciones : 0,
    notificacionActual : 0,
    notificaciones : [],
    chekingProgress : false,
    checkNotificaciones : function(successCallback) {
        console.log("checkNotificaciones.init");
        if(!core.isMovil()){
            if(frondend.chekingProgress === false){
                frondend.chekingProgress = true;
                core.request({
                    url : $("#pathCheckNotificaciones").val(),
                    method: "GET",
                    dataType: "json",
                    async: true,
                    hiddenError : true,
                    showLoading: false,
                    successCallback: function(data){
                        var items  = data.items;
                        if(items !== undefined && items !== null && items.length !== 0){  
                            frondend.notificacionActual = 0;
                            frondend.totalNotificaciones = items.length;
                            frondend.notificaciones = items;
                            var item = items[0];
                            $(".notificacionDiv").removeClass("hidden");
                            console.log("show item: " + frondend.notificacionActual);
                            var text = "Info " + (frondend.notificacionActual+1) + " de " + frondend.totalNotificaciones + ". " + item.text;
                            $(".notificacionText").html(text);
                            $('.notificacionText').marquee({
                                duration: item.time,
                                pauseOnHover : false
                            })
                            .unbind("finished").bind('finished', function(){
                                //inicio otro hilo
                                $(".notificacionText").html("&nbsp;");
                                setTimeout(function(){
                                    frondend.notificacionActual++;
                                    console.log("show item: " + frondend.notificacionActual);
                                    if(frondend.notificacionActual >= frondend.totalNotificaciones){
                                        frondend.notificacionActual = 0;
                                    }
                                    var item = items[frondend.notificacionActual];
                                    var text = "Info " + (frondend.notificacionActual+1) + " de " + frondend.totalNotificaciones + ". " + item.text;
                                    $(".notificacionText").html(text); 
                                    $('.notificacionText').marquee({
                                        duration: item.time,
                                        pauseOnHover : false
                                    });
                                }, 500);
                            });

                        }else{
                            frondend.notificaciones = [];
                            $(".notificacionText").text("");
                            $(".notificacionDiv").addClass("hidden");
                        }
                        if(successCallback && $.isFunction(successCallback)){
                            successCallback();
                        }
                        frondend.chekingProgress = false;
                    },
                    errorCallback : function(jqXHR, statusText, error){
                        console.log("Ocurrio un error cargando las notificaciones.");
                    }
                });
            }
        }else{
            console.log("checkNotificaciones.No se muestra en celulares");
        }
        
    },
    
    loadPage : function() {
//        console.debug("frondend.loadPage");
        $("#clouseContentModal").click(function(e) {
//            console.debug("clic en clouseContentModal");
//            console.debug($(this));
            e.preventDefault();
            e.stopPropagation();
            core.request({
                url : $(this).attr('href'),
		dataType:"html",
                method: 'GET', //Obligatorio para cache
		async:true
            });
         }); 
    },
    
    linkFilter : function(e, link, grid, form, successCallback) {
//        console.debug("linkFilter - clic");
        e.preventDefault();
        e.stopPropagation();
        if(!link){
            link = $(this);
        }
        if(!grid){
            grid = $('#grid'); //Default
        }
        if(!form){
            form = $('#filtersForm');
        }
        var values = form.formSerialize();
        var itemsCheckbox = form.find('input[type="checkbox"]');
        $.each(itemsCheckbox, function(index, item) {
//            console.debug(index);
//            console.debug(item);
            item = $(item);
            if(item.prop("checked") === false){
               values += "&"+item.prop("name")+"=0";
            }
        });
//        console.debug(values);
//        console.debug("linkFilter - reload - init");
        grid.flexOptions({
            newp: 1, 
            query:values
        }).flexReload();
//        console.debug("linkFilter - reload - end");
        if(successCallback && $.isFunction(successCallback)){
            successCallback();
        }
    },
    
    loadSubPage :function(e, link, successCallback, options) {
            console.log("loadSubPage-init");
            e.preventDefault();
            e.stopPropagation();
            
            if(options === undefined || options === null){
                options = {};
            }
            
            var data = {};
            if(!link){
               link = $(this);
            }
            var index = link.data("index");
//            console.debug("Index:"+index);
            if(index){
//                console.log("Entro al index");
                //Default
                var selected = null;
                if(eval(index) === true || $.trim(index) === ""){
                    selected = core.getSelectedItemId($("#grid"));
                }else if(eval(index) === false){} //Existe index, pero esta dehabilitado
                else{
                    selected = $("#"+index).val();
                }
                if(eval(index) !== false){
                    if(selected === null || $.trim(selected) === ""){
                        var message = link.data("index-message");
                        if(!message || message === null || $.trim(message) === ""){
                            message =  "Debe seleccionar un elemento.";
                        }
                        alert(message);
                        return;
                    }else{
                        data['id'] = selected;
                    }
                }
            }
            
            
            var name = link.data("title");
            if(!name){
                name =  link.text();
            }
            
            var print = link.data("print");
            var email = link.data("email");
            var printesc = link.data("printesc");
            var autoOpenFile = link.data("autoopenfile");
            var showDialog = link.data("dialog");
            if(eval(showDialog) === false){
                console.log("loadSubPage-showDialog-init");
                core.request({
                    url : link.attr('href'),
                    method: 'GET', //Obligatorio
                    extraParams: data,
                    dataType:"html",
                    async:true
                });
            }
            else if(print && $.trim(print) !== ""){
                console.log("loadSubPage-print-init");
                if(print === "method1"){
                    frondend.printFactura(link.attr('href'), { ids : data['id'] });
                }else if(print === "method2"){
                    frondend.printVoucherBoleto(link.attr('href'), { ids : data['id'] });
                }else if(print === "method3"){
                    frondend.printFactura(link.attr('href'), { ids : data['id'] });
                }else if(print === "method4"){
                    frondend.printDataEncomienda(link.attr('href'), { ids : data['id'] });
                }else{
                    
                }
            }else if(eval(printesc) === true){
                console.log("loadSubPage-printesc-init");
                core.printESCP(link.attr('href'), { ids : data['id'] });
            }else if(eval(email) === true){
                console.log("loadSubPage-email-init");
                core.request({
                    url : link.attr('href'),
                    method: 'POST', //Obligatorio
                    dataType: "html",
                    async: false,
                    extraParams: { ids : data['id'] }, 
                    successCallback: function(responseText){
                        if(!core.procesarRespuestaServidor(responseText)){ 
                            alert("Operación realizada satisfactoriamente.");
                        }
                    }
                });
            }else if(autoOpenFile !== null && $.trim(autoOpenFile) !== ""){
                console.log("loadSubPage-autoOpenFile-init");
                core.request({
                    url : link.attr('href'),
                    method: 'POST', //Obligatorio
                    dataType: "html",
                    async: false,
                    extraParams: { id : data['id'], type: autoOpenFile }, 
                    successCallback: function(responseText){
                       if(!core.procesarRespuestaServidor(responseText)){
                            if(typeof(jsPrintSetup) !== 'undefined'){
                                jsPrintSetup.setSilentPrint(false);
                            }
                            var urlPDF = core.getValueFromResponse(responseText, "data");
//                            console.debug("Abriendo pdf:" + urlPDF);
                            window.open(urlPDF);
                       }
                    }
                });
            }
            else
            {
                console.debug("loadSubPage-default-init");
                var dialog = core.request({
                    url : link.attr('href'),
                    method: 'GET', //Obligatorio
                    extraParams: data, 
                    dataType: "html",
                    async:true,
                    successCallback: function(success){
                        console.debug("loadSubPage-default-successCallback-init");
                        console.debug(success);
                        var component = document.createElement("div");
                        component.innerHTML = success;
                        var existForms = component.getElementsByTagName('form').length !== 0;

                        var buttons = {};
                        var fullscreen = $(link).data("fullscreen");
                        
                        if(existForms === true){
                           buttons['Aceptar'] = {
                                primary: true,
                                type: "info",
                                click: function() {
//                                    console.log("clic. aceptar....");
                                    if(eval(link.data("find")) === true){ //Es un buscador, solo hay que devolver el item seleccionado
//                                        console.log("El formulario está en modalidad de buscador...");
                                        var selectGrid = link.data("grid"); //selector del grid, puede ser por id, por css
                                        if(!selectGrid){
                                            throw new Error("La subpage está en modalidad de buscador, debe especificar la propiedad data-grid en el link."); 
                                        }
                                        var selected = core.getSelectedItemId(selectGrid);
                                        if(selected === null || $.trim(selected) === ""){
                                            var message = link.data("find-message");
                                            if(!message || message === null || $.trim(message) === ""){
                                                message =  "Debe seleccionar un elemento.";
                                            }
                                            alert(message);
                                            return;
                                        }else{
                                            if(successCallback && $.isFunction(successCallback)){
                                                successCallback(selected);
                                                if(eval(fullscreen) === true){
                                                    $("body").css("overflow-y", "auto");
                                                }
                                                $("div[id*='dialog_internal']").dialog2("close");
                                            }else{
                                               throw new Error("La subpage está en modalidad de buscador, debe especificar una successCallback."); 
                                            }
                                        }

                                    }else{  //hay que enviar un formulario
//                                        console.log("El formulario no está en modalidad de buscador...");
                                        var form = $("div[id*='dialog_internal']").find("form");
                                        if(form.length === 0){
                                            throw new Error("No se encontro el formulario en el dialogo.");
                                        }
                                        
                                        var nit = form.find(".inputNIT").val();
                                        if(nit === null || $.trim(nit) === ""){
                                            form.find(".inputNIT").val("CF");
                                        }
                                        
                                        var validator = $(form).validate({
                                            ignoreTitle: true,
                                            errorClass: "text-error"
                                        });

                                        if(validator.form() === true){
//                                            console.log("submitHandler...");
                                            var url = form.attr('action');
                                            $(form).ajaxSubmit({
                                                target: url,
                                                type : "POST",
                                                dataType: "html",
                                                cache : false,
                                                async:false,
                                                beforeSubmit: function(arr, $form, options) { 
                                                    core.showLoading({showLoading:true});
                                                },
                                                error: function() {
                                                    core.hideLoading({showLoading:true});
                                                },
                                                success: function(responseText) {
//                                                    console.log("submitHandler....success");
//                                                    console.debug(responseText);  
                                                    core.hideLoading({showLoading:true});
                                                    if(!core.procesarRespuestaServidor(responseText)){
//                                                       console.log("procesarRespuestaServidor....okook");
                                                       if(eval(fullscreen) === true){
                                                            $("body").css("overflow-y", "auto");
                                                       }
                                                       $("div[id*='dialog_internal']").dialog2("close");
                                                       $('#grid').flexReload();  //Refresh default grid
                                                       
                                                       var confirmarOperacion = options["confirmarOperacion"];
                                                       if(confirmarOperacion === undefined || confirmarOperacion === null){
                                                            confirmarOperacion = true;
                                                       }
                                                       
                                                       if(confirmarOperacion){
                                                            alert("Operación realizada satisfactoriamente.", function() {
                                                                var data = core.getValueFromResponse(responseText, 'data');
                                                                if(successCallback && $.isFunction(successCallback)){
                                                                    successCallback(data);
                                                                }
                                                            }); 
                                                       }else{
                                                            var data = core.getValueFromResponse(responseText, 'data');
                                                            if(successCallback && $.isFunction(successCallback)){
                                                                successCallback(data);
                                                            }
                                                       }
                                                       
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            };
                            buttons['Cancelar'] = {
                                //Como no es el boton primario se pide confirmacion
                                click: function() {
//                                    console.log("Cancelar - click - init");
                                    var modal = this;
//                                    console.debug(modal);
                                    modal.parent().hide();
                                    confirm("¿Está seguro que desea cancelar?", function(confirmed){
//                                        console.debug(confirmed);
                                        if(confirmed === true){
                                            $("body").css("overflow-y", "auto");
                                            modal.dialog2("close");
                                        }else{
                                            modal.parent().show();
                                        }
                                    });

                                }
                            };
                        }else{
                            //Cuando es primario no se pide confirmacion
                            buttons['Cancelar'] = {
                                primary: true,
                                type: "info",
                                click: function() {
//                                    console.log("Cancelar - click - init");
                                    $("body").css("overflow-y", "auto");
                                    this.dialog2("close");					
                                }
                            };
                        }

                        
                        core.showMessageDialog({
                            title:name,
                            fullscreen: fullscreen,
                            compact: true,
                            text: success,
                            defaultButtonOFF: true,
                            buttons: buttons
                        });
                        core.focus();
                    }
                });  
           
            }
    },
    
    printFactura : function(url, extraParams, successCallback) {
        if(!url || $.trim(url) === ""){
            throw new Error("Debe definir la url a imprimir.");
        }
        
        if(!extraParams){
             extraParams = {};
        }
        
        if(!(core.checkInstalljsPrintSetup() === true || core.checkInstallCustomQZprint() === true)) {
           alert("No se detecto ningun componente para imprimir facturas.", function (){
                if(successCallback && $.isFunction(successCallback)){
                    successCallback();
                }
           }); 
        }else{
            core.request({
                url : url,
                method: 'POST', //Obligatorio
                dataType: "html",
                async: false,
                extraParams: extraParams, 
                successCallback: function(responseText){
                   frondend.printFacturaInternal(responseText, successCallback);
                }
            });
        }
    },
    
    printFacturaInternal : function(responseText, successCallback) {
        if(!core.procesarRespuestaServidor(responseText)){
            var pluginJava = core.getValueFromResponse(responseText, "pluginJava");
            pluginJava = eval(pluginJava);
            if(pluginJava === 1 || pluginJava === true){
                core._printDataJavaPlugin(responseText, successCallback);
            }else{
                var iframe = document.createElement('iframe');
                iframe.setAttribute('src', 'about:blank');
                iframe.onload = function()
                {
                    core._printDataJsPrintSetup(iframe, responseText, successCallback);
                };
                document.body.appendChild(iframe);
            }
        }else{
            if(successCallback && $.isFunction(successCallback)){
                successCallback(responseText);
            }
        }
    },
    
    printVoucherBoleto : function(url, extraParams, successCallback) {
        if(!url || $.trim(url) === ""){
            throw new Error("Debe definir la url a imprimir.");
        }
        
        if(!extraParams){
             extraParams = {};
        }
        
        var idEstacion = $("#idEstacionTopMenu").val();
        if(extraParams['pdf'] === true || $.trim(idEstacion) === "" || $.trim(frondend.listaImpresorasDisponibles) === "" || 
                core.checkInstalljsPrintSetup() === false){
            if(url.indexOf(".pdf") === -1){
                url = url + ".pdf";
            }
            core.request({
                url : url,
                method: 'POST', //Obligatorio
                dataType: "html",
                async: false,
                extraParams: extraParams, 
                successCallback: function(responseText){
                    if(!core.procesarRespuestaServidor(responseText)){
                        setTimeout(function(){
                            var urlPDF = core.getValueFromResponse(responseText, "data");
                            window.open(urlPDF);
                        }, 500);     
                    }
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }
            });
        }else{
            core.request({
                url : url,
                method: 'POST', //Obligatorio
                dataType: "html",
                async: false,
                extraParams: extraParams, 
                successCallback: function(responseText){
                   if(!core.procesarRespuestaServidor(responseText)){
                        var iframe = document.createElement('iframe');
                        iframe.setAttribute('src', 'about:blank');
                        iframe.onload = function()
                        {
                            core._printDataJsPrintSetup(iframe, responseText, successCallback);
                        };
                        document.body.appendChild(iframe);
                   }else{
                       if(successCallback && $.isFunction(successCallback)){
                            successCallback(responseText);
                       }
                   }
                }
            });
        }
    },
    
    printDataEncomienda : function(url, extraParams, successCallback) {
        if(!url || $.trim(url) === ""){
            throw new Error("Debe definir la url a imprimir.");
        }
        
        if(!extraParams){
             extraParams = {};
        }
        
        var idEstacion = $("#idEstacionTopMenu").val();
        if(extraParams['pdf'] === true || $.trim(idEstacion) === "" || $.trim(frondend.listaImpresorasDisponibles) === "" || 
                core.checkInstalljsPrintSetup() === false){
            if(url.indexOf(".pdf") === -1){
                url = url + ".pdf";
            }
            core.request({
                url : url,
                method: 'POST', //Obligatorio
                dataType: "html",
                async: false,
                extraParams: extraParams, 
                successCallback: function(responseText){
                    if(!core.procesarRespuestaServidor(responseText)){
                        setTimeout(function(){
                            var urlPDF = core.getValueFromResponse(responseText, "data");
                            window.open(urlPDF);
                        }, 500);     
                    }
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }
            });
        }else{
            core.request({
                url : url,
                method: 'POST', //Obligatorio
                dataType: "html",
                async: false,
                extraParams: extraParams, 
                successCallback: function(responseText){
                   if(!core.procesarRespuestaServidor(responseText)){
                        var iframe = document.createElement('iframe');
                        iframe.setAttribute('src', 'about:blank');
                        iframe.onload = function()
                        {
                            core._printDataJsPrintSetup(iframe, responseText, successCallback);
                        };
                        document.body.appendChild(iframe);
                   }else{
                       if(successCallback && $.isFunction(successCallback)){
                            successCallback(responseText);
                       }
                   }
                }
            });
        }
    }
    
};