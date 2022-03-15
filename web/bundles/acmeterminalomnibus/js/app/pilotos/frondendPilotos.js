frondendPilotos = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendPilotos.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendPilotos.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        
        // -------------------  FILTROS END ------------------
        // -------------------  GRID INIT ------------------
        $("#grid").flexigrid({
            url: $("#grid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: "",
            rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
            colModel : [
                    {display: 'Código', name : 'codigo', width : 45, sortable : false, align: 'center'},
                    {display: 'Nombre Completo', name : 'nombre', width : 150, sortable : false, align: 'left'},
                    {display: 'Nacionalidad', name : 'nacionalidad', width : 100, sortable : false, align: 'left'},
                    {display: 'DPI', name : 'dpi', width : 100, sortable : false, align: 'center'},
                    {display: 'Seguro Social', name : 'seguroSocial', width : 100, sortable : false, align: 'center'},
                    {display: 'Teléfono', name : 'telefono', width : 100, sortable : false, align: 'center'},
                    {display: 'Licencia', name : 'numeroLicencia', width : 100, sortable : false, align: 'center'},
                    {display: 'Vencimiento', name : 'fechaVencimientoLicencia', width : 80, sortable : false, align: 'center'},
                    {display: 'Sexo', name : 'sexo', width : 50, sortable : false, align: 'center'},
                    {display: 'Empresa', name : 'empresa', width : 100, sortable : false, align: 'center'},
                    {display: 'Fecha Nacimiento', name : 'fechaNacimiento', width : 100, sortable : false, align: 'center'},
                    {display: 'Activo', name : 'activo', width : 40, sortable : false, align: 'center'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(frondend.linkFilter);
        $("li a.menuPilotos").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
