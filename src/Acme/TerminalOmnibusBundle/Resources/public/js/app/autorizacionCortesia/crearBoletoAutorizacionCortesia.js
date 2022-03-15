crearBoletoAutorizacionCortesia = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        var pathlistarclientespaginando = $("#crear_boleto_autorizacion_cortesia_command_cliente").data("pathlistarclientespaginando");
        $("#crear_boleto_autorizacion_cortesia_command_cliente").select2({
            minimumInputLength: 1,
            closeOnSelect : false,
            ajax: { 
                   url: pathlistarclientespaginando,
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
        
        $('#crear_boleto_autorizacion_cortesia_command_fechaSalida').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-1d",
            endDate: "+3m",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#crear_boleto_autorizacion_cortesia_command_fechaSalida').datepicker("setDate", new Date());
        
        if(core.isMovil()){
            
            $(".widgetReal").hide();
            $(".widgetAux").show();
            
        }else{
            
            $(".widgetReal").show();
            $(".widgetAux").hide();
            
            $("#crear_boleto_autorizacion_cortesia_command_estacionOrigen").select2();
            
            $("#crear_boleto_autorizacion_cortesia_command_salida").select2({
                data: []
            });
            
            $("#crear_boleto_autorizacion_cortesia_command_estacionSubeEn").select2({
                data: []
            });
        
            $("#crear_boleto_autorizacion_cortesia_command_estacionBajaEn").select2({
                data: []
            });
        }
        
        crearBoletoAutorizacionCortesia.loadSalidas();
        crearBoletoAutorizacionCortesia.checkSelectedSalida();
     },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'), false);
                }
            });
        }); 
        $("#aceptar").click(crearBoletoAutorizacionCortesia.clickAceptar);
         
         $("#addCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#crear_boleto_autorizacion_cortesia_command_cliente");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#crear_boleto_autorizacion_cortesia_command_cliente').select2('data', data.options[0]); 
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
                var element = $("#crear_boleto_autorizacion_cortesia_command_cliente");
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
                                $('#crear_boleto_autorizacion_cortesia_command_cliente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
        
        $('#crear_boleto_autorizacion_cortesia_command_fechaSalida').datepicker().on('changeDate', function (e) {
            e.preventDefault();
            e.stopPropagation();
            crearBoletoAutorizacionCortesia.loadSalidas();
        });
        $("#crear_boleto_autorizacion_cortesia_command_estacionOrigen").on("change", crearBoletoAutorizacionCortesia.loadSalidas);
        $("#crear_boleto_autorizacion_cortesia_command_salida").on("change", crearBoletoAutorizacionCortesia.checkSelectedSalida);
        $("#salidaAux").on("change", crearBoletoAutorizacionCortesia.checkSelectedSalida);
    },
    
    
    loadSalidas : function() {
        console.log("loadSalidas...");
        var fecha = $("#crear_boleto_autorizacion_cortesia_command_fechaSalida").val();
        var estacion = $("#crear_boleto_autorizacion_cortesia_command_estacionOrigen").val();
        if(fecha === null || $.trim(fecha) === "" || estacion === null || $.trim(estacion) === ""){
            $("#salidaDIV").hide();
            return;
        }
        core.request({
             url : $("#pathListarSalidas").val(),
             type: "POST",
             dataType: "json",
             async: false,
             extraParams : {
                 fecha : fecha,
                 estacion : estacion
             },
             successCallback: function(data){
                $("#salidaDIV").show();
                if( data.optionSalidas ){
                    if(core.isMovil()){
                        $('#salidaAux').find('option').remove();
                        $.each(data.optionSalidas, function (i, item) {
                            $('#salidaAux').append($('<option>', { 
                                value: item.id,
                                text : item.text 
                            }));
                        });
                        $('#salidaAux').val("");
                    }else{
                        $("#crear_boleto_autorizacion_cortesia_command_salida").select2({
                            data: { results: data.optionSalidas }
                        });
                    }
                }
                crearBoletoAutorizacionCortesia.checkSelectedSalida();
             }
        });
    },
    
    checkSelectedSalida : function() {
        var idSalida = null;
        if(core.isMovil()){
            idSalida = $('#salidaAux').val();
        }else{
            idSalida = $("#crear_boleto_autorizacion_cortesia_command_salida").prop("value");
        }       
        if(idSalida === null || $.trim(idSalida) === ""){
            if(core.isMovil()){
                $('#estacionSubeEnAux').find('option').remove();
                $('#estacionBajaEnAux').find('option').remove();
            }else{
                $("#crear_boleto_autorizacion_cortesia_command_estacionSubeEn").select2({ data: [] });
                $("#crear_boleto_autorizacion_cortesia_command_estacionBajaEn").select2({ data: [] });
            }
            $("#listaAsientosHidden").val(JSON.stringify([]));
            $("#listaSenalesHidden").val(JSON.stringify([]));
            $("#listaBoletosHidden").val(JSON.stringify([]));
            $("#listaReservacionesHidden").val(JSON.stringify([]));
            $("#dependenciasSelecccionSalidaDIV").hide();
            $("#utilizadoSpan").text(0);
            $("#totalSpan").text(0);
            crearBoletoAutorizacionCortesia.mostrarIconos();
        }else{
            core.request({
                url : $("#pathGetInformacionPorSalida").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { idSalida: idSalida },
                successCallback: function(data){
                    $("#dependenciasSelecccionSalidaDIV").show();
                    var optionEstacionOrigen = data.optionEstacionOrigen;
                    var optionEstacionDestino = data.optionEstacionDestino;
                    var optionsEstacionesIntermedias = data.optionsEstacionesIntermedias;
                    if( data.optionEstacionOrigen && data.optionsEstacionesIntermedias){
                        var results = optionEstacionOrigen.concat(optionsEstacionesIntermedias);
                        if(core.isMovil()){
                            $('#estacionSubeEnAux').find('option').remove();
                            $.each(results, function (i, item) {
                                $('#estacionSubeEnAux').append($('<option>', { 
                                    value: item.id,
                                    text : item.text 
                                }));
                            });
                            $('#estacionSubeEnAux').val(optionEstacionOrigen[0].id);
                        }else{
                            $("#crear_boleto_autorizacion_cortesia_command_estacionSubeEn").select2({
                                data: { results: results }
                            });
                            $("#crear_boleto_autorizacion_cortesia_command_estacionSubeEn").select2('val', optionEstacionOrigen[0].id);
                        }
                    }
                    if( data.optionEstacionDestino && data.optionsEstacionesIntermedias){
                        var results = optionEstacionDestino.concat(optionsEstacionesIntermedias); 
                        if(core.isMovil()){
                            $('#estacionBajaEnAux').find('option').remove();
                            $.each(results, function (i, item) {
                                $('#estacionBajaEnAux').append($('<option>', { 
                                    value: item.id,
                                    text : item.text 
                                }));
                            });
                            $('#estacionBajaEnAux').val(optionEstacionDestino[0].id);
                        }else{
                            $("#crear_boleto_autorizacion_cortesia_command_estacionBajaEn").select2({
                                data: { results: results }
                            });
                            $("#crear_boleto_autorizacion_cortesia_command_estacionBajaEn").select2('val', optionEstacionDestino[0].id);
                        }
                    }
                    if( data.optionListaAsientos ){
                        $("#listaAsientosHidden").val(JSON.stringify(data.optionListaAsientos));
                    }
                    if( data.optionListaSenales ){
                        $("#listaSenalesHidden").val(JSON.stringify(data.optionListaSenales));
                    }
                    if( data.optionBoletos ){
                        $("#listaBoletosHidden").val(JSON.stringify(data.optionBoletos));
                    }
                    if( data.optionReservaciones ){
                        $("#listaReservacionesHidden").val(JSON.stringify(data.optionReservaciones));
                    }
                    crearBoletoAutorizacionCortesia.mostrarIconos();
                }
             });
        }
    },
    
    clearItemGridBoleto: function() {
        $("#crear_boleto_autorizacion_cortesia_command_listaBoleto").val(JSON.stringify([]));
    },
    
    buscarBoletoReservacionPorAsiento : function(numeroX, lista) {
        var item = null;
        $.each(lista, function() {   
           if(this.numero === numeroX){
                item = this;
                return;
           }
        });
        return item;
    },
    
    buscarEstadoAsientoItem : function(item) {      
        if(item.tipo === "R"){
            return "reservado";
        }else if(item.tipo === "B"){
            if(item.tipoDocumento === 1 || item.tipoDocumento === 2 || item.tipoDocumento === 4 || item.tipoDocumento === 5 || item.tipoDocumento === 6 || item.tipoDocumento === 7 || item.tipoDocumento === 8)
            {
                return "vendido";
            }
            else if(item.tipoDocumento === 3){
                return "cortesia";
            }else{
                throw new Error("No se pudo determinar el tipo de documento del boleto con id:" + item.id + ", y numero:" + item.numero);
            }
        }else{
            throw new Error("No se pudo determinar el tipo del asiento con id:" + item.id + ", y numero:" + item.numero);
        }
    },
    
    mostrarIconos : function() {
        
        $(".item").remove(); //Elimino todos los item para generarlos nuevamente con los for.
        
        var listaBoletos = $("#listaBoletosHidden").val();
        if(listaBoletos){ listaBoletos = JSON.parse(listaBoletos); }
        else{ listaBoletos = []; }
        var listaReservaciones = $("#listaReservacionesHidden").val();
        if(listaReservaciones){ listaReservaciones = JSON.parse(listaReservaciones); }
        else{ listaReservaciones = []; }
        var all = listaBoletos.concat(listaReservaciones);

        $("#nav2").addClass("hidden");
        var asientosPosicionados = [];
        var listaAsientos = $("#listaAsientosHidden").val();        
        if(listaAsientos){ listaAsientos = JSON.parse(listaAsientos); }
        else{ listaAsientos = []; }
        $("#totalSpan").text(listaAsientos.length);
        jQuery.each(listaAsientos, function() {                                
            var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            if(clase === "1") { clase = "claseA"; }
            else if(clase === "2") { clase = "claseB"; }
            else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
            asientosPosicionados[this.numero] = this;
            var estado = "libre";
            var item0 = crearBoletoAutorizacionCortesia.buscarBoletoReservacionPorAsiento(this.numero, all);
            if(item0 !== null){
                estado = crearBoletoAutorizacionCortesia.buscarEstadoAsientoItem(item0);
            }
            var item = $(".icono." + clase + "." + estado).clone();
            item.removeClass("ui-draggable icono");
            item.addClass("item"); 
            var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2 
            if(eval(nivel2)) { 
                $("#nav2").removeClass("hidden");
                $(".nivel2").append(item); 
            }
            else { $(".nivel1").append(item); }            
            item.css("left" , core.ajustarPosicion(this.coordenadaX));
            item.css("top" , core.ajustarPosicion(this.coordenadaY));
            this.jsId = core.uniqId();
            item.attr('jsId', this.jsId);
            var numero = this.numero;
            item.find(".detalle").text(this.numero);
            item.data("numero", this.numero);
            item.data("id", this.id); //Id del asiento
            if(item0 !== null){
                item.data("tipo", item0.tipo);
                if(item0.tipo === "R"){
                    item.data("idReservacion", item0.id); //Id de la reservacion
                    item.data("idClinteReservacion", item0.clinteId); //Id del cliente de la reservacion
                }
            }
            item.css('cursor', 'hand');
        });
        $("#listaAsientosHidden").val(JSON.stringify(listaAsientos));
        
        $("#pendientes").addClass("hidden");
        $("#utilizadoSpan").text(all.length);
        jQuery.each(all, function() { 
            var asiento = asientosPosicionados[this.numero];
            if(!asiento){
                $("#pendientes").removeClass("hidden");
                var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
                if(clase === "1") { clase = "claseA"; }
                else if(clase === "2") { clase = "claseB"; }
                else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
                var estado = crearBoletoAutorizacionCortesia.buscarEstadoAsientoItem(this);
                var item = $(".icono." + clase + "." + estado).clone();
                item.removeClass("ui-draggable icono");
                item.addClass("item2");            
                $(".pendientes").append(item);
                item.css("left" , core.ajustarPosicion(this.coordenadaX));
                item.css("top" , core.ajustarPosicion(this.coordenadaY));
                this.jsId = core.uniqId();
                item.attr('jsId', this.jsId);
                var numero = this.numero;
                item.find(".detalle").text(this.numero);
                item.data("numero", this.numero);
                //item.data("id", this.id); //El id seria de la reservacion o boleto, por eso esta mal, aqui no existe una asiento valido.
                item.data("tipo", this.tipo);
                if(this.tipo === "R"){
                    item.data("idReservacion", this.id); //Id de la reservacion
                    item.data("idClinteReservacion", this.clinteId); //Id del cliente de la reservacion
                }
            }
        });
        
        $(".containment .active").removeClass("active");
        $("#tab1").addClass("active");
        $("#nav1").addClass("active");
        
        var listaSenales = $("#listaSenalesHidden").val();
        if(listaSenales){ listaSenales = JSON.parse(listaSenales); }
        else{ listaSenales = []; }
        jQuery.each(listaSenales, function() {                                
            var tipo = this.tipo; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            if(tipo === "1") { tipo = "salida"; }
            else if(tipo === "2") { tipo = "chofer"; }
            else { throw new Error("El item debe tener uno de los id de tipo siguientes: 1 o 2.");  }
            var item = $(".icono." + tipo).clone();   
            item.removeClass("ui-draggable icono");
            item.addClass("item"); 
            var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2 
            if(eval(nivel2)) { $(".nivel2").append(item); }
            else { $(".nivel1").append(item); }            
            item.css("left" , core.ajustarPosicion(this.coordenadaX));
            item.css("top" , core.ajustarPosicion(this.coordenadaY));
            var id = this.id; 
            this.jsId = core.uniqId();
            item.attr('jsId', this.jsId);
        });
        $("#listaSenalesHidden").val(JSON.stringify(listaSenales));
        
        $('.asiento .showInfo').bind("click", crearBoletoAutorizacionCortesia.info);
        //Solo se conectan los asietos que esten en un nivel valido, los que estan en pendientes hay que reasignarlos.
        $('.nivel1 .asiento img').bind("click", crearBoletoAutorizacionCortesia.clickAsiento);
        $('.nivel2 .asiento img').bind("click", crearBoletoAutorizacionCortesia.clickAsiento);

        
    },
    
    info : function(e) {
        e.preventDefault();
        e.stopPropagation();
        var item = $(this);
        var numero = item.parent().data("numero");
        if(numero === null || $.trim(numero) === ""){
            throw new Error("No se pudo determinar el numero de asiento.");
        }
        var idSalida = null;
        if(core.isMovil()){
            idSalida = $('#salidaAux').val();
        }else{
            idSalida = $("#crear_boleto_autorizacion_cortesia_command_salida").prop("value");
        } 
        if(idSalida === null || $.trim(idSalida) === ""){
             throw new Error("No se pudo determinar el id de la salida.");
        }
        
        core.request({
            url : $("#pathInfoAsientoSalida").attr('value'),
            method: 'GET', //Obligatorio
            extraParams: {
                idSalida: idSalida,
                numeroAsiento:numero
            }, 
            dataType: "json",
            async:true,
            successCallback: function(success){
                console.debug(success);
                if(success.items){
                    var str = "";
                    var items = success.items;
                    $.each(items, function(){
                        str += "<label><b>ID: " + this.id + "</b></label>";
                        str += "<label>Asiento: " + this.numeroAsiento + "</label>";
                        str += "<label>Tipo: " + this.tipo + "</label>";
                        str += "<label>Creado: " + this.fechaCreacion + "</label>";
                        str += "<label>Estación Venta: " + this.estacionVenta + "</label>";
                        str += "<label>Estación Origen: " + this.origen + "</label>";
                        str += "<label>Estación Destino: " + this.destino + "</label>";
                        str += "<label>Cliente: " + this.cliente + "</label>";
                        if(this.cortesia === true){
                            str += "<label>Usuario: " + this.autorizadoPor + "</label>";
                            str += "<label>Motivo: " + this.motivoCortesia + "</label>";
                            str += "<label><a class='btn cancelarCortesiaPopUp' data-value='"+this.id+"'>CANCELAR CORTESIA</a></label>";
                        }
                    });
                    if(str === ""){
                        str += "<label>Asiento libre</label>";
                    }
                    str += "<BR><BR>";
                    var dialog = core.showMessageDialog({
                        title : "Consultar Boleto",
                        showCloseHandle : true,
                        text: str,
                        uniqid : true,
                        buttons: {
                           Cancelar: function() {
                               this.dialog2("close");
                           }				    
                       }
                    });
                    $(".btn.cancelarCortesiaPopUp").click(function (){
                        var id = $(this).data("value");
                        core.request({
                            url : $("#pathCancelarBoleto").attr("value"),
                            type: "POST",
                            dataType: "html",
                            async: false,
                            extraParams : {
                                id : id
                            },
                            successCallback: function(responseText){
                                if(!core.procesarRespuestaServidor(responseText)){
                                    dialog.dialog2("close");
                                    crearBoletoAutorizacionCortesia.checkSelectedSalida();
                                }
                            }
                       });
                    });
                }
            }
        });
    },
    
    clickAsiento : function(e) {
        e.preventDefault();
        e.stopPropagation();
        var figure = $(this).parent();
        var numero = figure.data("numero");
        if(figure.hasClass("reservado")){
        }else if(figure.hasClass("cortesia")){
            alert("El asiento con el número " + numero + " es una cortesía.");
            return;
        }else if(figure.hasClass("vendido")){
            alert("El asiento con el número " + numero + " ya está vendido.");
            return;
        }
        var listaBoleto = $("#crear_boleto_autorizacion_cortesia_command_listaBoleto").val();
        if(listaBoleto){ listaBoleto = JSON.parse(listaBoleto); }
        else{ listaBoleto = []; }
        var item = crearBoletoAutorizacionCortesia.findAsiento(listaBoleto, numero);
        if(item === null){
           item = {
                numero : figure.data("numero"),
                id : figure.data("id"),
                idCliente : '',
                idReservacion : figure.data("idReservacion") !== undefined ? figure.data("idReservacion") : "",
                idClinteReservacion : ''
           };
           listaBoleto.push(item);
           figure.addClass("selected");
        }else{
           listaBoleto = core.removeItemArray(listaBoleto, item);
           figure.removeClass("selected");
        }
        $("#crear_boleto_autorizacion_cortesia_command_listaBoleto").val(JSON.stringify(listaBoleto));
    },
    
    findAsiento : function(listaClienteBoleto, numero) {
        var result = null;
        numero = $.trim(numero);
        $.each(listaClienteBoleto, function() {  
           if($.trim(this.numero) === numero){
               result = this;
               return;
           }
        });
        return result;
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var autorizacionCortesiaForm = $("#autorizacionCortesiaForm");
        if(core.customValidateForm(autorizacionCortesiaForm) === true && crearBoletoAutorizacionCortesia.customValidate() === true){
            if(core.isMovil()){
                $("#crear_boleto_autorizacion_cortesia_command_salida").val($("#salidaAux").val());
                $("#crear_boleto_autorizacion_cortesia_command_estacionSubeEn").val($("#estacionSubeEnAux").val());
                $("#crear_boleto_autorizacion_cortesia_command_estacionBajaEn").val($("#estacionBajaEnAux").val());
            }
            $("#crear_boleto_autorizacion_cortesia_command_movil").prop("value", core.isMovil());
            $("#crear_boleto_autorizacion_cortesia_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
            $(autorizacionCortesiaForm).ajaxSubmit({
                target: autorizacionCortesiaForm.attr('action'),
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
                        alert("Operación realizada satisfactoriamente." + info, function() {
                            if(core.isMovil()){
                                core.getPageForMenu(autorizacionCortesiaForm.attr('action'));
                            }else{
                                crearBoletoAutorizacionCortesia.imprimirVoucher(responseText, function (){
                                    core.getPageForMenu(autorizacionCortesiaForm.attr('action'));
                                });
                            }
                        });
                    }
                }
           });
        }
    },
    
    customValidate: function(dialog) {
        
        var listaBoleto = $("#crear_boleto_autorizacion_cortesia_command_listaBoleto").val();
        if(listaBoleto){ listaBoleto = JSON.parse(listaBoleto); }
        else{ listaBoleto = []; }
        var cantidadBoletos = listaBoleto.length;
        if( cantidadBoletos <= 0){
            core.hiddenDialog2(dialog);
            alert("Debe definir al menos un boleto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    imprimirVoucher: function(responseText, successCallback) {
        var data = core.getValueFromResponse(responseText, 'data');
        frondend.printVoucherBoleto($("#pathPrintVoucherBoleto").attr("value"), { ids : data }, function (){
            if(successCallback && $.isFunction(successCallback)){
                successCallback(responseText);
            }
        });
       
    }
};
