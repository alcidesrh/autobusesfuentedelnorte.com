entregarEncomienda = {
    
    funcionesAddOnload : function() {
//        console.debug("entregarEncomienda.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("entregarEncomienda.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        var pathlistarclientespaginando = $("#entregar_encomienda_command_clienteReceptor").data("pathlistarclientespaginando");
        $("#entregar_encomienda_command_clienteReceptor").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: pathlistarclientespaginando,
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        $("#entregar_encomienda_command_clienteDocumentoVirtual").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#entregar_encomienda_command_clienteDocumentoVirtual").data("pathlistarclientespaginando"),
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
        
        $("#entregar_encomienda_command_tipoPagoVirtual").select2({
            allowClear: true
        });
        
        $("#entregar_encomienda_command_monedaPagoVirtual").select2({
            allowClear: true
        });
        
        $("#entregar_encomienda_command_serieFacturaVirtual").select2({
            allowClear: false,
            data: []
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
        
        $("#addCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#entregar_encomienda_command_clienteReceptor");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entregar_encomienda_command_clienteReceptor').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             }, {
                 'confirmarOperacion' : false
             });
         });
        $("#updateCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function() {
//                console.debug("Actulizando datos del combo...."); 
                var element = $("#entregar_encomienda_command_clienteReceptor");
                var id = element.val();
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
//                            console.debug("Actulizando datos del combo...data");
//                            console.debug(data);
                            if( data.options && data.options[0]){
                                $('#entregar_encomienda_command_clienteReceptor').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
//                console.debug("Seteando elemento seleccionado..."); 
//                console.debug(id);
                if (id !== "") {
                    var element = $("#entregar_encomienda_command_clienteReceptor");
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
//                            console.debug("Actulizando datos del combo...data");
//                            console.debug(data);
                            if( data.options && data.options[0]){
                                $('#entregar_encomienda_command_clienteReceptor').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#addClienteDocumento").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#entregar_encomienda_command_clienteDocumentoVirtual");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entregar_encomienda_command_clienteDocumentoVirtual').select2('data', data.options[0]); 
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
                var element = $("#entregar_encomienda_command_clienteDocumentoVirtual");
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
                                $('#entregar_encomienda_command_clienteDocumentoVirtual').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachClienteDocumento").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                if (id !== "") {
                    var element = $("#entregar_encomienda_command_clienteDocumentoVirtual");
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#entregar_encomienda_command_clienteDocumentoVirtual').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         
         $("#entregar_encomienda_command_serieFacturaVirtual").on("change", function (){
             console.debug("serieFacturaVirtual-init");
             var idSerieFactura = $("#entregar_encomienda_command_serieFacturaVirtual").select2('val');
             var idEmpresa = $("#entregar_encomienda_command_serieFacturaVirtual").select2('data').idEmpresa;
             sessionStorage.setItem("sist_last_id_serie_factura_enco_" + idEmpresa, idSerieFactura);
         });
         
        $("#aceptar").click(entregarEncomienda.clickAceptar); 
        $("#entregar_encomienda_command_tipoPagoVirtual").on("change", entregarEncomienda.changeTipoPago);
        $("#entregar_encomienda_command_monedaPagoVirtual").on("change", entregarEncomienda.changeMonedaPago);
        $("#entregar_encomienda_command_efectivoVirtual").on("change", entregarEncomienda.changeEfectivo);
        $('#entregar_encomienda_command_facturarVirtual').bind("click", entregarEncomienda.checkFacturar);
    },
    
    imprimirEncomiendaFactura: function(responseText, successCallback) {
        frondend.printFacturaInternal(responseText, successCallback);
    },
    
    customPostValidateFactura: function(dialog) {
        
        if(frondend.listaImpresorasDisponibles === null || $.trim(frondend.listaImpresorasDisponibles) === ""){
            core.hiddenDialog2(dialog);
            alert("El sistema aún no ha podido detectar las impresoras para la facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var tipoPago = $("#entregar_encomienda_command_tipoPagoVirtual").prop("value");
        if(tipoPago === undefined || tipoPago === null || $.trim(tipoPago) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un tipo de pago.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }else{
            
            if(tipoPago === "1"){  //Efectivo
                
               var monedaPago = $("#entregar_encomienda_command_monedaPagoVirtual").prop("value");
                if(monedaPago === undefined || monedaPago === null || $.trim(monedaPago) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar una moneda de pago.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var efectivo = $("#entregar_encomienda_command_efectivoVirtual").prop("value");
                if(efectivo === undefined || efectivo === null || $.trim(efectivo) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un efectivo.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                } 
            }
        }
        
        if($("#entregar_encomienda_command_facturarVirtual").prop('checked') === true){
            var totalNeto = $("#entregar_encomienda_command_clienteDocumentoVirtual").prop("value");
            if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
                core.hiddenDialog2(dialog);
                alert("Debe seleccionar el cliente de la factura.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
        }
        
        var totalNeto = $("#entregar_encomienda_command_totalNetoVirtual").prop("value");
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total neto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var encomiendaForm = $("#encomiendaForm");
        if(core.customValidateForm(encomiendaForm) === true){
            var tipoDocumento = $("#entregar_encomienda_command_tipoDocumento").val();
            if(tipoDocumento !== "2"){  
//                console.debug("clickAceptar-no es por cobrar");
                $("#entregar_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
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
                                core.getPageForMenu($("#pathHomeEncomienda").val());
                            });
                         }
                    }
                });
                
            }else{
//                console.debug("clickAceptar-es por cobrar"); //Por cobrar
                if(entregarEncomienda.customPreValidateFactura() === true){
                    entregarEncomienda.loadSeriesFacturaActiva(function(){
                    $("#entregar_encomienda_command_facturarVirtual").prop('checked', false);
                    entregarEncomienda.checkFacturar();    
                    core.showMessageDialog({
                        title : "Facturar",
                        selector: $("#facturaDIV"),
                        removeOnClose : false,
                        uniqid : false,
                        buttons: {
                           Aceptar: {
                               click: function() {
                                   var dialogActual = this;
       //                            console.debug("Aceptar-click...");
                                   if(core.customValidateForm(encomiendaForm) === true && entregarEncomienda.customPostValidateFactura(this) === true){
                                       $("#entregar_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                       $("#entregar_encomienda_command_facturar").prop("value", $("#entregar_encomienda_command_facturarVirtual").prop('checked'));
                                       $("#entregar_encomienda_command_tipoPago").prop("value", $("#entregar_encomienda_command_tipoPagoVirtual").prop("value"));
                                       $("#entregar_encomienda_command_totalNeto").prop("value", $("#entregar_encomienda_command_totalNetoVirtual").prop("value"));
                                       $("#entregar_encomienda_command_monedaPago").prop("value", $("#entregar_encomienda_command_monedaPagoVirtual").prop("value"));
                                       $("#entregar_encomienda_command_tasa").prop("value", $("#entregar_encomienda_command_tasaVirtual").prop("value"));
                                       $("#entregar_encomienda_command_totalPago").prop("value", $("#entregar_encomienda_command_totalPagoVirtual").prop("value"));
                                       $("#entregar_encomienda_command_efectivo").prop("value", $("#entregar_encomienda_command_efectivoVirtual").prop("value"));
                                       $("#entregar_encomienda_command_vuelto").prop("value", $("#entregar_encomienda_command_vueltoVirtual").prop("value"));
                                       $("#entregar_encomienda_command_serieFactura").prop("value", $("#entregar_encomienda_command_serieFacturaVirtual").prop("value"));
                                       $("#entregar_encomienda_command_clienteDocumento").prop("value", $("#entregar_encomienda_command_clienteDocumentoVirtual").prop("value"));
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
                                               var facturar = eval($("#entregar_encomienda_command_facturar").prop("value"));
                                               core.hideLoading({showLoading:true});
                                               if(!core.procesarRespuestaServidor(responseText)){
       //                                            console.log("procesarRespuestaServidor....okook");
                                                   dialogActual.dialog2("close");
                                                   var info = core.getValueFromResponse(responseText, "info");
                                                   core.showNotification({text : info});
                                                   alert("Operación realizada satisfactoriamente. "  + info, function() {
                                                       core.getPageForMenu($("#pathHomeEncomienda").val());
                                                   });
                                                   
                                                   if(facturar){
                                                        entregarEncomienda.imprimirEncomiendaFactura(responseText);
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
                       });
                       entregarEncomienda.checkBloque2();   //Se chequea los div en cascada
                       entregarEncomienda.changeTipoPago(); //Se chequea los valores en cascada
                }
            }
        }
    },
    
    loadSeriesFacturaActiva : function(successCallback){
        var idEstacionDestino = $("#entregar_encomienda_command_idEstacionDestino").val();
        var idEmpresa = $("#entregar_encomienda_command_idEmpresa").val();
        $("#entregar_encomienda_command_serieFacturaVirtual").select2({ data: [] });
        if(idEstacionDestino !== null && $.trim(idEstacionDestino) !== "" && 
                idEmpresa !== null && $.trim(idEmpresa) !== "" ){
            core.request({
                url : $("#pathSeriesActivaPorEstacion").attr('value'),
                method: 'POST',
                extraParams: {
                    idEstacion : idEstacionDestino,
                    idEmpresa : idEmpresa,
                    tipoServicio : '2' //Encomienda
                }, 
                dataType: "json",
                async:true,
                successCallback: function(success){
                    var optionSeriesFacturas = success.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#entregar_encomienda_command_serieFacturaVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        if(optionSeriesFacturas[0]){
                            $("#entregar_encomienda_command_serieFacturaVirtual").select2('val', optionSeriesFacturas[0].id);
                            if(successCallback && $.isFunction(successCallback)){
                                successCallback();
                            }
                        }
                    }
                }
           });
        }
    },
    
   checkFacturar : function() {
        var checked = $("#entregar_encomienda_command_facturarVirtual").prop("checked");
        if(checked){
            $(".bloque0").removeClass("hidden");
        }else{
            $(".bloque0").addClass("hidden");
            $("#entregar_encomienda_command_clienteDocumentoVirtual").select2('val', '');
        }
    },
    
    customPreValidateFactura: function(dialog) {
        
        var cantidadMonedas = $("#entregar_encomienda_command_monedaPagoVirtual option").length;
        if( cantidadMonedas <= 0 ){
            core.hiddenDialog2(dialog);
            alert("Debe abrir una caja para facturar.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    checkBloque2: function(tipoPago) {
//        console.log("checkBloque2-init");
        if(!tipoPago){
            tipoPago = $("#entregar_encomienda_command_tipoPagoVirtual").val();
        }
        if(tipoPago === ""){  //Vacio
            $("#entregar_encomienda_command_totalNetoVirtual").val("");
            $(".bloque2").hide();
            entregarEncomienda.checkBloque3(tipoPago);
        }
        else if(tipoPago === "1"){ //Efectivo
            $(".bloque2").show();
            entregarEncomienda.checkBloque3(tipoPago);
        }
        else if(tipoPago === "2"){
            $(".bloque2").hide();
            entregarEncomienda.checkBloque3(tipoPago);
        }
        else if(tipoPago === "3"){
            $(".bloque2").hide();
            entregarEncomienda.checkBloque3(tipoPago);
        }
    },
    
    checkBloque3: function(tipoPago) {
//        console.log("checkBloque3-init");
        if(!tipoPago){
            tipoPago = $("#entregar_encomienda_command_tipoPagoVirtual").val();
        }
        var monedaPago = $("#entregar_encomienda_command_monedaPagoVirtual").val();
        if(tipoPago === "1" && monedaPago !== "" && monedaPago !== "1"){  //Efectivo
            $(".bloque3").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque3").hide();
        }
        entregarEncomienda.checkBloque4(tipoPago);
    },
    
    checkBloque4: function(tipoPago) {
//        console.log("checkBloque4-init");
        if(!tipoPago){
            tipoPago = $("#entregar_encomienda_command_tipoPagoVirtual").val();
        }
        if(tipoPago === "1"){  //Efectivo
            $(".bloque4").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque4").hide();
        }
    },
   
    calcularImporteTotalMonedaBase: function() {
//        console.log("calcularImporteTotalMonedaBase....");
        var idEncomiendaOriginal = $("#entregar_encomienda_command_encomiendaOriginal").val();
        var idTipoPago = $("#entregar_encomienda_command_tipoPagoVirtual").val();
        $("#entregar_encomienda_command_totalNetoVirtual").val("");
        if(idEncomiendaOriginal !== null && $.trim(idEncomiendaOriginal) !== "" && 
           idTipoPago !== null && $.trim(idTipoPago) !== ""){
//           console.log("buscando importe...");
           core.request({
                url : $("#pathCalcularImporteTotalMonedaBaseEntregarEncomienda").attr('value'),
                method: 'POST', //Obligatorio
                extraParams: {
                    idEncomiendaOriginal : idEncomiendaOriginal,
                    idTipoPago : idTipoPago
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
                        $("#entregar_encomienda_command_totalNetoVirtual").val(success.total);
                        if(idTipoPago === "1"){  //Solo si es efectivo, en otro caso no hace falta.. ya estan limpios los inputs...
                            entregarEncomienda.changeMonedaPago();
                        }
                    }
                }
           });
       }
    },
    
    changeTipoPago: function() {
//        console.log("changeTipoPago-init");
        entregarEncomienda.checkBloque2();
        $("#entregar_encomienda_command_totalNetoVirtual").val("");
        $("#entregar_encomienda_command_tasaVirtual").val("");
        $("#entregar_encomienda_command_totalPagoVirtual").val("");
        $("#entregar_encomienda_command_vueltoVirtual").val("");
        $("#entregar_encomienda_command_efectivoVirtual").val("");
        entregarEncomienda.calcularImporteTotalMonedaBase();
    },
    
    changeMonedaPago: function() {
//      console.log("changeMonedaPago-init");  
      var monedaPago = $("#entregar_encomienda_command_monedaPagoVirtual").val();
      $("#entregar_encomienda_command_tasaVirtual").val("");
      $("#entregar_encomienda_command_totalPagoVirtual").val("");
      $("#entregar_encomienda_command_vueltoVirtual").val("");
      if(monedaPago === null || $.trim(monedaPago) === ""){
        $("#prependTotalPagoVirtual").text("???");
        $("#prependEfectivoVirtual").text("???");
      }
      else if($.trim(monedaPago) === "1"){
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        entregarEncomienda.changeEfectivo();
      }
      else{
        //NO SON QUETSALES
        var totalNeto = $("#entregar_encomienda_command_totalNetoVirtual").val();
        var idMonedaPago = $("#entregar_encomienda_command_monedaPagoVirtual").val();
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
                        $("#entregar_encomienda_command_tasaVirtual").val(success.tasa);
                        $("#entregar_encomienda_command_totalPagoVirtual").val(success.total);
                        entregarEncomienda.changeEfectivo();
                    }
                }
           });
        }
      }
      entregarEncomienda.checkBloque3();
    },
    
    
    changeEfectivo: function() {
//      console.log("changeEfectivo-init");
      var monedaPago = $("#entregar_encomienda_command_monedaPagoVirtual").val();
      $("#entregar_encomienda_command_vueltoVirtual").val("");
      
      var efectivo = $("#entregar_encomienda_command_efectivoVirtual").val();
      if(!$.isNumeric(efectivo)){
          $("#entregar_encomienda_command_efectivoVirtual").val("");
          return;
      }else{
          efectivo = parseFloat(efectivo).toFixed(2);
          $("#entregar_encomienda_command_efectivoVirtual").val(efectivo);
      }
      
      if($.trim(monedaPago) === "1"){ //GTQ
         var totalNeto = $("#entregar_encomienda_command_totalNetoVirtual").val();
         var vuelto = efectivo - parseFloat(totalNeto);
         vuelto = parseFloat(vuelto).toFixed(2);
         if(vuelto === 0){
              $("#entregar_encomienda_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalNeto + ".", function (){
                modal.show();
                $("#entregar_encomienda_command_efectivoVirtual").val(totalNeto); // Se le setea el importe minimo.
                $("#entregar_encomienda_command_vueltoVirtual").val(0);
             });
         }else{
             $("#entregar_encomienda_command_vueltoVirtual").val(vuelto);
         }
      }
      else{
        //NO SON QUETSALES
        var totalPago = $("#entregar_encomienda_command_totalPagoVirtual").val();
        var vuelto = efectivo - parseFloat(totalPago);
         vuelto = parseFloat(vuelto).toFixed(2); //Vuelto en EUR O USD
         if(vuelto === 0){
              $("#entregar_encomienda_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalPago + ".", function (){
                modal.show();
                $("#entregar_encomienda_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                $("#entregar_encomienda_command_vueltoVirtual").val(0);
             });
         }else{

            //Convirtiendo el vuelto a Quetsales
            var idMonedaPago = $("#entregar_encomienda_command_monedaPagoVirtual").val();
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
                           $("#entregar_encomienda_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                           $("#entregar_encomienda_command_vueltoVirtual").val(0);
                        });
                    }else{
                        $("#entregar_encomienda_command_vueltoVirtual").val(success.total);
                    }
                }
           });
        }
             
             
             
             
         }
        
      }
        
    }
    
    
    
    
};
