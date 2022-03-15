perfil = {
    
    funcionesAddOnload : function() {
//        console.debug("perfil.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("perfil.funcionesAddOnload-end");
    },
			
    _init : function() {
        
          $("#cambiarContrasena").click(frondend.loadSubPage);
          $("#cambiarEstacion").click(function(e) {
              frondend.loadSubPage(e, $(this), function(name) {
                  $("#infoEstacionUsuario").text(name); 
                  $("a.perfil").click();
              });
          });
     },
     
    _conectEvents : function() {
        
       
    }
    
};