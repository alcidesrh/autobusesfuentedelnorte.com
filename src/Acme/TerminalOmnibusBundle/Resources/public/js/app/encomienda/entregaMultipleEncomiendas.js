entregaMultipleEncomiendas = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#entrega_multiple_encomienda_command_estacion").select2({
             allowClear: $("#entrega_multiple_encomienda_command_estacion option[value='']").length === 1
        });
        $("#entrega_multiple_encomienda_command_estacion").select2("readonly", ($("#entrega_multiple_encomienda_command_estacion").attr("readonly") === "readonly"));
        
        $("#entrega_multiple_encomienda_command_empresa").select2({
             allowClear: $("#entrega_multiple_encomienda_command_empresa option[value='']").length === 1
        });
        $("#entrega_multiple_encomienda_command_empresa").select2("readonly", ($("#entrega_multiple_encomienda_command_empresa").attr("readonly") === "readonly"));
        
        $("#entrega_multiple_encomienda_command_clienteReceptor").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#entrega_multiple_encomienda_command_clienteReceptor").data("pathlistarclientespaginando"),
                dataType: 'json',
                type: "GET",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual").data("pathlistarclientespaginando"),
                dataType: 'json',
                type: "GET",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        $("#entrega_multiple_encomienda_command_tipoPagoVirtual").select2({
            allowClear: true
        });
        
        $("#entrega_multiple_encomienda_command_monedaPagoVirtual").select2({
            allowClear: true
        });
        
        $("#entrega_multiple_encomienda_command_serieFacturaVirtual").select2({
            allowClear: false,
            data: []
        });
        
        entregaMultipleEncomiendas.buscarDatos();
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
        $("#entrega_multiple_encomienda_command_estacion").on("change", entregaMultipleEncomiendas.buscarDatos);
        $("#entrega_multiple_encomienda_command_empresa").on("change", entregaMultipleEncomiendas.buscarDatos);
        $("#aceptar").click(entregaMultipleEncomiendas.clickAceptar);
        $("#addCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#entrega_multiple_encomienda_command_clienteReceptor");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entrega_multiple_encomienda_command_clienteReceptor').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             }, {
                 'confirmarOperacion' : false
             });
         });
         $("#updateCliente").click(function(e) {
             console.log("updateCliente-clic");
             frondend.loadSubPage(e, $(this), function() {
                var element = $("#entrega_multiple_encomienda_command_clienteReceptor");
                var id = element.val();
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entrega_multiple_encomienda_command_clienteReceptor').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                if (id !== "") {
                    var element = $("#entrega_multiple_encomienda_command_clienteReceptor");
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entrega_multiple_encomienda_command_clienteReceptor').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#addClienteDocumento").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entrega_multiple_encomienda_command_clienteDocumentoVirtual').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             }, {
                 'confirmarOperacion' : false
             });
         });
         $("#updateClienteDocumento").click(function(e) {
             console.log("updateCliente-clic");
             frondend.loadSubPage(e, $(this), function() {
                var element = $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual");
                var id = element.val();
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entrega_multiple_encomienda_command_clienteDocumentoVirtual').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachClienteDocumento").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                if (id !== "") {
                    var element = $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual");
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entrega_multiple_encomienda_command_clienteDocumentoVirtual').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#entrega_multiple_encomienda_command_serieFacturaVirtual").on("change", function (){
             console.debug("serieFacturaVirtual-init");
             var idSerieFactura = $("#entrega_multiple_encomienda_command_serieFacturaVirtual").select2('val');
             var idEmpresa = $("#entrega_multiple_encomienda_command_serieFacturaVirtual").select2('data').idEmpresa;
             sessionStorage.setItem("sist_last_id_serie_factura_enco_" + idEmpresa, idSerieFactura);
         });
         
         $("#entrega_multiple_encomienda_command_tipoPagoVirtual").on("change", entregaMultipleEncomiendas.changeTipoPago);
         $("#entrega_multiple_encomienda_command_monedaPagoVirtual").on("change", entregaMultipleEncomiendas.changeMonedaPago);
         $("#entrega_multiple_encomienda_command_efectivoVirtual").on("change", entregaMultipleEncomiendas.changeEfectivo);
         $('#entrega_multiple_encomienda_command_facturarVirtual').bind("click", entregaMultipleEncomiendas.checkFacturar);
    },
    
    imprimirEncomiendaFactura: function(responseText, successCallback) {
        frondend.printFacturaInternal(responseText, successCallback);
    },

    buscarDatos : function(e) {
        console.debug("buscarDatos-init");
        $("#encomiendasBody").find("tr").not("#encomiendasVacioTR").remove();
        $("#encomiendasBody").find("#encomiendasVacioTR").show();
        $("#entrega_multiple_encomienda_command_importeTotal").val("");

        var estacion = $("#entrega_multiple_encomienda_command_estacion").val();
        var empresa = $("#entrega_multiple_encomienda_command_empresa").val();
        if(estacion !== null && $.trim(estacion) !== "" && empresa !== null && $.trim(empresa) !== ""){
           core.request({
                url : $("#pathListarEncomiendasPendientesEntrega").attr('value'),
                method: 'POST',
                extraParams: {
                    estacion : estacion,
                    empresa : empresa,
                    showClientes : true
                }, 
                dataType: "json",
                async:false,
                successCallback: function(success){
                    console.debug(success);
                    var encomiendas = success.encomiendas;
                    var pathConsultarEncomienda = $("#pathConsultarEncomienda").val();
                    $.each(encomiendas, function(){
                        $("#encomiendasBody").find("#encomiendasVacioTR").hide();
                        var itemTR = $("<tr id='E"+this.id+"'>"+
                                       "<td class='center'><a class='btn'>"+this.id+"</a></td>"+
                                       "<td class='center inputCheckbox'><input data-valor='"+this.id+"' data-importe='"+this.importe+"' data-iddoc='"+this.idDoc+"' type='checkbox'></td>"+
                                       "<td class='center'>"+this.empresa+"</td>"+
                                       "<td class='center'>"+this.fecha+"</td>"+
                                       "<td class='center'>"+this.doc+"</td>"+
                                       "<td class='center'>"+this.cli+"</td>"+
                                       "<td class='center'> GTQ "+this.importe+"</td>"+
                                       "<td class='center'>"+this.desc+"</td>"+
                                       "</tr>");
                        $("#encomiendasBody").append(itemTR);
                        itemTR.val(this.id); 
                        itemTR.find("a").attr('href', pathConsultarEncomienda);
                        itemTR.find("a").data("index", "E"+this.id);
                        itemTR.find("a").data("title", "Consultar Encomienda");
                        itemTR.find("a").data("fullscreen", true);
                        itemTR.find("a").click(frondend.loadSubPage);
                    });
                    
                    $(".inputCheckbox").find("input").bind("click", entregaMultipleEncomiendas.totalizar);
                    entregaMultipleEncomiendas.totalizar();
                    
                    var optionSeriesFacturas = success.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#entrega_multiple_encomienda_command_serieFacturaVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        if(optionSeriesFacturas.length > 0 && optionSeriesFacturas[0]){
                            $("#entrega_multiple_encomienda_command_serieFacturaVirtual").select2('val', optionSeriesFacturas[0].id);
                        }
                    }
                }
           });
        }else{
            entregaMultipleEncomiendas.totalizar();
        }
    },
    
    syncronizarLista : function(e) {
        var listaIdEncomiendas = [];
        var items = $(".inputCheckbox").find("input");
        items.each(function (index, item){
            var checked = $(item).prop("checked");
            if(checked){
                listaIdEncomiendas.push($(item).data("valor"));
            }
        });
        $("#entrega_multiple_encomienda_command_listaIdEncomiendas").val(JSON.stringify(listaIdEncomiendas));
    },
    
    totalizar : function() {
        console.log("totalizar-init");
        var tipoDocumentoEncomienda = null;
        var total = 0;
        var items = $(".inputCheckbox").find("input");
        items.each(function (index, item){
            var checked = $(item).prop("checked");
            if(checked){
                console.log("chequeando item checked...");
                if(tipoDocumentoEncomienda === null){
                    console.log("tipoDocumentoEncomienda is null");
                    tipoDocumentoEncomienda = $(item).data("iddoc");
                }else{
                    console.log("tipoDocumentoEncomienda: " + tipoDocumentoEncomienda + ", y el item tiene: " + $(item).data("iddoc"));
                    if(tipoDocumentoEncomienda !== $(item).data("iddoc")){
                         console.log("son diferentes...desmarcando...");
                        $(item).prop("checked", false);
                        return;
                    }
                }
                total += core.customParseFloat($(item).data("importe"));
            }else{
                console.log("chequeando item...");
            }
        });
        $("#entrega_multiple_encomienda_command_tipoDocumentoEncomienda").val(tipoDocumentoEncomienda);
        $("#entrega_multiple_encomienda_command_importeTotal").val(total);
    },
    
    clickAceptar: function(e) {
        console.debug("clickAceptar-init");
        e.preventDefault();
        e.stopPropagation();
        
        var encomiendaForm = $("#entregaMultipleEncomiendas");
        entregaMultipleEncomiendas.syncronizarLista();
        if(core.customValidateForm(encomiendaForm) === true && entregaMultipleEncomiendas.communPreValidate() === true){
            var tipoDocumento = $("#entrega_multiple_encomienda_command_tipoDocumentoEncomienda").val();
            if(tipoDocumento !== "2"){ //Si no es por Cobrar
                $("#entrega_multiple_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
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
                            var info = core.getValueFromResponse(responseText, "info");
                            core.showNotification({text : info});
                            alert("Operación realizada satisfactoriamente. " + info, function() {
                                entregaMultipleEncomiendas.buscarDatos();
                            });
                        }
                    }
                });
            }else{
                if(entregaMultipleEncomiendas.customPreValidateFactura() === true){
                    $("#entrega_multiple_encomienda_command_facturarVirtual").prop('checked', false);
                    entregaMultipleEncomiendas.checkFacturar();
                    core.showMessageDialog({
                        title : "Facturar",
                        selector: $("#facturaDIV"),
                        removeOnClose : false,
                        uniqid : false,
                        buttons: {
                           Aceptar: {
                               click: function() {
                                   var dialogActual = this;
                                   if(core.customValidateForm(encomiendaForm) === true && entregaMultipleEncomiendas.customPostValidateFactura(this) === true){
                                       $("#entrega_multiple_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                       $("#entrega_multiple_encomienda_command_facturar").prop("value", $("#entrega_multiple_encomienda_command_facturarVirtual").prop('checked'));
                                       $("#entrega_multiple_encomienda_command_tipoPago").prop("value", $("#entrega_multiple_encomienda_command_tipoPagoVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_totalNeto").prop("value", $("#entrega_multiple_encomienda_command_totalNetoVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_monedaPago").prop("value", $("#entrega_multiple_encomienda_command_monedaPagoVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_tasa").prop("value", $("#entrega_multiple_encomienda_command_tasaVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_totalPago").prop("value", $("#entrega_multiple_encomienda_command_totalPagoVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_efectivo").prop("value", $("#entrega_multiple_encomienda_command_efectivoVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_vuelto").prop("value", $("#entrega_multiple_encomienda_command_vueltoVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_serieFactura").prop("value", $("#entrega_multiple_encomienda_command_serieFacturaVirtual").prop("value"));
                                       $("#entrega_multiple_encomienda_command_clienteDocumento").prop("value", $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual").prop("value"));
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
       //                                        console.log("submitHandler....success");
       //                                        console.debug(responseText);  
                                               var facturar = eval($("#entrega_multiple_encomienda_command_facturar").prop("value"));
                                               core.hideLoading({showLoading:true});
                                               if(!core.procesarRespuestaServidor(responseText)){
       //                                            console.log("procesarRespuestaServidor....okook");
                                                   dialogActual.dialog2("close");
                                                   var info = core.getValueFromResponse(responseText, "info");
                                                   core.showNotification({text : info});
                                                   alert("Operación realizada satisfactoriamente. "  + info, function() {
                                                       entregaMultipleEncomiendas.buscarDatos();
                                                   });
                                                   
                                                   if(facturar){
                                                        entregaMultipleEncomiendas.imprimirEncomiendaFactura(responseText);
                                                   }
                                               }
                                          }
                                       });
                                   }
                               }, 
                               primary: true,
                               type: "info"
                           }, 
                           Cancelar: function() {
                               this.dialog2("close");					
                           }				    
                           }
                       }); 
                       
                    entregaMultipleEncomiendas.checkBloque2();   //Se chequea los div en cascada
                    entregaMultipleEncomiendas.changeTipoPago(); //Se chequea los valores en cascada
                }
            }
        }
    },
    
    checkFacturar : function() {
        var checked = $("#entrega_multiple_encomienda_command_facturarVirtual").prop("checked");
        if(checked){
            $(".bloque0").removeClass("hidden");
        }else{
            $(".bloque0").addClass("hidden");
            $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual").select2('val', '');
        }
    },
    
    communPreValidate : function() {
      
        var listaIdEncomiendas = $("#entrega_multiple_encomienda_command_listaIdEncomiendas").val();
        if(listaIdEncomiendas){ listaIdEncomiendas = JSON.parse(listaIdEncomiendas); }
        else{ listaIdEncomiendas = []; }
        if(listaIdEncomiendas.length === 0){
            alert("Debe seleccionar al menos una encomienda.");
            return false;
        }
        
        return true;
    },
    
    customPreValidateFactura: function(dialog) {
                
        var cantidadMonedas = $("#entrega_multiple_encomienda_command_monedaPagoVirtual option").length;
        if( cantidadMonedas <= 0 ){
            core.hiddenDialog2(dialog);
            alert("Debe abrir una caja para facturar.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    customPostValidateFactura: function(dialog) {
        
        if(frondend.listaImpresorasDisponibles === null || $.trim(frondend.listaImpresorasDisponibles) === ""){
            core.hiddenDialog2(dialog);
            alert("El sistema aún no ha podido detectar las impresoras para la facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var tipoPago = $("#entrega_multiple_encomienda_command_tipoPagoVirtual").prop("value");
        if(tipoPago === undefined || tipoPago === null || $.trim(tipoPago) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un tipo de pago.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }else{
            
            if(tipoPago === "1"){  //Efectivo
                
               var monedaPago = $("#entrega_multiple_encomienda_command_monedaPagoVirtual").prop("value");
                if(monedaPago === undefined || monedaPago === null || $.trim(monedaPago) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar una moneda de pago.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var efectivo = $("#entrega_multiple_encomienda_command_efectivoVirtual").prop("value");
                if(efectivo === undefined || efectivo === null || $.trim(efectivo) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un efectivo.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                } 
            }
        }
        
        if($("#entrega_multiple_encomienda_command_facturarVirtual").prop('checked') === true){
            var cantidadMaxima = 4;
            var listaIdEncomiendas = $("#entrega_multiple_encomienda_command_listaIdEncomiendas").val();
            if(listaIdEncomiendas){ listaIdEncomiendas = JSON.parse(listaIdEncomiendas); }
            else{ listaIdEncomiendas = []; }
            if(listaIdEncomiendas.length > cantidadMaxima){
                alert("No se puede seleccionar más de " + cantidadMaxima + " encomiendas a la vez cuando se factura.");
                return;
            }  
            
            var totalNeto = $("#entrega_multiple_encomienda_command_clienteDocumentoVirtual").prop("value");
            if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
                core.hiddenDialog2(dialog);
                alert("Debe seleccionar el cliente de la factura.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
        }
        
        var totalNeto = $("#entrega_multiple_encomienda_command_totalNetoVirtual").prop("value");
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total neto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    checkBloque2: function(tipoPago) {
//        console.log("checkBloque2-init");
        if(!tipoPago){
            tipoPago = $("#entrega_multiple_encomienda_command_tipoPagoVirtual").val();
        }
        if(tipoPago === ""){  //Vacio
            $("#entrega_multiple_encomienda_command_totalNetoVirtual").val("");
            $(".bloque2").hide();
            entregaMultipleEncomiendas.checkBloque3(tipoPago);
        }
        else if(tipoPago === "1"){ //Efectivo
            $(".bloque2").show();
            entregaMultipleEncomiendas.checkBloque3(tipoPago);
        }
        else if(tipoPago === "2"){
            $(".bloque2").hide();
            entregaMultipleEncomiendas.checkBloque3(tipoPago);
        }
        else if(tipoPago === "3"){
            $(".bloque2").hide();
            entregaMultipleEncomiendas.checkBloque3(tipoPago);
        }
    },
    
    checkBloque3: function(tipoPago) {
//        console.log("checkBloque3-init");
        if(!tipoPago){
            tipoPago = $("#entrega_multiple_encomienda_command_tipoPagoVirtual").val();
        }
        var monedaPago = $("#entrega_multiple_encomienda_command_monedaPagoVirtual").val();
        if(tipoPago === "1" && monedaPago !== "" && monedaPago !== "1"){  //Efectivo
            $(".bloque3").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque3").hide();
        }
        entregaMultipleEncomiendas.checkBloque4(tipoPago);
    },
    
    checkBloque4: function(tipoPago) {
//        console.log("checkBloque4-init");
        if(!tipoPago){
            tipoPago = $("#entrega_multiple_encomienda_command_tipoPagoVirtual").val();
        }
        if(tipoPago === "1"){  //Efectivo
            $(".bloque4").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque4").hide();
        }
    },
   
    calcularImporteTotalMonedaBase: function() {
//        console.log("calcularImporteTotalMonedaBase....");
        var idTipoPago = $("#entrega_multiple_encomienda_command_tipoPagoVirtual").val();
        var importeTotal = $("#entrega_multiple_encomienda_command_importeTotal").val();
        $("#entrega_multiple_encomienda_command_totalNetoVirtual").val(importeTotal);
        if(idTipoPago === "1"){  //Solo si es efectivo, en otro caso no hace falta.. ya estan limpios los inputs...
            entregaMultipleEncomiendas.changeMonedaPago();
        }
    },
    
    changeTipoPago: function() {
//        console.log("changeTipoPago-init");
        entregaMultipleEncomiendas.checkBloque2();
        $("#entrega_multiple_encomienda_command_totalNetoVirtual").val("");
        $("#entrega_multiple_encomienda_command_tasaVirtual").val("");
        $("#entrega_multiple_encomienda_command_totalPagoVirtual").val("");
        $("#entrega_multiple_encomienda_command_vueltoVirtual").val("");
        $("#entrega_multiple_encomienda_command_efectivoVirtual").val("");
        entregaMultipleEncomiendas.calcularImporteTotalMonedaBase();
    },
    
    changeMonedaPago: function() {
//      console.log("changeMonedaPago-init");  
      var monedaPago = $("#entrega_multiple_encomienda_command_monedaPagoVirtual").val();
      $("#entrega_multiple_encomienda_command_tasaVirtual").val("");
      $("#entrega_multiple_encomienda_command_totalPagoVirtual").val("");
      $("#entrega_multiple_encomienda_command_vueltoVirtual").val("");
      if(monedaPago === null || $.trim(monedaPago) === ""){
        $("#prependTotalPagoVirtual").text("???");
        $("#prependEfectivoVirtual").text("???");
      }
      else if($.trim(monedaPago) === "1"){
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        entregaMultipleEncomiendas.changeEfectivo();
      }
      else{
        //NO SON QUETSALES
        var totalNeto = $("#entrega_multiple_encomienda_command_totalNetoVirtual").val();
        var idMonedaPago = $("#entrega_multiple_encomienda_command_monedaPagoVirtual").val();
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        if(totalNeto !== null && $.trim(totalNeto) !== "" && 
           idMonedaPago !== null && $.trim(idMonedaPago) !== ""){
           core.request({
                url : $("#pathCalcularImporteTotalPorMoneda").attr('value'),
                method: 'POST',
                extraParams: {
                    totalNeto : totalNeto,
                    idMoneda : idMonedaPago
                }, 
                dataType: "json",
                async:true,
                successCallback: function(success){
//                    console.debug(success);
                    if(success.error && $.trim(success.error) !== ""){
                        var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                        modal.hide();
                        alert(success.error, function (){
                           modal.show(); 
                        });
                    }else{
                        $("#prependTotalPagoVirtual").text(success.sigla);
                        $("#prependEfectivoVirtual").text(success.sigla);
                        $("#entrega_multiple_encomienda_command_tasaVirtual").val(success.tasa);
                        $("#entrega_multiple_encomienda_command_totalPagoVirtual").val(success.total);
                        entregaMultipleEncomiendas.changeEfectivo();
                    }
                }
           });
        }
      }
      entregaMultipleEncomiendas.checkBloque3();
    },
    
    
    changeEfectivo: function() {
//      console.log("changeEfectivo-init");
      var monedaPago = $("#entrega_multiple_encomienda_command_monedaPagoVirtual").val();
      $("#entrega_multiple_encomienda_command_vueltoVirtual").val("");
      
      var efectivo = $("#entrega_multiple_encomienda_command_efectivoVirtual").val();
      if(!$.isNumeric(efectivo)){
          $("#entrega_multiple_encomienda_command_efectivoVirtual").val("");
          return;
      }else{
          efectivo = parseFloat(efectivo).toFixed(2);
          $("#entrega_multiple_encomienda_command_efectivoVirtual").val(efectivo);
      }
      
      if($.trim(monedaPago) === "1"){ //GTQ
         var totalNeto = $("#entrega_multiple_encomienda_command_totalNetoVirtual").val();
         var vuelto = efectivo - parseFloat(totalNeto);
         vuelto = parseFloat(vuelto).toFixed(2);
         if(vuelto === 0){
              $("#entrega_multiple_encomienda_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalNeto + ".", function (){
                modal.show();
                $("#entrega_multiple_encomienda_command_efectivoVirtual").val(totalNeto); // Se le setea el importe minimo.
                $("#entrega_multiple_encomienda_command_vueltoVirtual").val(0);
             });
         }else{
             $("#entrega_multiple_encomienda_command_vueltoVirtual").val(vuelto);
         }
      }
      else{
        //NO SON QUETSALES
        var totalPago = $("#entrega_multiple_encomienda_command_totalPagoVirtual").val();
        var vuelto = efectivo - parseFloat(totalPago);
         vuelto = parseFloat(vuelto).toFixed(2); //Vuelto en EUR O USD
         if(vuelto === 0){
              $("#entrega_multiple_encomienda_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalPago + ".", function (){
                modal.show();
                $("#entrega_multiple_encomienda_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                $("#entrega_multiple_encomienda_command_vueltoVirtual").val(0);
             });
         }else{

            //Convirtiendo el vuelto a Quetsales
            var idMonedaPago = $("#entrega_multiple_encomienda_command_monedaPagoVirtual").val();
            if(idMonedaPago !== null && $.trim(idMonedaPago) !== ""){
                core.request({
                    url : $("#pathCalcularImporteTotalPorMoneda").attr('value'),
                    method: 'POST',
                    extraParams: {
                        totalNeto : vuelto,
                        idMoneda : idMonedaPago,
                        dir : false
                    }, 
                    dataType: "json",
                    async:true,
                    successCallback: function(success){
//                    console.debug(success);
                    if(success.error && $.trim(success.error) !== ""){
                        var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                        modal.hide();
                        alert(success.error, function (){
                           modal.show(); 
                           $("#entrega_multiple_encomienda_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                           $("#entrega_multiple_encomienda_command_vueltoVirtual").val(0);
                        });
                    }else{
                        $("#entrega_multiple_encomienda_command_vueltoVirtual").val(success.total);
                    }
                }
           });
        }
         }
        
      }
        
    }
    
    
};
