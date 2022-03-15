ruta = {
    
    funcionesAddOnload : function() {
        console.debug("ruta.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("ruta.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $('.buttons').affix();
        this.renderEstacionesDisponibles();
        this.renderEstacionesIntermedias();
    },
    
    _conectEvents : function() {
        
        $("#button1").click(ruta.clickRight);
        $("#button2").click(ruta.clickLeft);
        $("#button3").click(ruta.clickUp);
        $("#button4").click(ruta.clickDown);
         
    },
    
    renderEstacionesDisponibles : function() {
        console.log("renderEstacionesDisponibles-init");
        $("#estacionDisponiblesBody").find("tr").not("#estacionDisponiblesVacioTR").remove();
        $("#estacionDisponiblesBody").find("#estacionDisponiblesVacioTR").show();
        
        var listaEstacionesDisponibles = $("#acme_backendbundle_ruta_type_listaEstacionesDisponiblesHidden").val();
        if(listaEstacionesDisponibles){ listaEstacionesDisponibles = JSON.parse(listaEstacionesDisponibles); }
        else{ listaEstacionesDisponibles = []; }
        
        if(listaEstacionesDisponibles.length !== 0){
            $("#estacionDisponiblesBody").find("#estacionDisponiblesVacioTR").hide();
            console.log("renderEstacionesDisponibles: " + listaEstacionesDisponibles.length + " items");
            jQuery.each(listaEstacionesDisponibles, function() {
                var itemTR = $("<tr class='trSelect' data-id='"+this.id+"'><td><input type='checkbox' /></td><td class='nombreTD'>"+this.nombre+"</td></tr>");
                $("#estacionDisponiblesBody").append(itemTR);
            });
        }
    },
    
    renderEstacionesIntermedias : function() {
        console.log("renderEstacionesIntermedias-init");
        $("#estacionesIntermediasBody").find("tr").not("#estacionesIntermediasVacioTR").remove();
        $("#estacionesIntermediasBody").find("#estacionesIntermediasVacioTR").show();
        
        var listaEstacionesIntermedias = $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val();
        if(listaEstacionesIntermedias){ listaEstacionesIntermedias = JSON.parse(listaEstacionesIntermedias); }
        else{ listaEstacionesIntermedias = []; }
        
        if(listaEstacionesIntermedias.length !== 0){
            $("#estacionesIntermediasBody").find("#estacionesIntermediasVacioTR").hide();
            console.log("renderEstacionesIntermedias: " + listaEstacionesIntermedias.length + " items");
            jQuery.each(listaEstacionesIntermedias, function() {
                var itemTR = $("<tr class='trSelect' data-id='"+this.id+"'><td><input type='checkbox' /></td><td class='posicionTD'>"+this.posicion+"</td><td class='nombreTD'>"+this.nombre+"</td></tr>");
                $("#estacionesIntermediasBody").append(itemTR);
            });
        }
    },
    
    clickUp : function() {
        console.log("clickUp-init");
        var itemsSelected = [];
        var itemsCheckbox = $("#estacionesIntermediasBody").find('input[type="checkbox"]');
        $.each(itemsCheckbox, function(index, item) {
            item = $(item);
            if(item.prop("checked") === true){
                var select = item.parent().parent();
               var element = {
                   id : select.data("id"),
                   nombre : select.find(".nombreTD").text(),
                   posicion : select.find(".posicionTD").text()
               };
               console.log("se selecciono el elemento con id " + element.id + ".");
               itemsSelected.push(element);
            }
        });
        console.log("Se seleccionaron " + itemsSelected.length + " elementos.");
        
        if(itemsSelected.length === 0){
            alert("Debe seleccionar una estación intermedia");
            return;
        }else if(itemsSelected.length > 1){
            alert("Solamente puede reubicar una estación intermedia la vez.");
            return;
        }
        
        var itemSelected = itemsSelected[0];
        console.debug(itemSelected);
        var idItemSelected = itemSelected.id;
        var posicionItemSelected = parseInt(itemSelected.posicion);
        
        var listaEstacionesIntermedias = $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val();
        if(listaEstacionesIntermedias){ listaEstacionesIntermedias = JSON.parse(listaEstacionesIntermedias); }
        else{ listaEstacionesIntermedias = []; }
        
        if(posicionItemSelected <= 1){
            alert("El elemento está al principio de la lista.");
            return;
        }
        
        var item1 = listaEstacionesIntermedias[posicionItemSelected-1];
        item1.posicion = posicionItemSelected - 1;
        var item2 = listaEstacionesIntermedias[posicionItemSelected-2];
        item2.posicion = posicionItemSelected;
        listaEstacionesIntermedias[posicionItemSelected-1] = item2;
        listaEstacionesIntermedias[posicionItemSelected-2] = item1;
        
        $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val(JSON.stringify(listaEstacionesIntermedias));
        ruta.renderEstacionesIntermedias();
        
        $(".trSelect[data-id='"+idItemSelected+"']").find('input[type="checkbox"]').prop("checked", true);
    },
    
    clickDown : function() {
        console.log("clickUp-init");
        var itemsSelected = [];
        var itemsCheckbox = $("#estacionesIntermediasBody").find('input[type="checkbox"]');
        $.each(itemsCheckbox, function(index, item) {
            item = $(item);
            if(item.prop("checked") === true){
                var select = item.parent().parent();
               var element = {
                   id : select.data("id"),
                   nombre : select.find(".nombreTD").text(),
                   posicion : select.find(".posicionTD").text()
               };
               console.log("se selecciono el elemento con id " + element.id + ".");
               itemsSelected.push(element);
            }
        });
        console.log("Se seleccionaron " + itemsSelected.length + " elementos.");
        
        if(itemsSelected.length === 0){
            alert("Debe seleccionar una estación intermedia");
            return;
        }else if(itemsSelected.length > 1){
            alert("Solamente puede reubicar una estación intermedia la vez.");
            return;
        }
        
        var itemSelected = itemsSelected[0];
        console.debug(itemSelected);
        var idItemSelected = itemSelected.id;
        var posicionItemSelected = parseInt(itemSelected.posicion);
        
        var listaEstacionesIntermedias = $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val();
        if(listaEstacionesIntermedias){ listaEstacionesIntermedias = JSON.parse(listaEstacionesIntermedias); }
        else{ listaEstacionesIntermedias = []; }
        
        if(posicionItemSelected >= listaEstacionesIntermedias.length){
            alert("El elemento está en el final de la lista.");
            return;
        }
        
        var item1 = listaEstacionesIntermedias[posicionItemSelected-1];
        item1.posicion = posicionItemSelected + 1;
        var item2 = listaEstacionesIntermedias[posicionItemSelected];
        item2.posicion = posicionItemSelected;
        listaEstacionesIntermedias[posicionItemSelected-1] = item2;
        listaEstacionesIntermedias[posicionItemSelected] = item1;
        
        $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val(JSON.stringify(listaEstacionesIntermedias));
        ruta.renderEstacionesIntermedias();
        
        $(".trSelect[data-id='"+idItemSelected+"']").find('input[type="checkbox"]').prop("checked", true);
    },
    
    clickRight : function() {
        console.log("clickRight-init");
        var itemsSelected = [];
        var itemsCheckbox = $("#estacionDisponiblesBody").find('input[type="checkbox"]');
        $.each(itemsCheckbox, function(index, item) {
            item = $(item);
            if(item.prop("checked") === true){
                var select = item.parent().parent();
               var element = {
                   id : select.data("id"),
                   nombre : select.find(".nombreTD").text()
               };
               console.log("se selecciono el elemento con id " + element.id + ".");
               itemsSelected.push(element);
            }
        });
        console.log("Se seleccionaron " + itemsSelected.length + " elementos.");
        
        var listaEstacionesDisponibles = $("#acme_backendbundle_ruta_type_listaEstacionesDisponiblesHidden").val();
        if(listaEstacionesDisponibles){ listaEstacionesDisponibles = JSON.parse(listaEstacionesDisponibles); }
        else{ listaEstacionesDisponibles = []; }
        
        var listaEstacionesIntermedias = $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val();
        if(listaEstacionesIntermedias){ listaEstacionesIntermedias = JSON.parse(listaEstacionesIntermedias); }
        else{ listaEstacionesIntermedias = []; }
        
        $.each(itemsSelected, function(index, item) {
            console.log("buscando item: " + item.id + " para eliminar");
            var item1 = ruta.findItemById(listaEstacionesDisponibles, item.id);
            if(item1 !== null){
                console.log("Se encontro el item: " + item.id + " para eliminar");
                listaEstacionesDisponibles = ruta.removeItemArray(listaEstacionesDisponibles, item1);
            }else{
                console.log("No se encontro el item: " + item.id + " para eliminar");
            }
            
            var posicion = listaEstacionesIntermedias.length + 1;
            listaEstacionesIntermedias.push({
               id : item.id,
               nombre : item.nombre,
               posicion : posicion
            });
            
        });
        $("#acme_backendbundle_ruta_type_listaEstacionesDisponiblesHidden").val(JSON.stringify(listaEstacionesDisponibles));
        $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val(JSON.stringify(listaEstacionesIntermedias));
        
        ruta.renderEstacionesDisponibles();
        ruta.renderEstacionesIntermedias();
    },
    
    clickLeft : function() {
        console.log("clickLeft-init");
        var itemsSelected = [];
        var itemsCheckbox = $("#estacionesIntermediasBody").find('input[type="checkbox"]');
        $.each(itemsCheckbox, function(index, item) {
            item = $(item);
            if(item.prop("checked") === true){
                var select = item.parent().parent();
               var element = {
                   id : select.data("id"),
                   nombre : select.find(".nombreTD").text()
               };
               console.log("se selecciono el elemento con id " + element.id + ".");
               itemsSelected.push(element);
            }
        });
        console.log("Se seleccionaron " + itemsSelected.length + " elementos.");
        
        var listaEstacionesDisponibles = $("#acme_backendbundle_ruta_type_listaEstacionesDisponiblesHidden").val();
        if(listaEstacionesDisponibles){ listaEstacionesDisponibles = JSON.parse(listaEstacionesDisponibles); }
        else{ listaEstacionesDisponibles = []; }
        
        var listaEstacionesIntermedias = $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val();
        if(listaEstacionesIntermedias){ listaEstacionesIntermedias = JSON.parse(listaEstacionesIntermedias); }
        else{ listaEstacionesIntermedias = []; }
        
        $.each(itemsSelected, function(index, item) {
            console.log("buscando item: " + item.id + " para eliminar");
            var item1 = ruta.findItemById(listaEstacionesIntermedias, item.id);
            if(item1 !== null){
                console.log("Se encontro el item: " + item.id + " para eliminar");
                listaEstacionesIntermedias = ruta.removeItemArray(listaEstacionesIntermedias, item1);
            }else{
                console.log("No se encontro el item: " + item.id + " para eliminar");
            }
            
            listaEstacionesDisponibles.push({
               id : item.id,
               nombre : item.nombre
            });
        });
        
        var posicion = 1;
        $.each(listaEstacionesIntermedias, function(index, item) {
            item.posicion = posicion;
            posicion++;
        });
        
        $("#acme_backendbundle_ruta_type_listaEstacionesDisponiblesHidden").val(JSON.stringify(listaEstacionesDisponibles));
        $("#acme_backendbundle_ruta_type_listaEstacionesIntermediasHidden").val(JSON.stringify(listaEstacionesIntermedias));
        
        ruta.renderEstacionesDisponibles();
        ruta.renderEstacionesIntermedias();
    },
    
    findItemById : function(lista, idElement) {
        var result = null;
        idElement = $.trim(idElement);
        $.each(lista, function() {  
           if($.trim(this.id) === idElement){
               result = this;
               return;
           }
        });
        return result;
    },
    
    removeItemArray : function(array, item) {
       return $.grep(array, function(value) {
           return value !== item; 
       });
    },
};