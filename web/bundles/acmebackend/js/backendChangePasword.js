backendChangePasword = {
    
    funcionesAddOnload : function() {
        console.debug("backendChangePasword.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("backendChangePasword.funcionesAddOnload-end");
    },
			
    _init : function() {
        

    },
    
    _conectEvents : function() {
        
        $('.changePasswordForm').on('submit', function(e) {
                e.preventDefault(); // <-- important
//                var varx = $(".changePasswordForm").data("varX");
                    
                $(this).ajaxSubmit({
//                    data:{'pos':pos},
                    success : function(success) {
                        console.debug("changePasswordForm-success-init");
//                        console.debug(success);
                        try {
                            console.debug(success);
//                            var json = JSON.parse(success);
//                            console.debug(json);
                            console.debug(success.result);
                            if(success.result === "ok"){
                                console.debug("cerrando dialog...");
                                $("#cambiarPasswordDiv").dialog2("close");
                                return;
                            }
                        }catch (err){}
                        
                        var component = document.createElement("div");
                        component.innerHTML = success;
                        var listDiv = component.getElementsByTagName('div');
                        console.debug(listDiv);
                        console.debug($(listDiv));
                        var bodyFormDiv = $(listDiv[0]);
                        console.debug(bodyFormDiv);
                        if(bodyFormDiv) $(".bodyForm").html(bodyFormDiv);
                        console.debug("changePasswordForm-success-end");                          
                    },
                    error : function(jqXHR) {
                        console.debug("error");
                        console.debug(jqXHR);
                        alert("Ha ocurrido un error.")
                    }
                });
            });
         
    },

    
}