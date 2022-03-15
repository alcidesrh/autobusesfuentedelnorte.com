manisfiestoBoletoPiloto = {
    
    funcionesAddOnload : function() {
//        console.debug("manisfiestoBoletoPiloto.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("manisfiestoBoletoPiloto.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $("#manisfiestoBoletoPiloto_salida").select2({
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
        
//        $("#manisfiestoBoletoPiloto_estacion").select2({
//            allowClear: $("#manisfiestoBoletoPiloto_estacion option[value='']").length === 1
//        });
//        $("#manisfiestoBoletoPiloto_estacion").select2("readonly", ($("#manisfiestoBoletoPiloto_estacion").attr("readonly") === "readonly"));
     },
     
    _conectEvents : function() {

        
    }
};
