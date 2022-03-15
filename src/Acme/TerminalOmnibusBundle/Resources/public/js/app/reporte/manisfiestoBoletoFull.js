manisfiestoBoletoFull = {
    
    funcionesAddOnload : function() {
//        console.debug("manisfiestoBoletoFull.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("manisfiestoBoletoFull.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $("#manisfiestoBoletoFull_salida").select2({
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
        
//        $("#manisfiestoBoletoFull_estacion").select2({
//            allowClear: $("#manisfiestoBoletoFull_estacion option[value='']").length === 1
//        });
//        $("#manisfiestoBoletoFull_estacion").select2("readonly", ($("#manisfiestoBoletoFull_estacion").attr("readonly") === "readonly"));
     },
     
    _conectEvents : function() {

        
    }
};
