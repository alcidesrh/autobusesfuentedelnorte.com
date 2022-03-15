configurarCustomWeb = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
       $("#configuracionEstacion").select2({
            allowClear: true,
            data: []
        });
        configurarCustomWeb.loadEstaciones();
        
        if(typeof(jsPrintSetup) !== 'undefined'){
            $("#listaImpresoras").val(jsPrintSetup.getPrintersList());
        }
         
     },
     
     loadEstaciones :function() {
        core.request({
            url : $("#pathloadEstaciones").prop("value"),
            method: "GET",
            dataType: "json",
            async: true,
            showLoading: false,
            successCallback: function(data){
                $("#configuracionEstacion").select2({
                    allowClear: true,
                    data: { results: data.optionEstaciones }
                });
                $("#configuracionEstacion").select2('val', localStorage.getItem('est-id'));
            }
        });
    },
    
    _conectEvents : function() {
        
       $("#configuracionEstacionLink").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var data = $("#configuracionEstacion").select2("data");
            if(data){
                localStorage.setItem('est-id', data.id);
                localStorage.setItem('est-name', data.text);
            }else{
                localStorage.setItem('est-id', "");
                localStorage.setItem('est-name', "");
            }
            alert("Operación realizada satisfactoriamente.");
        });
        
    }
    
};
