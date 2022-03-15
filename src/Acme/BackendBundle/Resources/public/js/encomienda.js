encomienda = {
    
     funcionesAddOnload : function() {
        console.debug("encomienda.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("encomienda.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        var href = $(".generatePinPath").val();
        var html = $('<a class="btn sonata-action-element changePin" href="'+ href +'"><i class="icon-refresh"></i>Cambiar pin</div></a>');
        $("input[id*='codigo']").parent().append(html);
        this.hiddenItemsByTipoEncomienda();
        this.hiddenItemsByTipoDocumento();
     },
    
    _conectEvents : function() {
        
        $(".changePin").bind("click", this.changePin);
        $("select[id*='tipoEncomienda']").change(function () {
             encomienda.hiddenItemsByTipoEncomienda();
             encomienda.setPrecioCalculado();
        });
        $("select[id*='clienteRemitente']").change(function () {
             encomienda.setPrecioCalculado();
        });
        $("input[id*='cantidad']").change(function () {
             encomienda.setPrecioCalculado();
        });
        $("select[id*='tipoEncomiendaEspecial']").change(function () {
             encomienda.setPrecioCalculado();
        });
        $("input[id*='volumen']").change(function () {
             encomienda.setPrecioCalculado();
        });
        $("select[id*='tipoDocumento']").change(function () {
             encomienda.hiddenItemsByTipoDocumento();
             encomienda.setPrecioCalculado();
        });
    },
    
    changePin : function(event) {
        console.debug("changePin-init");
        $.get($(this).attr("href") , function( data ) {
            $( "input[id*='codigo']" ).val(data);
        });
        event.preventDefault();
        console.debug("changePin-end");
    },
    
    hiddenItemsByTipoEncomienda : function() {
        var tipoEncomienda = $("select[id*='tipoEncomienda']").val();
        if(tipoEncomienda === null || $.trim(tipoEncomienda) === ""){
            $("input[id*='cantidad']").parent().parent().hide();
            $("input[id*='cantidad']").val("");
            $("select[id*='tipoEncomiendaEspecial']").parent().parent().parent().parent().hide();
            $("select[id*='tipoEncomiendaEspecial']").val("");
            $("select[id*='tipoEncomiendaEspecial']").parent().find(".select2-chosen").text("");
            $("input[id*='volumen']").parent().parent().hide();
            $("input[id*='volumen']").val("");
        }else if($.trim(tipoEncomienda) === "1"){ //Efectivo
            $("input[id*='cantidad']").parent().parent().show();
            $("select[id*='tipoEncomiendaEspecial']").parent().parent().parent().parent().hide();
            $("select[id*='tipoEncomiendaEspecial']").val("");
            $("select[id*='tipoEncomiendaEspecial']").parent().find(".select2-chosen").text("");
            $("input[id*='volumen']").parent().parent().hide();
            $("input[id*='volumen']").val("");
        }else if($.trim(tipoEncomienda) === "2"){ //Especial
            $("input[id*='cantidad']").parent().parent().show();
            $("select[id*='tipoEncomiendaEspecial']").parent().parent().parent().parent().show();
            $("input[id*='volumen']").parent().parent().hide();
            $("input[id*='volumen']").val("");
        }else if($.trim(tipoEncomienda) === "3"){ //Paquete
            $("input[id*='cantidad']").parent().parent().show();
            $("select[id*='tipoEncomiendaEspecial']").parent().parent().parent().parent().hide();
            $("select[id*='tipoEncomiendaEspecial']").val("");
            $("select[id*='tipoEncomiendaEspecial']").parent().find(".select2-chosen").text("");
            $("input[id*='volumen']").parent().parent().show();
        } 
    },
    
    hiddenItemsByTipoDocumento : function() {
        var tipoDocumento = $("select[id*='tipoDocumento']").val();
        if(tipoDocumento === null || $.trim(tipoDocumento) === ""){
            $("select[id*='autorizacionCortesia']").parent().parent().hide();
            $("select[id*='autorizacionCortesia']").val("");
            $("select[id*='autorizacionCortesia']").parent().find(".select2-chosen").text("");
            $("select[id*='autorizacionInterna']").parent().parent().hide();
            $("select[id*='autorizacionInterna']").val("");
            $("select[id*='autorizacionInterna']").parent().find(".select2-chosen").text("");
            $("select[id*='tipoPago']").parent().parent().parent().parent().hide();
            $("select[id*='tipoPago']").val("");
            $("select[id*='tipoPago']").parent().find(".select2-chosen").text("");
            $("input[id*='nombreTarifa']").parent().parent().hide();
            $("input[id*='nombreTarifa']").val("");
            $("input[id*='precioCalculado']").parent().parent().hide();
            $("input[id*='precioCalculado']").val("");
            $("select[id*='facturaGenerada']").parent().parent().parent().parent().hide();
            $("select[id*='facturaGenerada']").val("");
            $("select[id*='facturaGenerada']").parent().find(".select2-chosen").text("");
        }else if($.trim(tipoDocumento) === "1"){ //Factura
            $("select[id*='autorizacionCortesia']").parent().parent().hide();
            $("select[id*='autorizacionCortesia']").val("");
            $("select[id*='autorizacionCortesia']").parent().find(".select2-chosen").text("");
            $("select[id*='autorizacionInterna']").parent().parent().hide();
            $("select[id*='autorizacionInterna']").val("");
            $("select[id*='autorizacionInterna']").parent().find(".select2-chosen").text("");
            $("select[id*='tipoPago']").parent().parent().parent().parent().show();
            $("input[id*='nombreTarifa']").parent().parent().show();
            $("input[id*='precioCalculado']").parent().parent().show();
            $("select[id*='facturaGenerada']").parent().parent().parent().parent().show();
        }else if($.trim(tipoDocumento) === "2"){ //Por Cobrar
            $("select[id*='autorizacionCortesia']").parent().parent().hide();
            $("select[id*='autorizacionCortesia']").val("");
            $("select[id*='autorizacionCortesia']").parent().find(".select2-chosen").text("");
            $("select[id*='autorizacionInterna']").parent().parent().hide();
            $("select[id*='autorizacionInterna']").val("");
            $("select[id*='autorizacionInterna']").parent().find(".select2-chosen").text("");
            $("select[id*='tipoPago']").parent().parent().parent().parent().show();
            $("input[id*='nombreTarifa']").parent().parent().show();
            $("input[id*='precioCalculado']").parent().parent().show();
            $("select[id*='facturaGenerada']").parent().parent().parent().parent().show();
        }else if($.trim(tipoDocumento) === "3"){ //Autorización Cortesía 
            $("select[id*='autorizacionCortesia']").parent().parent().show();
            $("select[id*='autorizacionInterna']").parent().parent().hide();
            $("select[id*='autorizacionInterna']").val("");
            $("select[id*='autorizacionInterna']").parent().find(".select2-chosen").text("");
            $("select[id*='tipoPago']").parent().parent().parent().parent().hide();
            $("select[id*='tipoPago']").val("");
            $("select[id*='tipoPago']").parent().find(".select2-chosen").text("");
            $("input[id*='nombreTarifa']").parent().parent().hide();
            $("input[id*='nombreTarifa']").val("");
            $("input[id*='precioCalculado']").parent().parent().hide();
            $("input[id*='precioCalculado']").val("");
            $("select[id*='facturaGenerada']").parent().parent().parent().parent().hide();
            $("select[id*='facturaGenerada']").val("");
            $("select[id*='facturaGenerada']").parent().find(".select2-chosen").text("");
        }else if($.trim(tipoDocumento) === "4"){ //Autorización Interna 
            $("select[id*='autorizacionCortesia']").parent().parent().hide();
            $("select[id*='autorizacionCortesia']").val("");
            $("select[id*='autorizacionCortesia']").parent().find(".select2-chosen").text("");
            $("select[id*='autorizacionInterna']").parent().parent().show();
            $("select[id*='tipoPago']").parent().parent().parent().parent().hide();
            $("select[id*='tipoPago']").val("");
            $("select[id*='tipoPago']").parent().find(".select2-chosen").text("");
            $("input[id*='nombreTarifa']").parent().parent().hide();
            $("input[id*='nombreTarifa']").val("");
            $("input[id*='precioCalculado']").parent().parent().hide();
            $("input[id*='precioCalculado']").val("");
            $("select[id*='facturaGenerada']").parent().parent().parent().parent().hide();
            $("select[id*='facturaGenerada']").val("");
            $("select[id*='facturaGenerada']").parent().find(".select2-chosen").text("");
        }  
    },
    
    setPrecioCalculado : function() {
        console.debug("setPrecioCalculado-init");
        //  ------------- OBLIGATORIOS ------------------------
        var tipoEncomienda = $("select[id*='tipoEncomienda']").val();
        var idCliente = $("select[id*='clienteRemitente']").val();
        var cantidad = $("input[id*='cantidad']").val();
        var tipoDocumento = $("select[id*='tipoDocumento']").val();
        //  ------------- OPCIONALES ------------------------
        var tipoEncomiendaEspecial = $("select[id*='tipoEncomiendaEspecial']").val();
        var volumen = $("input[id*='volumen']").val();
        
        if(tipoEncomienda === null || $.trim(tipoEncomienda) === "" ||
           idCliente === null || $.trim(idCliente) === "" ||
           cantidad === null || $.trim(cantidad) === "" || 
           tipoDocumento === null || $.trim(tipoDocumento) === "" ||
           (tipoDocumento === "3" || tipoDocumento === "4") ||
           (tipoEncomienda === "2" && (tipoEncomiendaEspecial === null || $.trim(tipoEncomiendaEspecial) === "")) ||
           (tipoEncomienda === "3" && (volumen === null || $.trim(volumen) === ""))){
           $(".idTarifa").val("");
           $(".nombreTarifa").val("");
           $(".precioCalculado").val("");
        }else{
           var data = {
               tipoEncomienda : tipoEncomienda,
               cantidad : cantidad,
               idCliente : idCliente,
               tipoEncomiendaEspecial : tipoEncomiendaEspecial,
               volumen : volumen
           };
           $.ajax({
                url: $(".calcularPrecioPath").val(),
                type: "POST",
                data: data,
                success: function(json){
                    $(".idTarifa").val(json.id);
                    $(".nombreTarifa").val(json.nombre);
                    $(".precioCalculado").val(json.precio);
                    if(json.error !== ""){
                        alert(json.error);
                    }
                }
            });
        }
    }
    

}