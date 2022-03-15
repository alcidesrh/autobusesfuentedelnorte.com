manisfiestoEncomiendaFull = {
    
    funcionesAddOnload : function() {
//        console.debug("manisfiestoEncomiendaFull.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("manisfiestoEncomiendaFull.funcionesAddOnload-end");
    },
			
    _init : function() {
        
         $("#manisfiestoEncomiendaFull_salida").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#pathListarSalidasPaginando").val(),
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 10};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        if(!core.isMovil()){
            $("#manisfiestoEncomiendaFull_estacionOrigen").select2({
                allowClear: $("#manisfiestoEncomiendaFull_estacionOrigen option[value='']").length === 1
            });
            $("#manisfiestoEncomiendaFull_estacionOrigen").select2("readonly", ($("#manisfiestoEncomiendaFull_estacionOrigen").attr("readonly") === "readonly"));

            $("#manisfiestoEncomiendaFull_estacionDestino").select2({
                allowClear: $("#manisfiestoEncomiendaFull_estacionDestino option[value='']").length === 1
            });
            $("#manisfiestoEncomiendaFull_estacionDestino").select2("readonly", ($("#manisfiestoEncomiendaFull_estacionDestino").attr("readonly") === "readonly"));
        }
     },
     
    _conectEvents : function() {

        
    }
};
