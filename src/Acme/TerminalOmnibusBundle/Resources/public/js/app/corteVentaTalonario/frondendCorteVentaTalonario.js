frondendCorteVentaTalonario = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
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
                    {display: 'Tarjeta', name : 'tarjeta', width : 100, sortable : false, align: 'center'},
                    {display: 'Fecha', name : 'fecha', width : 120, sortable : true, align: 'center'},
                    {display: 'Inicio', name : 'inicio', width : 100, sortable : false, align: 'center'},
                    {display: 'Fin', name : 'fin', width : 100, sortable : false, align: 'center'},
                    {display: 'Cantidad', name : 'cantidad', width : 100, sortable : false, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 100, sortable : false, align: 'center'}
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
        $("li a.menuCorteVentaTalonarios").click(frondend.loadSubPage);
        
    }
    
    
};
