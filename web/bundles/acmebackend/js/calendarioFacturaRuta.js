calendarioFacturaRuta = {
    
     funcionesAddOnload : function() {
        console.debug("calendarioFacturaRuta.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("calendarioFacturaRuta.funcionesAddOnload-end");
    },
			
    _init : function() {
        this.checkedConstante();
        this.procesarHiddenEmpresas();
        this.showCalendarioFechas();
     },
    
    _conectEvents : function() {
        
         $('#acme_backendbundle_calendario_factura_ruta_type_constante').bind("click", function() { 
              console.debug("constante - click");
              calendarioFacturaRuta.checkedConstante();
         });
         $('.empresa').bind("click", function() {
             calendarioFacturaRuta.chekedEmpresa(this);
         });
         $('.empresa-seleccionado').bind("click", function() {
             calendarioFacturaRuta.chekedEmpresa($(this).parent().parent());
         });
         $('.item-mes-dia').bind("click", function() {
             calendarioFacturaRuta.clickItemMesDia($(this));
         });    
         
    },
    
    buscaFacturaInArrayPorFecha: function(fecha, items){
        
        if(!fecha || !items || items.length === 0)
            return null;
        
        for (i = 0; i < items.length; i++) {
            if(calendarioFacturaRuta.dateComapreTo(items[i].fecha, fecha) === 0){
               return items[i];
           }
        }
        
        return null;
    },
      
    clickItemMesDia : function(item) {
        console.debug("clickItemMesDia-init");
        item = $(item);
        var fechaActual = $("#acme_backendbundle_calendario_factura_ruta_type_fechaActual").val();
        var fecha = item.data("fecha");
        var result = calendarioFacturaRuta.dateComapreTo(fechaActual, fecha);
        if( result >= 0){
            var mensaje = "No se puede modificar la empresa del factura del día:" + fecha + " porque ";
            if(result === 0) mensaje += " se está usando actualmente.";
            else mensaje += " ya fue usada.";
            mensaje += " El sistema tiene fecha actual:" + fechaActual + ".";
            alert(mensaje);
            return false;
        }
        
        var empresaDiv = $(".empresa-seleccionado[checked]");
        var idEmpresa = item.data("empresa");
        console.debug(idEmpresa);
        if(empresaDiv.length === 0 && (!idEmpresa || idEmpresa === null || idEmpresa === "")){
            alert("No hay ninguna empresa seleccionada.");
        }
        else if(!idEmpresa || idEmpresa === null || idEmpresa === ""){
            console.debug("adicionando..");
            var itemsEmpresas = $("#acme_backendbundle_calendario_factura_ruta_type_listaEmpresas").val();
            if(itemsEmpresas){ itemsEmpresas = JSON.parse(itemsEmpresas); }
            else{ itemsEmpresas = []; }
            var idEmpresa = empresaDiv.data("id");
            var empresa = itemsEmpresas[idEmpresa];
            var color = empresa.color;
            item.css("background-color", "#"+color);
            item.css("color", "#FFFFFF");
            item.data("empresa", idEmpresa);
            var itemsFacturas = $("#acme_backendbundle_calendario_factura_ruta_type_listaCalendarioFacturaFechaHidden").val();
            if(itemsFacturas){ itemsFacturas = JSON.parse(itemsFacturas); }
            else{ itemsFacturas = []; }
            
            var factura = calendarioFacturaRuta.buscaFacturaInArrayPorFecha(fecha, itemsFacturas);
            console.debug(factura);
            if(factura){
                console.debug("x1");
                factura.empresa = idEmpresa;
            }else{
                console.debug("x2");
                factura = {
                    id : 0,
                    empresa : idEmpresa,
                    fecha : fecha
                };
                console.debug(factura);
                itemsFacturas.push(factura);
                console.debug(itemsFacturas);
            }
            console.debug(itemsFacturas);
            $("#acme_backendbundle_calendario_factura_ruta_type_listaCalendarioFacturaFechaHidden").val(JSON.stringify(itemsFacturas));
            console.debug($("#acme_backendbundle_calendario_factura_ruta_type_listaCalendarioFacturaFechaHidden").val());
        }else{
            console.debug("removiendo..");
            item.css("background-color","");
            item.css("color", "");
            item.data("empresa", "");
            var itemsFacturas = $("#acme_backendbundle_calendario_factura_ruta_type_listaCalendarioFacturaFechaHidden").val();
            if(itemsFacturas){ itemsFacturas = JSON.parse(itemsFacturas); }
            else{ itemsFacturas = []; }
            var factura = calendarioFacturaRuta.buscaFacturaInArrayPorFecha(fecha, itemsFacturas);
            itemsFacturas.splice(itemsFacturas.indexOf(factura), 1);
            $("#acme_backendbundle_calendario_factura_ruta_type_listaCalendarioFacturaFechaHidden").val(JSON.stringify(itemsFacturas));
        }
        console.debug(item);
    },
    
    procesarHiddenEmpresas : function() {
        var hiddenEmpresas = $("#acme_backendbundle_calendario_factura_ruta_type_listaEmpresas").val();
        if(hiddenEmpresas){ listaEmpresas = JSON.parse(hiddenEmpresas); }
        else{ listaEmpresas = []; }
        var html = "";
        jQuery.each(listaEmpresas, function() {
            html += "<div id='empresa-" + this.id + "' class='empresa'>" +                   
                   "<div class='empresa-color' data-color='" + this.color + "'>" +
                   "<input type='checkbox' class='empresa-seleccionado' data-id='" + this.id + "'></input>" +
                   "</div>" +
                   "<label class='empresa-nombre'>" + this.nombre + "</label>"  +
                   "</div>";            
        });
        if(html === "") html = "No hay empresas disponibles";
        $(".leyenda").html(html);
        jQuery.each($(".empresa-color"), function() {
            var color = $(this).data("color");
            $(this).css("background-color", "#"+color);
        });
    },
    
    showCalendarioFechas : function() {
        console.debug("showCalendarioFechas-init");
        var itemsEmpresas = $("#acme_backendbundle_calendario_factura_ruta_type_listaEmpresas").val();
        var itemsFacturas = $("#acme_backendbundle_calendario_factura_ruta_type_listaCalendarioFacturaFechaHidden").val();
        var fechaActual = $("#acme_backendbundle_calendario_factura_ruta_type_fechaActual").val();
        var fechaInicial = $("#acme_backendbundle_calendario_factura_ruta_type_fechaInicial").val();
        var fechaFinal = $("#acme_backendbundle_calendario_factura_ruta_type_fechaFinal").val();
        if(itemsEmpresas){ itemsEmpresas = JSON.parse(itemsEmpresas); }
        else{ itemsEmpresas = []; }
        if(itemsFacturas){ itemsFacturas = JSON.parse(itemsFacturas); }
        else{ itemsFacturas = []; }
        
        $("#theadCalendario").html("");
        $("#theadCalendario").append("<tr></tr>");
        
        var dateHeader = this.formatDate(fechaInicial);
        for (var i=1; i <= 12; i++){            
            $("#theadCalendario tr").append("<th id='fecha-" + i + "' class='fecha'>" + $.datepicker.formatDate('mm/yy', dateHeader) + "</th>");
            dateHeader.setMonth(dateHeader.getMonth() + 1);
        }

        $("#tbbodyCalendario").html("");
        var primerMes = eval(fechaInicial.split("-")[1]);
        var primerAno = eval(fechaInicial.split("-")[2]);
        for (var i=1; i <= 31; i++){ // Dias
           $("#tbbodyCalendario").append("<tr id='filadias-"+i+"' ></tr>");
           for (var y = 0; y < 12; y++){ //Meses
               var diaReal = i;
               var mesReal = primerMes + y ;
               var anoReal = primerAno;
               if(mesReal > 12){
                   mesReal = mesReal - 12;
                   anoReal = primerAno + 1;
               }     
               var cadenaDate = diaReal + "-" + mesReal + "-" + anoReal;
               if(this.isDate(cadenaDate)){
                   var dataEmpresa = "";
                   var idEmpresa = null;
                   var factura = calendarioFacturaRuta.buscaFacturaInArrayPorFecha(cadenaDate, itemsFacturas); 
                   if(factura){
                       idEmpresa = factura.empresa;
                       dataEmpresa = " data-empresa='"+idEmpresa+"'";
                   } 
                   var dataFecha = " data-fecha='"+cadenaDate+"'";
                   
                   var valueInfo = diaReal;
                   var result = calendarioFacturaRuta.dateComapreTo(fechaActual, cadenaDate);
                   if( result >= 0){
                       valueInfo = "<em>" + valueInfo + "</em>";
                   }                   
                   
                   var item = $("<td><div class='item-mes-dia' "+dataEmpresa+dataFecha+" id='item-mes-"+mesReal+"-dia-"+diaReal+"' >"+valueInfo+"</div></td>");
                   $("#filadias-"+i).append(item);
                   
                   if(idEmpresa !== null){
                       var value = itemsEmpresas[idEmpresa].color;
                       var layer = item.find("div");
                       layer.css("background-color", "#"+value);
                       layer.css("color", "#FFFFFF");
                   }
                   
               }else{
                   $("#filadias-"+i).append("<td></td>");
               }          
           }           
        }
        
        console.debug("showCalendarioFechas-end");
    },
    
    chekedEmpresa : function(item) {
        var input = $(item).find("input");
        var marcado = input.attr("checked") === 'checked';
        if(marcado) {
            $(input).removeAttr("checked");
        }else{
            $(".empresa-seleccionado").removeAttr("checked");
            $(input).attr("checked", "checked");
        }
    },
    
    checkedConstante : function() {
        console.debug("chekedConstante - init");
        var checked = $("#acme_backendbundle_calendario_factura_ruta_type_constante").attr("checked");
        if(checked){
            $("#acme_backendbundle_calendario_factura_ruta_type_empresa").attr('required', 'required');
            $(".fechasDiv").hide();
            $(".empresaDiv").show();
        }else{
            $("#acme_backendbundle_calendario_factura_ruta_type_empresa").val("");
            $("#acme_backendbundle_calendario_factura_ruta_type_empresa").select2("val", "");
            $("#acme_backendbundle_calendario_factura_ruta_type_empresa").removeAttr('required');
            $(".empresaDiv").hide();
            $(".fechasDiv").show();
        }        
    },
    
    //Debe tener el formato dd/mm/yyyy o dd-mm-yyyy o  dd.mm.yyyy y la funcion retorna un objeto date
    formatDate : function(value){
        items = value.split(/[\.\-\/]/);
        dia = items[0];
        mes = items[1];
        anho = items[2];
        fecha = anho + "," + mes + "," + dia;
        return new Date(fecha);
    },
    
    dateComapreTo : function(fecha1, fecha2) {
        return  this.formatDate(fecha1).getTime() - this.formatDate(fecha2).getTime();
    },
    
    isDate : function(str) {    
        var parms = str.split(/[\.\-\/]/);
        var yyyy = parseInt(parms[2],10);
        var mm   = parseInt(parms[1],10);
        var dd   = parseInt(parms[0],10);
        var date = new Date(yyyy, mm-1,dd,0,0,0,0);
        return mm === (date.getMonth()+1) && dd === date.getDate() && yyyy === date.getFullYear();
     },
    
    areDateEquals: function(date1, date2){
        if(date1.getFullYear() === date2.getFullYear() && 
           date1.getMonth() === date2.getMonth() && 
           date1.getDate() === date2.getDate())
            return true;
        else 
            return false;
    },
    
}