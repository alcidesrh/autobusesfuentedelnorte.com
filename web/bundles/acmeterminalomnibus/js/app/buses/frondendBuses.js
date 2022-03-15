frondendBuses = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendBuses.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendBuses.funcionesAddOnload-end");
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
                    {display: 'Código', name : 'id', width : 50, sortable : false, align: 'center'},
                    {display: 'Empresa', name : 'empresa', width : 100, sortable : false, align: 'left'},
                    {display: 'Placa', name : 'placa', width : 60, sortable : false, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 100, sortable : false, align: 'center'},
                    {display: 'Tipo', name : 'tipo', width : 200, sortable : false, align: 'left'}
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
        $("li a.menuBuses").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
