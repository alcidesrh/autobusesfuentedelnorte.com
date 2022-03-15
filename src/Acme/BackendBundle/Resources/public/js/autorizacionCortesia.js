autorizacionCortesia = {
    
     funcionesAddOnload : function() {
        console.debug("autorizacionCortesia.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("autorizacionCortesia.funcionesAddOnload-end");
    },
			
    _init : function() {
       
       var href = $(".generatePinHidden").val();
       var html = $('<a class="btn sonata-action-element changePin" href="'+ href +'"><i class="icon-refresh"></i>Cambiar pin</div></a>');
       $("input[id*='codigo']").parent().append(html);
       this.checkServicioEstacion();
       this.checkDisabledSalida();       
     },
    
    _conectEvents : function() {
        
        $(".changePin").bind("click", this.changePin);
        $("select[id*='servicioEstacion']").change(function() {
             autorizacionCortesia.checkServicioEstacion($(this));
        }); 
        $("select[id*='restriccionFechaUso_day']").change(function () {
             autorizacionCortesia.loadSalidas();
        });
        $("select[id*='restriccionFechaUso_month']").change(function () {
             autorizacionCortesia.loadSalidas();
        });
        $("select[id*='restriccionFechaUso_year']").change(function () {
             autorizacionCortesia.loadSalidas();
        });
        $("select[id*='restriccionEstacionOrigen']").change(function () {
             autorizacionCortesia.loadSalidas();
        });
        $("select[id*='restriccionEstacionDestino']").change(function () {
             autorizacionCortesia.loadSalidas();
        });
        $("select[id*='restriccionSalida']").change(function () {
             autorizacionCortesia.loadAsientos();
        });
        $("select[id*='restriccionClaseAsiento']").change(function () {
             autorizacionCortesia.loadAsientos();
        });
    },
    
    checkServicioEstacion : function(item) {
        console.debug("checkClaseAsiento-init");
        if(!item){
           item = $("select[id*='servicioEstacion']");
        }else{
           item = $(item);
        }
       
        var idServicioEstacion =  item.val();
                
        var itemClaseAsiento = $("select[id*='restriccionClaseAsiento']");
        if($.trim(idServicioEstacion) === "2" ){
            itemClaseAsiento.val("");
            itemClaseAsiento.parent().find(".select2-chosen").text("");
            itemClaseAsiento.parent().parent().parent().parent().hide();            
        }else{
            itemClaseAsiento.parent().parent().parent().parent().show();
        }
        
        var itemClaseAsiento = $("select[id*='restriccionSalida']");
        if($.trim(idServicioEstacion) === "2" ){
            itemClaseAsiento.val("");
            itemClaseAsiento.parent().find(".select2-chosen").text("");
            itemClaseAsiento.parent().parent().hide();            
        }else{
            itemClaseAsiento.parent().parent().show();
        }
        
        var itemClaseAsiento = $("select[id*='restriccionAsientoBus']");
        if($.trim(idServicioEstacion) === "2" ){
            itemClaseAsiento.val("");
            itemClaseAsiento.parent().find(".select2-chosen").text("");
            itemClaseAsiento.parent().parent().hide();            
        }else{
            itemClaseAsiento.parent().parent().show();
        }
        
        var itemClaseAsiento = $(".idBoleto");
        if($.trim(idServicioEstacion) === "2" ){
            itemClaseAsiento.parent().parent().hide();            
        }else{
            itemClaseAsiento.parent().parent().show();
        }
        
    },
    
    changePin : function(event) {
        console.debug("changePin-init");
        $.get($(this).attr("href") , function( data ) {
            $( "input[id*='codigo']" ).val(data);
        });
        event.preventDefault();
        console.debug("changePin-end");
    },
    
    loadSalidas: function() {
       console.debug("loadSalidas-init");
       autorizacionCortesia.checkDisabledSalida(true);
       var day = $("select[id*='restriccionFechaUso_day']").val();
       var month = $("select[id*='restriccionFechaUso_month']").val();
       var year = $("select[id*='restriccionFechaUso_year']").val();
       var estacionOrigen = $("select[id*='restriccionEstacionOrigen']").val();
       var estacionDestino = $("select[id*='restriccionEstacionDestino']").val();
       if(day === null || $.trim(day) === "" || month === null || $.trim(month) === "" || year === null || $.trim(year) === "" || 
               estacionOrigen === null || $.trim(estacionOrigen) === "" || estacionDestino === null || $.trim(estacionDestino) === ""){
           return;
       }
       data = {
           'day':day,
           'month':month,
           'year':year,
           'estacionOrigen':estacionOrigen,
           'estacionDestino':estacionDestino
       };
       $.ajax({
            type: "POST",
            data: data,
            url: $(".listarSalidasPath").val(),
            success: function(msg){
                autorizacionCortesia.checkDisabledSalida();
                $("select[id*='restriccionSalida']").html(msg);
                $("select[id*='restriccionSalida']").val("");
            }
        });
    },

    loadAsientos: function() {
       console.debug("loadAsientos-init");
       autorizacionCortesia.checkDisabledAsiento(true);
       var restriccionSalida = $("select[id*='restriccionSalida']").val();
       var restriccionClaseAsiento = $("select[id*='restriccionClaseAsiento']").val();
       if(restriccionSalida === null || $.trim(restriccionSalida) === "" || restriccionClaseAsiento === null || $.trim(restriccionClaseAsiento) === ""){
           return;
       }
       data = {
           'salida':restriccionSalida,
           'claseAsiento':restriccionClaseAsiento
       };
       $.ajax({
            type: "POST",
            data: data,
            url: $(".listaAsientosDisponiblesBySalidaPath").val(),
            success: function(msg){
                autorizacionCortesia.checkDisabledAsiento();
                $("select[id*='restriccionAsientoBus']").html(msg);
                $("select[id*='restriccionAsientoBus']").val("");
            }
        });
    },
    
    checkDisabledSalida : function(forceDisable) {
       var servicioEstacion = $("select[id*='servicioEstacion']").val();
       var day = $("select[id*='restriccionFechaUso_day']").val();
       var month = $("select[id*='restriccionFechaUso_month']").val();
       var year = $("select[id*='restriccionFechaUso_year']").val();
       var estacionOrigen = $("select[id*='restriccionEstacionOrigen']").val();
       var estacionDestino = $("select[id*='restriccionEstacionDestino']").val();
       if(forceDisable === true 
               || servicioEstacion === null || $.trim(servicioEstacion) === "" || $.trim(servicioEstacion) === "2"
               || day === null || $.trim(day) === ""
               || month === null || $.trim(month) === "" || year === null || $.trim(year) === ""
               || estacionOrigen === null || $.trim(estacionOrigen) === "" || estacionDestino === null || $.trim(estacionDestino) === ""){
           $("select[id*='restriccionSalida']").html("");
           $("select[id*='restriccionSalida']").val("");
           $("select[id*='restriccionSalida']").parent().find(".select2-chosen").text("");
           $("select[id*='restriccionSalida']").parent().parent().hide();
       }else{
           $("select[id*='restriccionSalida']").parent().parent().show();
       }
       autorizacionCortesia.checkDisabledAsiento();
    },
    
    checkDisabledAsiento : function(forceDisable) {
       var restriccionSalida = $("select[id*='restriccionSalida']").val();
       var restriccionClaseAsiento = $("select[id*='restriccionClaseAsiento']").val();
       if(forceDisable === true || restriccionSalida === null || $.trim(restriccionSalida) === "" 
               || restriccionClaseAsiento === null || $.trim(restriccionClaseAsiento) === ""){
           $("select[id*='restriccionAsientoBus']").html("");
           $("select[id*='restriccionAsientoBus']").val("");
           $("select[id*='restriccionAsientoBus']").parent().find(".select2-chosen").text("");
           $("select[id*='restriccionAsientoBus']").parent().parent().hide();
       }else{
           $("select[id*='restriccionAsientoBus']").parent().parent().show();
       }
       
   }
}