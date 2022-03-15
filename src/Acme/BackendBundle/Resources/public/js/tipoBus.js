tipoBus = {
    
    mensajeEliminarAsiento : "Esta seguro que desea eliminar el asiento?. Si el asiento ya esta referenciado desde un bus el sistema no lo dejara eliminar." ,
    funcionesAddOnload : function() {
        console.debug("tipoBus.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("tipoBus.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $('#iconos').affix();
        $(".icono").draggable({
            helper: "clone",
            cursor: 'move'
        });
        $(".lista").droppable({
            accept: ".icono",
            drop: this._listaDrop //ocurre cuando el draggable cae dentro del droppable
        });
        this._mostrarIconos();
    },
    
    _conectEvents : function() {
        
         $('.item .close').bind("click", this.eliminarItem);
         
    },
    
    eliminarItem : function() {
        console.debug("eliminaando...Item..");
        $item = $(this).parent();
        var jsIdItem = $item.attr('jsId');
        if($item.hasClass("asiento")){
            if (confirm(tipoBus.mensajeEliminarAsiento) === true)
            {
                var listaAsientos = $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val();
                if(listaAsientos){ listaAsientos = JSON.parse(listaAsientos); }
                else{ listaAsientos = []; }
                var listaNewAsientos = [];
                var contador = 0;
                jQuery.each(listaAsientos, function() {
                    if(this.jsId !== jsIdItem){
                       contador++;
                       this.numero = contador;                   
                       listaNewAsientos.push(this);                   
                    }                
                });
                $("#acme_backendbundle_tipobus_type_totalAsientos").val(contador);
                console.debug(listaNewAsientos);
                $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val(JSON.stringify(listaNewAsientos));  
            }                
        }
        else if($item.hasClass("senal"))
        {
            var listaSenales = $("#acme_backendbundle_tipobus_type_listaSenalHidden").val();
            if(listaSenales){ listaSenales = JSON.parse(listaSenales); }
            else{ listaSenales = []; }
            var listaNewSenales = [];
            jQuery.each(listaSenales, function() {
                if(this.jsId !== jsIdItem){
                    listaNewSenales.push(this);  
                 }                
             });
            console.debug(listaNewSenales);
            $("#acme_backendbundle_tipobus_type_listaSenalHidden").val(JSON.stringify(listaNewSenales)); 
        }
        else
        {
            console.debug($item);
            throw new Error("El elemento a eliminar no tiene la clase asiento o senal.");
        }
        tipoBus._mostrarIconos();
        
    },
    
    _mostrarIconos : function() {
        console.debug("mostrarIconos...Item..");
        $(".item").remove(); //Elimino todos los item para generarlos nuevamente con los for.
        
        var listaAsientos = $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val();        
        if(listaAsientos){ listaAsientos = JSON.parse(listaAsientos); }
        else{ listaAsientos = []; }
        jQuery.each(listaAsientos, function() {                                
            var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            if(clase === "1") { clase = "claseA"; }
            else if(clase === "2") { clase = "claseB"; }
            else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
            var item = $(".icono." + clase).clone();   
            item.removeClass("ui-draggable icono");
            item.addClass("item"); 
            var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2 
            if(eval(nivel2)) { $(".nivel2").append(item); }
            else { $(".nivel1").append(item); }            
            item.css("left" , tipoBus._ajustarPosicion(this.coordenadaX));
            item.css("top" , tipoBus._ajustarPosicion(this.coordenadaY));
            item.draggable({
                helper: "original",
                cursor: 'move',
                containment: 'parent',
                grid: [50,50],
                stop : tipoBus._draggableStop
            });
            var numero = this.numero;
            var id = this.id;  
            this.jsId = tipoBus.uniqId();
            item.attr('jsId', this.jsId);
            item.find(".detalle").text(this.numero);
        });
        $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val(JSON.stringify(listaAsientos));
        
        var listaSenales = $("#acme_backendbundle_tipobus_type_listaSenalHidden").val();
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
            item.css("left" , tipoBus._ajustarPosicion(this.coordenadaX));
            item.css("top" , tipoBus._ajustarPosicion(this.coordenadaY));
            item.draggable({
                helper: "original",
                cursor: 'move',
                containment: 'parent',
                grid: [50,50],
                stop : tipoBus._draggableStop
            });            
            var id = this.id; 
            this.jsId = tipoBus.uniqId();
            item.attr('jsId', this.jsId);
        });
        $("#acme_backendbundle_tipobus_type_listaSenalHidden").val(JSON.stringify(listaSenales));
        $('.item .close').bind("click", tipoBus.eliminarItem);
    },
    
    /* Funcion para cuando muevo el item dentro del contenedor*/
    _draggableStop: function() {
        $item = $(this);
        var left = tipoBus._ajustarPosicion($item.position().left);
        var top = tipoBus._ajustarPosicion($item.position().top);
        $item.css("left" , left);
        $item.css("top" , top);
        var jsIdItem = $item.attr('jsId');
        if($item.hasClass("asiento")){ 
            var listaAsientos = $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val();
            if(listaAsientos){ listaAsientos = JSON.parse(listaAsientos); }
            else{ listaAsientos = []; }
            jQuery.each(listaAsientos, function() { 
                if(this.jsId === jsIdItem){
                    this.coordenadaX = left;
                    this.coordenadaY = top;
                }
            });
            $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val(JSON.stringify(listaAsientos));    
        }
        else if($item.hasClass("senal"))
        {
           var listaSenales = $("#acme_backendbundle_tipobus_type_listaSenalHidden").val();
           if(listaSenales){ listaSenales = JSON.parse(listaSenales); }
           else{ listaSenales = []; }
           jQuery.each(listaSenales, function() { 
                if(this.jsId === jsIdItem){
                    this.coordenadaX = left;
                    this.coordenadaY = top;
                }
            });           
           $("#acme_backendbundle_tipobus_type_listaSenalHidden").val(JSON.stringify(listaSenales)); 
        }
    },
    
    /* Ocurre cuando cae un icono en un contenedor  */
    _listaDrop: function(event, ui){
        console.debug("drop...");
        var item = $(ui.draggable).clone();
        $(this).append(item);
        item.removeClass("ui-draggable icono");
        item.addClass("item");                
        var left = tipoBus._ajustarPosicion(ui.offset.left - $(this).position().left);
        var top = tipoBus._ajustarPosicion(ui.offset.top - $(this).position().top);
        item.css("left" , left);
        item.css("top" , top);
        item.attr('jsId', tipoBus.uniqId());
        item.draggable({
            helper: "original",
            cursor: 'move',
            containment: 'parent',
            grid: [50,50],
            stop : tipoBus._draggableStop
         });
                
         if(item.hasClass("asiento")){
            console.debug("puse un asiento...");                    
            var nivel2 = null;
            if($(this).hasClass("nivel1")){ nivel2 = false; }
            else if($(this).hasClass("nivel2")){ nivel2 = true; }
            else{ throw new Error("El contenedor debe ser de las clases: nivel1 o nivel2.");  }
            var clase = null;
            if(item.hasClass("claseA")){ clase = "1"; }
            else if(item.hasClass("claseB")){ clase = "2"; }
            else { throw new Error("El asiento debe ser de las clases: claseA o claseB."); }
            var coordenadaX = left;
            var coordenadaY = top;            
            var itemTotalAsientos = $("#acme_backendbundle_tipobus_type_totalAsientos");
            var totalAsientos = itemTotalAsientos.val();
            totalAsientos++;
            var asiento = {
                id: 0,
                nivel2: nivel2,
                clase : clase,
                coordenadaX : coordenadaX,
                coordenadaY : coordenadaY,
                numero : totalAsientos,
                jsId : item.attr('jsId')
            };   
            item.find(".detalle").text(asiento.numero);
            var asientoArray = [asiento];
            var listaAsientos = $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val();
            if(listaAsientos){ listaAsientos = JSON.parse(listaAsientos); }
            else{ listaAsientos = []; }
            listaAsientos = listaAsientos.concat(asientoArray);
            $("#acme_backendbundle_tipobus_type_listaAsientoHidden").val(JSON.stringify(listaAsientos));                    
            itemTotalAsientos.val(totalAsientos);            
         }
          
         if(item.hasClass("senal")){
            console.debug("puse una senal...");                    
            var nivel2 = null;
            if($(this).hasClass("nivel1")){ nivel2 = false; }
            else if($(this).hasClass("nivel2")){ nivel2 = true; }
            else{ throw new Error("El contenedor debe ser de las clases: nivel1 o nivel2."); }
            var tipo = null;
            if(item.hasClass("salida")){ tipo = "1"; }
            else if(item.hasClass("chofer")){ tipo = "2"; }
            else {  throw new Error("El tipo debe ser de las clases: salida o chofer."); }
            var senal = {
                id: 0,
                nivel2: nivel2,
                tipo : tipo,
                coordenadaX : left,
                coordenadaY : top,
                jsId : item.attr('jsId')
            };   
            var senalArray = [senal];
            var listaSenales = $("#acme_backendbundle_tipobus_type_listaSenalHidden").val();
            if(listaSenales){ listaSenales = JSON.parse(listaSenales); }
            else{ listaSenales = []; }
            listaSenales = listaSenales.concat(senalArray);
            $("#acme_backendbundle_tipobus_type_listaSenalHidden").val(JSON.stringify(listaSenales));
          }
          $('.item .close').bind("click", tipoBus.eliminarItem);
    },
    
    _ajustarPosicion : function(valor) {
        valor = Math.abs(valor);
        var result = parseInt(valor / 50);
        result = result * 50;
        if(valor%50 >= 25)
            result += 50;
        return result;
    },
        
    uniqId : function (prefijo) {
        if(!prefijo) prefijo = "internal_" ;
        return prefijo + Math.round(new Date().getTime() + (Math.random() * 10045));
    }
   
};
   
   
   
/*
 
    DRAG AND DROP
 
$(".icono").draggable({
            helper: "clone",
            cursor: 'move'
        });
        $(".lista").droppable({
            accept: ".icono",
            drop: function(event, ui) { //ocurre cuando el draggable cae dentro del droppable
                console.debug("drop...");
                var item = $(ui.draggable).clone();
                $(this).append(item);
                item.removeClass("ui-draggable icono");
                item.addClass("item");
                var left = tipoBus._ajustarPosicion(ui.position.left - $(this).position().left);
                var top = tipoBus._ajustarPosicion(ui.position.top - $(this).position().top);
                item.css("left" , left);
                item.css("top" , top);
                item.draggable({
                    helper: "original",
                    cursor: 'move',
                    containment: 'parent',
                    grid: [50,50],
                    stop : function() {
                        item.css("left" , tipoBus._ajustarPosicion($(this).position().left));
                        item.css("top" , tipoBus._ajustarPosicion($(this).position().top));
                    }
                });
                
                if(item.hasClass("asiento")){
                    console.debug("puse un asiento...");                    
                    var nivel2 = null;
                    if($(this).hasClass("nivel1")){ nivel2 = false; }
                    else if($(this).hasClass("nivel2")){ nivel2 = true; }
                    else{
                        throw new Error("El contenedor debe ser de las clases: nivel1 o nivel2.");
                    }
                    var clase = null;
                    if(item.hasClass("claseA")){ clase = "1"; }
                    else if(item.hasClass("claseB")){ clase = "2"; }
                    else { 
                        throw new Error("El asiento debe ser de las clases: claseA o claseB.");
                    }
                    var coordenadaX = left;
                    var coordenadaY = top;            
                    var itemTotalAsientos = $("input[name*='[totalAsientos]']");
                    var totalAsientos = itemTotalAsientos.val();
                    totalAsientos++;
                    var name = itemTotalAsientos.attr("id").split("_")[0];
                    var datos = "<div class='hide'>";                    
                    datos += "<input type='checkbox' name='"+name+"[listaAsientos]["+totalAsientos+"][nivel2]' ";
                    if(nivel2)  datos += " checked='checked' />" ;
                    else        datos += " />" ;
                    datos += "<input name='"+name+"[listaAsientos]["+totalAsientos+"][clase]' value='"+clase+"' />";
                    datos += "<input name='"+name+"[listaAsientos]["+totalAsientos+"][numero]' value='"+totalAsientos+"' />";
                    datos += "<input name='"+name+"[listaAsientos]["+totalAsientos+"][coordenadaX]' value='"+coordenadaX+"' />";
                    datos += "<input name='"+name+"[listaAsientos]["+totalAsientos+"][coordenadaY]' value='"+coordenadaY+"' />";
                    datos += "</div>";
                    item.append($(datos));       
                    itemTotalAsientos.val(totalAsientos);
                }
            }
        }); 

 */