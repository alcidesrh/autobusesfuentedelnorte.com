frondendAlquiler = {
    
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
                    {display: 'ID', name : 'id', width : 70, sortable : false, align: 'center'},
                    {display: 'Fecha Inicial', name : 'fechaInicial', width : 100, sortable : false, align: 'center'},
                    {display: 'Fecha Final', name : 'fechaFinal', width : 100, sortable : false, align: 'center'},
                    {display: 'Empresa', name : 'empresa', width : 100, sortable : false, align: 'center'},
                    {display: 'Bus', name : 'bus', width : 100, sortable : false, align: 'center'},
                    {display: 'Piloto1', name : 'piloto1', width : 100, sortable : false, align: 'left'},
                    {display: 'Piloto2', name : 'piloto2', width : 100, sortable : false, align: 'left'},
                    {display: 'Estado', name : 'estado', width : 100, sortable : false, align: 'center'},
                    {display: 'Importe', name : 'importe', width : 100, sortable : false, align: 'left'},
                    {display: 'Descripción', name : 'descripcion', width : 200, sortable : false, align: 'left'}
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
        $("li a.menuAlquiler").click(frondend.loadSubPage);
        
    }
    
    
};
