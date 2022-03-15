reasignarBoleto = {
    
    procesandoDatos : false,
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {

         $("#reasignar_boleto_command_identificadorWeb").val(core.uniqIdCompuesto());
        
         var pathlistarclientespaginando = $("#reasignar_boleto_command_clienteDocumento").data("pathlistarclientespaginando");
         $("#reasignar_boleto_command_clienteDocumento").select2({
            minimumInputLength: 1,
            allowClear: true,
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
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax(pathlistarclientespaginando, {    
                        data: {
                            id: id
                        },
                        type: "GET",
                        dataType: "json"
                    }).done(function(data) { 
                        $(element).select2('data', data.options[0]);
                    });
                }
            }
        });
        
        $('#reasignar_boleto_command_fechaSalida').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-1d",
            endDate: "+2m",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#reasignar_boleto_command_fechaSalida').datepicker("setDate", new Date());
        
        $("#reasignar_boleto_command_estacionOrigen").select2({
            allowClear: true
        });
        $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").select2({
            allowClear: true
        });
        
        $("#salidaGrid").flexigrid({
            url: $("#salidaGrid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: reasignarBoleto.getQueryString(),
            rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
            colModel : [
                    {display: 'ID', name : 'id', width : 70, sortable : true, align: 'center', hide: true},
                    {display: 'Fecha y Hora', name : 'fecha', width : 120, sortable : false, align: 'center'},
                    {display: 'Origen', name : 'origen', width : 120, sortable : false, align: 'left'},
                    {display: 'Destino', name : 'destino', width : 120, sortable : false, align: 'left'},
                    {display: 'Empresa', name : 'empresa', width : 80, sortable : false, align: 'left'},
//                    {display: 'Tipo Bus', name : 'tipoBus', width : 80, sortable : false, align: 'left'},
//                    {display: 'Clase de Bus', name : 'claseBus', width : 80, sortable : false, align: 'left'},
                    {display: 'Itinerario', name : 'itinerario', width : 130, sortable : false, align: 'left'},
                    {display: 'Bus', name : 'bus', width : 130, sortable : false, align: 'left'},
                    {display: 'Piloto', name : 'piloto', width : 130, sortable : false, align: 'left', hide: true},
                    {display: 'Estado', name : 'estado', width : 70, sortable : false, align: 'center'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            showTableToggleBtn: false,
            height: 250,
            onSuccess : function() {
                $("#salidaGrid tr").on('click', reasignarBoleto.checkSelectedSalida);
                reasignarBoleto.checkSelectedSalida();
            }
        });
        
        $("#reasignar_boleto_command_estacionSubeEn").select2({
            allowClear: true,
            data: []
        });
        
        $("#reasignar_boleto_command_estacionBajaEn").select2({
            allowClear: true,
            data: []
        });
        
        $("#reasignar_boleto_command_tipoPagoVirtual").select2({
            allowClear: true
        });
        
        $("#reasignar_boleto_command_monedaPagoVirtual").select2({
            allowClear: true
        });
        
        $("#reasignar_boleto_command_serieFacturaVirtual").select2({
            allowClear: false,
            data: []
        });
        $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2({
            allowClear: false,
            data: []
        });
        
        var precioCalculadoMonedaBase = $("#reasignar_boleto_command_precioCalculadoMonedaBase").val();
        if(precioCalculadoMonedaBase !== "" && precioCalculadoMonedaBase !== 0 && precioCalculadoMonedaBase !== "0"){
            $("#importeFacturado").val("GTQ " + precioCalculadoMonedaBase);
        }
        
        reasignarBoleto.checkSelectedSalida();
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
                var element = $("#reasignar_boleto_command_clienteDocumento");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#reasignar_boleto_command_clienteDocumento').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#updateCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function() {
//                console.debug("Actulizando datos del combo...."); 
                var element = $("#reasignar_boleto_command_clienteDocumento");
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
                                $('#reasignar_boleto_command_clienteDocumento').select2('data', data.options[0]); 
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
                    var element = $("#reasignar_boleto_command_clienteDocumento");
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
                                $('#reasignar_boleto_command_clienteDocumento').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         
         $("#aceptar").click(reasignarBoleto.clickAceptar);
         
         $("#reasignar_boleto_command_fechaSalida").on("change", reasignarBoleto.changeFiltersSalida);
         $("#reasignar_boleto_command_estacionOrigen").on("change", reasignarBoleto.changeFiltersSalida);
         
         $("#reasignar_boleto_command_tipoPagoVirtual").on("change", reasignarBoleto.changeTipoPago);
         $("#reasignar_boleto_command_monedaPagoVirtual").on("change", reasignarBoleto.changeMonedaPago);
         $("#reasignar_boleto_command_efectivoVirtual").on("change", reasignarBoleto.changeEfectivo);
         
         $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").on("change", reasignarBoleto.changeEstacionFacturacionEspecial);
         $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").on("change", reasignarBoleto.changeSerieFacturacionEspecial);
         $("#facturaEspecialVoucher").bind("click", reasignarBoleto.changeSerieFacturacionEspecial);
         
         $("#reasignar_boleto_command_serieFacturaVirtual").on("change", function (){
             console.debug("serieFacturaVirtual-init");
             var idSerieFactura = $("#reasignar_boleto_command_serieFacturaVirtual").select2('val');
             var idEmpresa = $("#reasignar_boleto_command_serieFacturaVirtual").select2('data').idEmpresa;
             sessionStorage.setItem("sist_last_id_serie_factura_" + idEmpresa, idSerieFactura);
         });
         
         $('#facturaVoucher').bind("click", reasignarBoleto.checkFacturaVoucher);
         $('#facturaEspecialVoucher').bind("click", reasignarBoleto.checkFacturaEspecialVoucher);
    },
    
    checkFacturaVoucher : function (){
        var checked = $("#facturaVoucher").prop("checked");
        if(checked){
            $(".voucherDIV").show();
        }else{
            $(".voucherDIV").hide();
        }
    },
    
    checkFacturaEspecialVoucher : function (){
        var checked = $("#facturaEspecialVoucher").prop("checked");
        if(checked){
            $(".voucherDIV").show();
        }else{
            $(".voucherDIV").hide();
        }
    },
    
    checkSelectedSalida : function() {
//        console.log("changeSelectedSalida-init");
        reasignarBoleto.clearItemGridBoleto();
        var selected = core.getSelectedItemId("#salidaGrid");
        $("#reasignar_boleto_command_salida").prop("value", selected);
        if(selected === null){
            $("#reasignar_boleto_command_estacionSubeEn").select2({ data: [] });
            $("#reasignar_boleto_command_estacionBajaEn").select2({ data: [] });
            $("#reasignar_boleto_command_serieFacturaVirtual").select2({ data: [] });
            $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2({ data: [] });
            $("#listaAsientosHidden").val(JSON.stringify([]));
            $("#listaSenalesHidden").val(JSON.stringify([]));
            $("#listaBoletosHidden").val(JSON.stringify([]));
            $("#listaReservacionesHidden").val(JSON.stringify([]));
            $("#dependenciasSelecccionSalidaGrid").hide();
            reasignarBoleto.mostrarIconos();
        }else{
            core.request({
                url : $("#pathGetInformacionPorSalida").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { idSalida: selected, showTime : true },
                successCallback: function(data){
//                    console.debug("Actulizando datos del combo...data");
//                    console.debug(data);
                    $("#dependenciasSelecccionSalidaGrid").show();
                    var optionEstacionOrigen = data.optionEstacionOrigen;
                    var optionEstacionDestino = data.optionEstacionDestino;
                    var optionsEstacionesIntermedias = data.optionsEstacionesIntermedias;
                    
                    $.each(optionEstacionOrigen, function(){
                        this.text = (this.time !== "" ? this.time + " " : "") + this.text;
                    });
                    $.each(optionEstacionDestino, function(){
                        this.text = (this.time !== "" ? this.time + " " : "") + this.text;
                    });
                    $.each(optionsEstacionesIntermedias, function(){
                        this.text = (this.time !== "" ? this.time + " " : "") + this.text;
                    });
                    
                    if( data.optionEstacionOrigen && data.optionsEstacionesIntermedias){
                        var results = optionEstacionOrigen.concat(optionsEstacionesIntermedias);
                        $("#reasignar_boleto_command_estacionSubeEn").select2({
                            allowClear: true,
                            data: { results: results }
                        });
                        $("#reasignar_boleto_command_estacionSubeEn").select2('val', optionEstacionOrigen[0].id);
                    }
                    if( data.optionEstacionDestino && data.optionsEstacionesIntermedias){
                        var results = optionEstacionDestino.concat(optionsEstacionesIntermedias);
                        $("#reasignar_boleto_command_estacionBajaEn").select2({
                            allowClear: true,
                            data: { results: results }
                        });
                        $("#reasignar_boleto_command_estacionBajaEn").select2('val', optionEstacionDestino[0].id);
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
                    
                    reasignarBoleto.mostrarIconos();
                    
                    var optionSeriesFacturas = data.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#reasignar_boleto_command_serieFacturaVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        
                        var cantidad = optionSeriesFacturas.length;
                        $("#reasignar_boleto_command_serieFacturaVirtual").data("sizeItems", cantidad);
                        if(cantidad > 0){
                            var idEmpresa = optionSeriesFacturas[0].idEmpresa;
                            var idSerieFactura = sessionStorage.getItem("sist_last_id_serie_factura_" + idEmpresa);
                            if(idSerieFactura){
                                $("#reasignar_boleto_command_serieFacturaVirtual").select2('val', idSerieFactura); 
                            }else if(optionSeriesFacturas[0]){
                                $("#reasignar_boleto_command_serieFacturaVirtual").select2('val', optionSeriesFacturas[0].id); 
                            }
                        }
                    }
                    
                    //Se borra pq se carga cuando se seleccione la estacion.
                    $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2({ data: [] });
                }
             });
        }
        
    },
    
    changeFiltersSalida : function() {
        $("#salidaGrid").flexOptions({
            newp: 1, 
            query: reasignarBoleto.getQueryString()
        }).flexReload(); 
    },
     
    getQueryString : function() {
        return $('.filterSalida').fieldSerialize();
    },
   
    buscarBoletoReservacionPorAsiento : function(numeroX, lista) {
//        console.debug("buscarEstadoAsiento asiento:" + numero);
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
//        console.debug("buscarEstadoAsientoItem-init");
//        console.debug(item);      
        var idBoletoOriginal = $("#reasignar_boleto_command_boletoOriginal").val();
        if(idBoletoOriginal === item.id){
            return "libre";
        }else if(item.tipo === "R"){
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
//        console.debug("mostrarIconos...Item..");
        
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
        jQuery.each(listaAsientos, function() {                                
            var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            if(clase === "1") { clase = "claseA"; }
            else if(clase === "2") { clase = "claseB"; }
            else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
            asientosPosicionados[this.numero] = this;
            var estado = "libre";
            var item0 = reasignarBoleto.buscarBoletoReservacionPorAsiento(this.numero, all);
            if(item0 !== null){
                estado = reasignarBoleto.buscarEstadoAsientoItem(item0);
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
        
//        console.debug(asientosPosicionados);
        $("#pendientes").addClass("hidden");
        jQuery.each(all, function() { 
            var asiento = asientosPosicionados[this.numero];
            if(!asiento){
//                console.log("El item no existe...");
//                console.debug(this);
                $("#pendientes").removeClass("hidden");
                var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
                if(clase === "1") { clase = "claseA"; }
                else if(clase === "2") { clase = "claseB"; }
                else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
                var estado = reasignarBoleto.buscarEstadoAsientoItem(this);
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
        
        $('.asiento .showInfo').bind("click", reasignarBoleto.info);
        //Solo se conectan los asietos que esten en un nivel valido, los que estan en pendientes hay que reasignarlos.
        $('.nivel1 .asiento img').bind("click", reasignarBoleto.clickAsiento);
        $('.nivel2 .asiento img').bind("click", reasignarBoleto.clickAsiento);
    },
    
    info : function(e) {
//        console.log("showInfo-init");
        e.preventDefault();
        e.stopPropagation();
        var item = $(this);
        var numero = item.parent().data("numero");
//        console.debug(numero);
        if(numero === null || $.trim(numero) === ""){
            throw new Error("No se pudo determinar el numero de asiento.");
        }
        var idSalida = core.getSelectedItemId("#salidaGrid");
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
            dataType: "html",
            async:true,
            successCallback: function(success){
//                console.debug(success);
                core.showMessageDialog({
                    title: "Consultar Asiento Bus",
                    fullscreen: true,
                    compact: true,
                    text: success,
                    defaultButtonOFF: true,
                    buttons: {
			Cancelar: {
                            primary: true,
                            type: "info",
                            click: function() {
//                                console.log("Cancelar - click - init");
                                $("body").css("overflow-y", "auto");
                                this.dialog2("close");					
                            }
			}				    
                    }
                });
            }
        });
        
        
    },
    
    clickAsiento : function(e) {
//        console.log("clickAsiento-init");
        e.preventDefault();
        e.stopPropagation();
//        console.debug(e);
//        console.debug($(this));
        var figure = $(this).parent();
        var numero = figure.data("numero");
//        var reservado = false;
        if(figure.hasClass("reservado")){
//            reservado = true;
        }else if(figure.hasClass("cortesia")){
            alert("El asiento con el número " + numero + " es una cortesía.");
            return;
        }else if(figure.hasClass("vendido")){
            alert("El asiento con el número " + numero + " ya está vendido.");
            return;
        }
        var listaClienteBoleto = $("#reasignar_boleto_command_listaClienteBoleto").val();
        if(listaClienteBoleto){ listaClienteBoleto = JSON.parse(listaClienteBoleto); }
        else{ listaClienteBoleto = []; }
        // --------------- BORRANDO TODO LO PREVIO  -------------------
        $(".asiento.selected").removeClass("selected");
        $.each(listaClienteBoleto, function() {  
           if($.trim(this.numero) !== numero){
                reasignarBoleto.renderItemGridDeletedBoleto(this, listaClienteBoleto);
           }
        });
        listaClienteBoleto = [];
        // --------------- BORRANDO TODO LO PREVIO  -------------------
        newItem = {
            numero : figure.data("numero"),
            id : figure.data("id"),
            idCliente : '',
            idReservacion : figure.data("idReservacion") !== undefined ? figure.data("idReservacion") : "",
            idClinteReservacion : figure.data("idClinteReservacion") !== undefined ? figure.data("idClinteReservacion") : ""
        };
        listaClienteBoleto.push(newItem);
        figure.addClass("selected");
        reasignarBoleto.renderItemGridAddBoleto(newItem);   
       
//        console.debug(listaClienteBoleto);
        $("#reasignar_boleto_command_listaClienteBoleto").val(JSON.stringify(listaClienteBoleto));
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
    
    //Adiciona un item especifico de la tabla
    renderItemGridAddBoleto: function(item) {
        
        $("#clienteBoletoBody").find("#clienteBoletoVacioTR").hide(); //Oculto el TR vacio
        var numero = item.numero;
        var id = core.uniqId("cliente_boleto");
        var placeholder = "Seleccione el cliente boleto";
        var inputSelect = $("#reasignar_boleto_command_clienteDocumento").clone();
        inputSelect.prop("id", id);
        inputSelect.prop("name", id);
        inputSelect.data("numero", numero);
        inputSelect.prop("placeholder", placeholder);
        inputSelect.attr("placeholder", placeholder);
        inputSelect.data("placeholder", placeholder);
        inputSelect.val("");
        inputSelect.removeClass("select2-offscreen");
        var itemTR = $("<tr class='trSelect"+numero+"'><td>"+numero+"</td><td class='inputSelect'></td></tr>");
        itemTR.find(".inputSelect").append(inputSelect);
        $("#clienteBoletoBody").append(itemTR);
        var pathlistarclientespaginando = inputSelect.data("pathlistarclientespaginando");
//        console.debug(inputSelect);
        if(item.idClinteReservacion !== ""){
            inputSelect.val(item.idClinteReservacion);
        }
        inputSelect.select2({
            minimumInputLength: 1,
            allowClear: true,
            placeholder: placeholder,
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
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax(pathlistarclientespaginando, {
                        data: { id: id },
                        dataType: 'json',
                        type: "GET"
                    }).done(function(data) {
                        callback(data.options[0]); 
                    });
                }
            }
        });
        
        if(inputSelect.val() === ""){
            $(inputSelect).select2('data', $('#reasignar_boleto_command_clienteDocumento').select2('data'));
        }
    },
    
    //Elimina un item especifico de la tabla
    renderItemGridDeletedBoleto: function(item, listaClienteBoleto) {
        if(!listaClienteBoleto){
            listaClienteBoleto = $("#reasignar_boleto_command_listaClienteBoleto").val();
            if(listaClienteBoleto){ listaClienteBoleto = JSON.parse(listaClienteBoleto); }
            else{ listaClienteBoleto = []; }
        }
        
        if(listaClienteBoleto.length === 0){
            $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").remove(); //Elimino todos los tr
            $("#clienteBoletoBody").find("#clienteBoletoVacioTR").show(); //Muestro el vacio
        }else{
            $("#clienteBoletoBody").find("#clienteBoletoVacioTR").hide(); //Oculto el vacio
            var numero = item.numero;
            $("#clienteBoletoBody").find(".trSelect"+numero).remove();
        }
        
    },
    
    //Se elimina toda la informacion de boletos hasta el momento, y se muestra el tr vacio
    clearItemGridBoleto: function() {
        $("#reasignar_boleto_command_listaClienteBoleto").val(JSON.stringify([]));
        $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").remove(); //Elimino todos los tr
        $("#clienteBoletoBody").find("#clienteBoletoVacioTR").show(); //Muestro el vacio
    },
    
    imprimirFacturas: function(responseText, successCallback) {
        var info = core.getValueFromResponse(responseText, "info");
        core.showNotification({text : info});
        var autoPrint = core.getValueFromResponse(responseText, 'autoPrint');
        if(eval(autoPrint) === true){
            frondend.printFacturaInternal(responseText, function (){
                alert("Operación realizada satisfactoriamente. " + info, function() {
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }); 
            });                                        
        }else{
            alert("Operación realizada satisfactoriamente. " + info, function() {
                frondend.printFacturaInternal(responseText, successCallback);                                         
            }); 
        }
    },
    
    imprimirVoucher: function(responseText, successCallback) {
        var info = core.getValueFromResponse(responseText, "info");
        core.showNotification({text : info});
        var autoPrint = core.getValueFromResponse(responseText, 'autoPrint');
        if(eval(autoPrint) === true){
            var data = core.getValueFromResponse(responseText, 'data');
            frondend.printVoucherBoleto($("#pathPrintVoucherBoleto").attr("value"), { ids : data }, function (){
                alert("Operación realizada satisfactoriamente. " + info, function() {
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }); 
            });
        }else{
            alert("Operación realizada satisfactoriamente. " + info, function() {
                var data = core.getValueFromResponse(responseText, 'data');
                frondend.printVoucherBoleto($("#pathPrintVoucherBoleto").attr("value"), { ids : data }, successCallback);
            }); 
        }
    },
    
    syncronizarListaBoletos: function() {
        var listaClienteBoleto = $("#reasignar_boleto_command_listaClienteBoleto").val();
        if(listaClienteBoleto){ listaClienteBoleto = JSON.parse(listaClienteBoleto); }
        else{ listaClienteBoleto = []; }
        $.each(listaClienteBoleto, function(){
            this.idCliente = $(".trSelect"+this.numero).find('input[id^="cliente_boleto"]').val();
        });
//        console.debug(listaClienteBoleto);
        $("#reasignar_boleto_command_listaClienteBoleto").val(JSON.stringify(listaClienteBoleto));
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var boletoForm = $("#boletoForm");
        if(core.customValidateForm(boletoForm) === true && reasignarBoleto.customCommunValidate() === true){
//            console.debug("clickAceptar-valid-ok");
            var tipoDocumento = parseInt($("#reasignar_boleto_command_tipoDocuemento").val());
            if(tipoDocumento === 1 || tipoDocumento === 2 || tipoDocumento === 4 || tipoDocumento === 6 || tipoDocumento === 7 || tipoDocumento === 8){
//                console.debug("clickAceptar-chequear si hay que facturar");
                $("#reasignar_boleto_command_tipoPagoVirtual").val("1"); //Por defecto este calculo siempre se hace con el efectivo, que es el mas barato.
                var importeTotalMonedaBase = parseFloat(reasignarBoleto.calcularImporteTotalMonedaBaseGenerico(function (success){
                    if(success.showVoucher && eval(success.showVoucher) === true){
                        $("#voucher").removeClass("hidden");
                        $("#facturaVoucher").removeClass("hidden");
                    }else{
                        $("#voucher").addClass("hidden");
                        $("#facturaVoucher").addClass("hidden");
                    }
                }, false, { showVoucher : true }));
//                console.debug(importeTotalMonedaBase);
                if(importeTotalMonedaBase === 0){
//                    console.debug("clickAceptar-no se factura.");
                    $("#reasignar_boleto_command_movil").prop("value", core.isMovil());
                    $("#reasignar_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                    var tipoDocumentoAux = "";
                    if(tipoDocumento === 1 || tipoDocumento === 2 || tipoDocumento === 4) tipoDocumentoAux = 1;
                    if(tipoDocumento === 6 || tipoDocumento === 7) tipoDocumentoAux = 6;
                    if(tipoDocumento === 8) tipoDocumentoAux = 8;
                    $("#reasignar_boleto_command_tipoDocuemento").prop("value", tipoDocumentoAux);
                    $("#reasignar_boleto_command_monedaPago").prop("value", "1");
                    $("#reasignar_boleto_command_totalNeto").prop("value", ""); //Garantizar que este vacio, pq sino se genera operacion de caja
                    reasignarBoleto.syncronizarListaBoletos();
                    $(boletoForm).ajaxSubmit({
                        target: boletoForm.attr('action'),
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
                               reasignarBoleto.imprimirVoucher(responseText, function (){
                                    core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                               });
                            }
                        }
                    });
                }else if (importeTotalMonedaBase !== -1){
//                  console.debug("clickAceptar-se facturan:" + importeTotalMonedaBase);
                    if(tipoDocumento === 8){
                        alert("No se puede reasignar un voucher por un precio mayor al pagado en el portal de internet. Existe una diferencia de GTQ " + importeTotalMonedaBase + ".");
                        return;
                    }

                    var info = "Se debe facturar una diferencia de GTQ " + importeTotalMonedaBase + ".";
                    core.showMessageDialog({
                        text: info,
                        buttons: {
                            "Facturar": {
                                click: function() {
                                    this.dialog2("close");
                                    reasignarBoleto.iniciarProcesoFacturar(importeTotalMonedaBase);
                                }, 
                                primary: true,
                                type: "info"
                            }, 
                            "Facturar Otra Estación" : {
                                click: function() {
                                    this.dialog2("close");
                                    reasignarBoleto.iniciarProcesoFacturarEspecial(importeTotalMonedaBase);
                                }, 
                                primary: true,
                                type: "info"
                            },
                            Cancelar: function() {
                                this.dialog2("close");					
                            }				    
                        }
                    });
                }
            }else if(tipoDocumento === 5){
                $("#reasignar_boleto_command_totalNetoAgenciaVirtual").val("");
                $("#reasignar_boleto_command_tipoPagoVirtual").val("1"); //Por defecto este calculo siempre se hace con el efectivo, que es el mas barato.
                reasignarBoleto.calcularImporteTotalMonedaBaseGenerico(function (success){
                   console.debug(success);
                   var importeTotal = parseFloat(success.totalSec);
                   if(importeTotal === 0){
                        $("#reasignar_boleto_command_movil").prop("value", core.isMovil());
                        $("#reasignar_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                        $("#reasignar_boleto_command_referenciaExternaAgencia").prop("value", "");
                        $("#reasignar_boleto_command_totalNetoAgencia").prop("value", 0);
                        $("#reasignar_boleto_command_tipoDocuemento").prop("value", 5); //Agencia
                        reasignarBoleto.syncronizarListaBoletos();
                        $(boletoForm).ajaxSubmit({
                            target: boletoForm.attr('action'),
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
                                   reasignarBoleto.imprimirVoucher(responseText, function (){
                                        core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                                   });
                                }
                            }
                        });
                    }else if(importeTotal !== -1){
                        console.log("x2");
                        reasignarBoleto.iniciarProcesoVoucherAgencia(importeTotal);
                    }
                   
                }, false, { monedaSec : $("#reasignar_boleto_command_monedaAgencia").val() });
                
            }else if(tipoDocumento === 3){
//                console.debug("clickAceptar-cortesia");
                reasignarBoleto.syncronizarListaBoletos();
                $("#reasignar_boleto_command_tipoDocuemento").prop("value", 3); 
                $("#reasignar_boleto_command_movil").prop("value", core.isMovil());
                $("#reasignar_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                $(boletoForm).ajaxSubmit({
                    target: boletoForm.attr('action'),
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
                            reasignarBoleto.imprimirVoucher(responseText, function (){
                                core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                            });
                        }
                    }
                });
            }else{
                throw new Error("Tipo de documento no valido. Valor:" +tipoDocumento + "." );
            }
        }
    },
    
    iniciarProcesoVoucherAgencia: function(importeTotal) {
            $("#reasignar_boleto_command_totalNetoAgenciaVirtual").val(importeTotal);  
            core.showMessageDialog({
                 title : "Registrar Voucher",
                 selector: $("#aceptarDIV"),
                 removeOnClose : false,
                 uniqid : false,
                 buttons: {
                    Aceptar: {
                        click: function() {
                            if(reasignarBoleto.procesandoDatos === false){
                                reasignarBoleto.procesandoDatos = true;
                                var dialogActual = this;
                                var boletoForm = $("#boletoForm");
                                if(core.customValidateForm(boletoForm) === true && reasignarBoleto.customPostValidateAceptar(this) === true){
                                    $("#reasignar_boleto_command_movil").prop("value", core.isMovil());
                                    $("#reasignar_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                    $("#reasignar_boleto_command_referenciaExternaAgencia").prop("value", $("#reasignar_boleto_command_referenciaExternaAgenciaVirtual").prop("value"));
                                    $("#reasignar_boleto_command_totalNetoAgencia").prop("value", $("#reasignar_boleto_command_totalNetoAgenciaVirtual").prop("value"));
                                    $("#reasignar_boleto_command_tipoDocuemento").prop("value", 5); //Agencia
                                    reasignarBoleto.syncronizarListaBoletos();
                                    $(boletoForm).ajaxSubmit({
                                        target: boletoForm.attr('action'),
                                        type : "POST",
                                        dataType: "html",
                                        cache : false,
                                        async:false,
                                        beforeSubmit: function() { 
                                            core.showLoading({showLoading:true});
                                        },
                                        error: function() {
                                            core.hideLoading({showLoading:true});
                                            reasignarBoleto.procesandoDatos = false;
                                        },
                                        success: function(responseText) { 
                                            core.hideLoading({showLoading:true});
                                            if(!core.procesarRespuestaServidor(responseText)){
                                                dialogActual.dialog2("close");
                                                reasignarBoleto.imprimirVoucher(responseText, function (){
                                                   core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                                                });
                                            }
                                            reasignarBoleto.procesandoDatos = false;
                                       }
                                    });
                                }else{
                                    reasignarBoleto.procesandoDatos = false;
                                }
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
    },
    
    customPostValidateAceptar: function(dialog) {
        
        var totalNeto = $("#reasignar_boleto_command_totalNetoAgenciaVirtual").prop("value");
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total neto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }   
        
        return true;
    },
    
    customPreValidateFactura: function(dialog) {
        
        var cantidadMonedas = $("#reasignar_boleto_command_monedaPagoVirtual option").length;
        if( cantidadMonedas <= 0 ){
            core.hiddenDialog2(dialog);
            alert("Debe abrir una caja para facturar.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var sizeItems = $("#reasignar_boleto_command_serieFacturaVirtual").data("sizeItems");
        if(sizeItems <= 0){
            core.hiddenDialog2(dialog);
            alert("No existen series de factura activa en la estación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    iniciarProcesoFacturar: function(importeTotalMonedaBase) {
        $("#reasignar_boleto_command_totalNetoVirtual").val("");
        $("#reasignar_boleto_command_tasaVirtual").val("");
        $("#reasignar_boleto_command_totalPagoVirtual").val("");
        $("#reasignar_boleto_command_vueltoVirtual").val("");
        $("#reasignar_boleto_command_efectivoVirtual").val("");
        $("#reasignar_boleto_command_totalNetoVirtual").val(importeTotalMonedaBase);
        if(reasignarBoleto.customPreValidateFactura() === true){
            $("#facturaVoucher").prop("checked", true);
            reasignarBoleto.checkFacturaVoucher();
            core.showMessageDialog({
                title : "Registrar Facturar",
                selector: $("#facturaDIV"),
                removeOnClose : false,
                uniqid : false,
                buttons: {
                    Aceptar: {
                        click: function() {
                                var dialogActual = this;
    //                            console.debug("Aceptar-click...");
                                var boletoForm = $("#boletoForm");
                                if(core.customValidateForm(boletoForm) === true && reasignarBoleto.customPostValidateFactura(this) === true){
                                    $("#reasignar_boleto_command_movil").prop("value", core.isMovil());
                                    $("#reasignar_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                    $("#reasignar_boleto_command_referenciaExterna").prop("value", $("#reasignar_boleto_command_referenciaExternaVirtual").prop("value"));
                                    $("#reasignar_boleto_command_tipoPago").prop("value", $("#reasignar_boleto_command_tipoPagoVirtual").prop("value"));
                                    $("#reasignar_boleto_command_autorizacionTarjeta").prop("value", $("#reasignar_boleto_command_autorizacionTarjetaVirtual").prop("value"));
                                    $("#reasignar_boleto_command_totalNeto").prop("value", $("#reasignar_boleto_command_totalNetoVirtual").prop("value"));
                                    $("#reasignar_boleto_command_monedaPago").prop("value", $("#reasignar_boleto_command_monedaPagoVirtual").prop("value"));
                                    $("#reasignar_boleto_command_tasa").prop("value", $("#reasignar_boleto_command_tasaVirtual").prop("value"));
                                    $("#reasignar_boleto_command_totalPago").prop("value", $("#reasignar_boleto_command_totalPagoVirtual").prop("value"));
                                    $("#reasignar_boleto_command_efectivo").prop("value", $("#reasignar_boleto_command_efectivoVirtual").prop("value"));
                                    $("#reasignar_boleto_command_vuelto").prop("value", $("#reasignar_boleto_command_vueltoVirtual").prop("value"));
                                    $("#reasignar_boleto_command_serieFactura").prop("value", $("#reasignar_boleto_command_serieFacturaVirtual").prop("value"));
                                    if($("#facturaVoucher").prop("checked") === true){
                                        $("#reasignar_boleto_command_tipoDocuemento").prop("value", 1); //Factura
                                    }else{
                                        $("#reasignar_boleto_command_tipoDocuemento").prop("value", 6); //Voucher
                                    }
                                    reasignarBoleto.syncronizarListaBoletos();
                                    $(boletoForm).ajaxSubmit({
                                        target: boletoForm.attr('action'),
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
                                            core.hideLoading({showLoading:true});
                                            if(!core.procesarRespuestaServidor(responseText)){
                                                dialogActual.dialog2("close");
                                                if($("#facturaVoucher").prop("checked") === true){
                                                    reasignarBoleto.imprimirFacturas(responseText, function (){
                                                        core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                                                    });
                                                }else{
                                                    reasignarBoleto.imprimirVoucher(responseText, function (){
                                                        core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                                                    });
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
            reasignarBoleto.checkBloque2();   //Se chequea los div en cascada
        }
    },
    
    customPreValidateFacturaEspecial: function(dialog) {
       
        return true;
    },
    
    customPostValidateFacturaEspecial: function(dialog) {
        
        var totalNeto = $("#totalFacturarEspecial").text();
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var estacionFacturacion = $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").prop("value");
        if(estacionFacturacion === undefined || estacionFacturacion === null || $.trim(estacionFacturacion) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar la estación solicitante de la facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        if($("#facturaEspecialVoucher").prop("checked")){
            var serieFacturacion = $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").prop("value");
            if(serieFacturacion === undefined || serieFacturacion === null || $.trim(serieFacturacion) === ""){
                core.hiddenDialog2(dialog);
                alert("Debe especificar una serie de facturación.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
        }
        
        var pingFacturacion = $("#pingFacturacionEspecial").prop("value");
        if(pingFacturacion === undefined || pingFacturacion === null || $.trim(pingFacturacion) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe definir el ping de autorización de facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var cantidadFacturasBoletos = $("#detalleFacturaBoletoBody").find("tr").length;
        if(cantidadFacturasBoletos <= 0){
            core.hiddenDialog2(dialog);
            alert("La estación seleccionada no tiene facturas disponibles.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    iniciarProcesoFacturarEspecial: function(importeTotalMonedaBase) {
        $("#detalleFacturaBoletoBody").find("tr").remove();
        $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").select2('val', "");
        $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2('val', "");
        $("#pingFacturacionEspecial").val("");
        $("#totalFacturarEspecial").text("Total: GTQ " + importeTotalMonedaBase);
        $("#cantidadBoletos").val($("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").length);
        if(reasignarBoleto.customPreValidateFacturaEspecial() === true){
            $("#facturaEspecialVoucher").prop("checked", true);
            reasignarBoleto.checkFacturaEspecialVoucher();
            core.showMessageDialog({
                title : "Registrar Facturar Otra Estación",
                selector: $("#facturacionEspecialDIV"),
                removeOnClose : false,
                uniqid : false,
                buttons: {
                    Aceptar: {
                        click: function() {
                                var dialogActual = this;
    //                            console.debug("Aceptar-click...");
                                var boletoForm = $("#boletoForm");
                                if(core.customValidateForm(boletoForm) === true && reasignarBoleto.customPostValidateFacturaEspecial(this) === true){
                                    $("#reasignar_boleto_command_movil").prop("value", core.isMovil());
                                    $("#reasignar_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                    $("#reasignar_boleto_command_estacionFacturacionEspecial").prop("value", $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").prop("value"));
                                    $("#reasignar_boleto_command_serieFacturacionEspecial").prop("value", $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").prop("value"));
                                    $("#reasignar_boleto_command_pingFacturacionEspecial").prop("value", $("#pingFacturacionEspecial").prop("value"));
                                    if($("#facturaEspecialVoucher").prop("checked") === true){
                                        $("#reasignar_boleto_command_tipoDocuemento").prop("value", 4); //Factura
                                    }else{
                                        $("#reasignar_boleto_command_tipoDocuemento").prop("value", 7); //Voucher
                                    }
                                    reasignarBoleto.syncronizarListaBoletos();
                                    $(boletoForm).ajaxSubmit({
                                        target: boletoForm.attr('action'),
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
                                                dialogActual.dialog2("close");
                                                var info = core.getValueFromResponse(responseText, "info");
                                                core.showNotification({text : info});
                                                alert("Operación realizada satisfactoriamente. " + info, function() {
                                                    core.getPageForMenu($("#pathHomeBoletos").attr('value'));
                                                }); 
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
        }
    },
    
    customCommunValidate: function(dialog) {
//        console.log("customCommunValidate-init");
        cantidadBoletos = $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").length;
        if(cantidadBoletos <= 0){
            if(dialog){
                core.hiddenDialog2(dialog);
            }
            alert("Debe definir al menos un boleto.", function(){
                if(dialog){
                    core.showDialog2(dialog);
                }
            });
            return false;
        }
        if(cantidadBoletos > 1){
            if(dialog){
                core.hiddenDialog2(dialog);
            }
            alert("Para las reasignaciones no puede seleccionar más de un boleto.", function(){
                if(dialog){
                    core.showDialog2(dialog);
                }
            });
            return false;
        }
        return true;
    },
    
    customPostValidateFactura: function(dialog) {
        
        if($("#facturaVoucher").prop("checked") === true){
            if(frondend.listaImpresorasDisponibles === null || $.trim(frondend.listaImpresorasDisponibles) === ""){
                core.hiddenDialog2(dialog);
                alert("El sistema aún no ha podido detectar las impresoras para la facturación.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
            
            var serieFactura = $("#reasignar_boleto_command_serieFacturaVirtual").select2("val");
            if( $.trim(serieFactura) === "" ){
                core.hiddenDialog2(dialog);
                alert("Debe seleccionar una serie de factura.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
        }
        
        var tipoPago = $("#reasignar_boleto_command_tipoPagoVirtual").prop("value");
        if(tipoPago === undefined || tipoPago === null || $.trim(tipoPago) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un tipo de pago.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }else{
            
            if(tipoPago === "1"){  //Efectivo
                
               var monedaPago = $("#reasignar_boleto_command_monedaPagoVirtual").prop("value");
                if(monedaPago === undefined || monedaPago === null || $.trim(monedaPago) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar una moneda de pago.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var efectivo = $("#reasignar_boleto_command_efectivoVirtual").prop("value");
                if(efectivo === undefined || efectivo === null || $.trim(efectivo) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un efectivo.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                } 
            }else{
                
                var autorizacionTarjeta = $("#reasignar_boleto_command_autorizacionTarjetaVirtual").prop("value");
                if(autorizacionTarjeta === undefined || autorizacionTarjeta === null || $.trim(autorizacionTarjeta) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un código de autorización.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
            }
        }
        
        var totalNeto = $("#reasignar_boleto_command_totalNetoVirtual").prop("value");
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
            tipoPago = $("#reasignar_boleto_command_tipoPagoVirtual").val();
        }
        if(tipoPago === ""){  //Vacio
            $("#reasignar_boleto_command_totalNetoVirtual").val("");
            $(".bloque2").hide();
            $(".bloque5").hide();
            reasignarBoleto.checkBloque3(tipoPago);
        }
        else if(tipoPago === "1"){ //Efectivo
            $(".bloque2").show();
            $(".bloque5").hide();
            reasignarBoleto.checkBloque3(tipoPago);
        }
        else if(tipoPago === "2"){
            $(".bloque2").hide();
            $(".bloque5").show();
            reasignarBoleto.checkBloque3(tipoPago);
        }
        else if(tipoPago === "3"){
            $(".bloque2").hide();
            $(".bloque5").show();
            reasignarBoleto.checkBloque3(tipoPago);
        }
    },
    
    checkBloque3: function(tipoPago) {
//        console.log("checkBloque3-init");
        if(!tipoPago){
            tipoPago = $("#reasignar_boleto_command_tipoPagoVirtual").val();
        }
        var monedaPago = $("#reasignar_boleto_command_monedaPagoVirtual").val();
        if(tipoPago === "1" && monedaPago !== "" && monedaPago !== "1"){  //Efectivo
            $(".bloque3").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque3").hide();
        }
        reasignarBoleto.checkBloque4(tipoPago);
    },
    
    checkBloque4: function(tipoPago) {
//        console.log("checkBloque4-init");
        if(!tipoPago){
            tipoPago = $("#reasignar_boleto_command_tipoPagoVirtual").val();
        }
        if(tipoPago === "1"){  //Efectivo
            $(".bloque4").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque4").hide();
        }
    },
    
    calcularImporteTotalMonedaBaseGenerico: function(successCallback, async, extra) {
        reasignarBoleto.syncronizarListaBoletos();
        var idEstacionOrigen = $("#reasignar_boleto_command_estacionSubeEn").val();
        var idEstacionDestino = $("#reasignar_boleto_command_estacionBajaEn").val();
        var idSalida = core.getSelectedItemId("#salidaGrid");
        var listaClienteBoleto = $("#reasignar_boleto_command_listaClienteBoleto").val();
        var utilizarDesdeEstacionOrigenSalida = $("#reasignar_boleto_command_utilizarDesdeEstacionOrigenSalida").prop('checked');
        var result = 0;
        if(idEstacionOrigen !== null && $.trim(idEstacionOrigen) !== "" && 
           idEstacionDestino !== null && $.trim(idEstacionDestino) !== "" && 
           idSalida !== null && $.trim(idSalida) !== "" && 
           listaClienteBoleto !== null && $.trim(listaClienteBoleto) !== ""){
           var idTipoPago = $("#reasignar_boleto_command_tipoPagoVirtual").val();
           if(idTipoPago === null || $.trim(idTipoPago) === ""){
               idTipoPago = "1"; //Efectivo por defecto
           }
           var idBoletoOriginal = $("#reasignar_boleto_command_boletoOriginal").val();
           var extraParams = {
                idEstacionOrigen : idEstacionOrigen,
                idEstacionDestino : idEstacionDestino,
                idTipoPago : idTipoPago,
                idSalida: idSalida,
                listaClienteBoleto: listaClienteBoleto,
                idBoletoOriginal: idBoletoOriginal,
                pagadoDesdeOrigenRuta: utilizarDesdeEstacionOrigenSalida
           };
           if(extra){ jQuery.extend(extraParams, extra); }
           core.request({
                url : $("#pathCalcularImporteTotalMonedaBase").attr('value'),
                method: 'POST', //Obligatorio
                extraParams: extraParams, 
                dataType: "json",
                async: async,
                successCallback: function(success){
                    if(success.error && $.trim(success.error) !== ""){
                        result = -1; //indicando error.
                        var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                        modal.hide();
                        alert(success.error, function (){
                           modal.show(); 
                        });
                    }else{
                       result = success.total;
                       if(successCallback && $.isFunction(successCallback)){
                           successCallback(success, extraParams);
                       } 
                    }
                }
           });
       }
       return result;
    },
   
    calcularImporteTotalMonedaBase: function() {
//        console.log("calcularImporteTotalMonedaBase....");
        reasignarBoleto.calcularImporteTotalMonedaBaseGenerico(function (success){
            $("#reasignar_boleto_command_totalNetoVirtual").val(success.total);
            var idTipoPago = $("#reasignar_boleto_command_tipoPagoVirtual").val();
            if(idTipoPago === "1"){  //Solo si es efectivo, en otro caso no hace falta.. ya estan limpios los inputs...
                reasignarBoleto.changeMonedaPago();
            }
        }, false, {});        
    },
    
    changeTipoPago: function() {
//        console.log("changeTipoPago-init");
        reasignarBoleto.checkBloque2();
        $("#reasignar_boleto_command_totalNetoVirtual").val("");
        $("#reasignar_boleto_command_tasaVirtual").val("");
        $("#reasignar_boleto_command_totalPagoVirtual").val("");
        $("#reasignar_boleto_command_vueltoVirtual").val("");
        $("#reasignar_boleto_command_efectivoVirtual").val("");
        reasignarBoleto.calcularImporteTotalMonedaBase();
    },
    
    changeMonedaPago: function() {
//      console.log("changeMonedaPago-init");  
      var monedaPago = $("#reasignar_boleto_command_monedaPagoVirtual").val();
      $("#reasignar_boleto_command_tasaVirtual").val("");
      $("#reasignar_boleto_command_totalPagoVirtual").val("");
      $("#reasignar_boleto_command_vueltoVirtual").val("");
      if(monedaPago === null || $.trim(monedaPago) === ""){
        $("#prependTotalPagoVirtual").text("???");
        $("#prependEfectivoVirtual").text("???");
      }
      else if($.trim(monedaPago) === "1"){
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        reasignarBoleto.changeEfectivo();
      }
      else{
        //NO SON QUETSALES
        var totalNeto = $("#reasignar_boleto_command_totalNetoVirtual").val();
        var idMonedaPago = $("#reasignar_boleto_command_monedaPagoVirtual").val();
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
                        $("#reasignar_boleto_command_tasaVirtual").val(success.tasa);
                        $("#reasignar_boleto_command_totalPagoVirtual").val(success.total);
                        reasignarBoleto.changeEfectivo();
                    }
                }
           });
        }
      }
      reasignarBoleto.checkBloque3();
    },
    
    
    changeEfectivo: function() {
//      console.log("changeEfectivo-init");
      var monedaPago = $("#reasignar_boleto_command_monedaPagoVirtual").val();
      $("#reasignar_boleto_command_vueltoVirtual").val("");
      
      var efectivo = $("#reasignar_boleto_command_efectivoVirtual").val();
      if(!$.isNumeric(efectivo)){
          $("#reasignar_boleto_command_efectivoVirtual").val("");
          return;
      }else{
          efectivo = parseFloat(efectivo).toFixed(2);
          $("#reasignar_boleto_command_efectivoVirtual").val(efectivo);
      }
      
      if($.trim(monedaPago) === "1"){ //GTQ
         var totalNeto = $("#reasignar_boleto_command_totalNetoVirtual").val();
         var vuelto = efectivo - parseFloat(totalNeto);
         vuelto = parseFloat(vuelto).toFixed(2);
         if(vuelto === 0){
              $("#reasignar_boleto_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalNeto + ".", function (){
                modal.show();
                $("#reasignar_boleto_command_efectivoVirtual").val(totalNeto); // Se le setea el importe minimo.
                $("#reasignar_boleto_command_vueltoVirtual").val(0);
             });
         }else{
             $("#reasignar_boleto_command_vueltoVirtual").val(vuelto);
         }
      }
      else{
        //NO SON QUETSALES
        var totalPago = $("#reasignar_boleto_command_totalPagoVirtual").val();
        var vuelto = efectivo - parseFloat(totalPago);
         vuelto = parseFloat(vuelto).toFixed(2); //Vuelto en EUR O USD
         if(vuelto === 0){
              $("#reasignar_boleto_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalPago + ".", function (){
                modal.show();
                $("#reasignar_boleto_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                $("#reasignar_boleto_command_vueltoVirtual").val(0);
             });
         }else{

            //Convirtiendo el vuelto a Quetsales
            var idMonedaPago = $("#reasignar_boleto_command_monedaPagoVirtual").val();
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
                           $("#reasignar_boleto_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                           $("#reasignar_boleto_command_vueltoVirtual").val(0);
                        });
                    }else{
                        $("#reasignar_boleto_command_vueltoVirtual").val(success.total);
                    }
                }
           });
        }
         }
      }
    },
    
    changeEstacionFacturacionEspecial : function() {
        var estacionFacturacionEspecial = $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").select2('val');
        var idSalida = core.getSelectedItemId("#salidaGrid");
        $("#detalleFacturaBoletoBody").find("tr").remove(); //Elimino todos los tr
        $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2({ data: [] });
        $("#facturaEspecialVoucher").prop("checked", true);
        reasignarBoleto.checkFacturaEspecialVoucher();
        if(estacionFacturacionEspecial !== null && $.trim(estacionFacturacionEspecial) !== "" && 
                idSalida !== null && $.trim(idSalida) !== "" ){
            core.request({
                url : $("#pathSeriesActivaPorEstacion").attr('value'),
                method: 'POST',
                extraParams: {
                    idEstacion : estacionFacturacionEspecial,
                    idSalida : idSalida
                }, 
                dataType: "json",
                async:true,
                successCallback: function(success){
                    var optionSeriesFacturas = success.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        
                        var cantidad = optionSeriesFacturas.length;
                        $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").data("sizeItems", cantidad);
                        if(cantidad > 0){
                            if(optionSeriesFacturas[0]){
                                $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2('val', optionSeriesFacturas[0].id);
                                reasignarBoleto.changeSerieFacturacionEspecial();
                            }
                        }else{
                            var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                            modal.hide();
                            alert("La estación no tiene series de facturas activas.", function (){
                                modal.show();  //Quito el popup ya que es innecesario, al final hay que cancelar         	
                            });
                        }
                    }
                    if(success.showVoucher && eval(success.showVoucher) === true){
                       $("#facturaEspecialVoucher").removeClass("hidden");
                    }else{
                       $("#facturaEspecialVoucher").addClass("hidden");
                    }
                }
           });
        }
    },
    
    changeSerieFacturacionEspecial : function() {
        var estacionFacturacionEspecial = $("#reasignar_boleto_command_estacionFacturacionEspecialVirtual").select2('val');
        var serieFacturacionEspecial = $("#reasignar_boleto_command_serieFacturacionEspecialVirtual").select2('val');
        $("#detalleFacturaBoletoBody").find("tr").remove(); //Elimino todos los tr
        reasignarBoleto.calcularImporteTotalMonedaBaseGenerico(function (success){
            if(success.error && $.trim(success.error) !== ""){
                var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                modal.hide();
                alert(success.error, function (){
                    modal.show();  //Quito el popup ya que es innecesario, al final hay que cancelar         	
                });
            }else{
                var boletos = success.boletos;
                $.each(boletos, function() {  
                    var itemTR = $("<tr><td>"+this.numero+"</td><td>"+this.factura+"</td><td>"+this.importe+"</td></tr>");
                    $("#detalleFacturaBoletoBody").append(itemTR);
                });
            }
        }, false, { 
            detalle : true,
            estacionDetalle : estacionFacturacionEspecial,
            facturar : $("#facturaEspecialVoucher").prop("checked"),
            serieFacturacionEspecial : serieFacturacionEspecial
        });
    }
};
