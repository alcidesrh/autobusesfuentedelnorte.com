backendListUser = {
    
    funcionesAddOnload : function() {
        console.debug("backendListUser.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("backendListUser.funcionesAddOnload-end");
    },
			
    _init : function() {
        

    },
    
    _conectEvents : function() {
        
        $('.changePassword').bind("click", this.changePassword);
         
    },
    
    changePassword : function(event) {
        console.debug("changePassword-clic");
        console.debug($(this));
        console.debug($(this).data("href"));
        $('<div/>').dialog2({
            closeOnOverlayClick : false, // Should the dialog be closed on overlay click?
            closeOnEscape : false, // Should the dialog be closed if [ESCAPE] key is pressed?
            removeOnClose : true, // Should the dialog be removed from the document when it is closed?
            showCloseHandle : true, // Should a close handle be shown?
            initialLoadText: "Cargando datos...",
            title: "Cambiar contraseña", 
            content: $(this).data("href"), 
            id: "cambiarPasswordDiv"
        });
        event.preventDefault();
    },
    
}