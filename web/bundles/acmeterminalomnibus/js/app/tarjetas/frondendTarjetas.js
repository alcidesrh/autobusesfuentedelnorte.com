frondendTarjetas = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendAlquiler.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendAlquiler.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        // -------------------  FILTROS END ------------------
        // -------------------  GRID INIT ------------------
        $("#grid").flexigrid({
            url: $("#grid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: "",
            rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
            colModel : [
                    {display: 'ID', name : 'id', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Número', name : 'numero', width : 80, sortable : false, align: 'center'},
                    {display: 'Salida', name : 'salida', width : 80, sortable : false, align: 'center'},
                    {display: 'Fecha', name : 'fecha', width : 120, sortable : true, align: 'center'},
                    {display: 'Ruta', name : 'ruta', width : 180, sortable : false, align: 'left'},
                    {display: 'Empresa', name : 'empresa', width : 80, sortable : false, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 140, sortable : false, align: 'center'},
                    {display: 'Cantidad', name : 'cantidad', width : 70, sortable : false, align: 'center'},
                    {display: 'Fecha Creación', name : 'fechaCreacion', width : 100, sortable : true, align: 'center'},
                    {display: 'Usuario Creación', name : 'usuarioCreacion', width : 100, sortable : true, align: 'left'},
                    {display: 'Estación Creación', name : 'estacionCreacion', width : 100, sortable : true, align: 'left'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            height: 320,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(frondend.linkFilter);
        $("li a.menuTarjetas").click(frondend.loadSubPage);
        
    }
    
    
};
