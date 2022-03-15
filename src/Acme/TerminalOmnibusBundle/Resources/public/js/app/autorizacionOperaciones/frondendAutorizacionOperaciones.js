frondendAutorizacionOperaciones = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: new Date(),
            dateLimit : moment.duration(6, 'months')
        });
        $('#rangoFecha').data('daterangepicker').clickApply();
        
        // -------------------  FILTROS END ------------------
        // -------------------  GRID INIT ------------------
        $("#grid").flexigrid({
            url: $("#grid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: "",
            rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
            colModel : [
                    {display: 'ID', name : 'id', width : 60, sortable : false, align: 'center'},
                    {display: 'Empresa', name : 'empresa', width : 90, sortable : false, align: 'center'},
                    {display: 'Fecha', name : 'fecha', width : 110, sortable : false, align: 'center'},
                    {display: 'Estación', name : 'estacion', width : 150, sortable : false, align: 'left'},
                    {display: 'Boleto', name : 'idBoleto', width : 80, sortable : false, align: 'center'},
                    {display: 'Tipo', name : 'tipo', width : 150, sortable : false, align: 'left'},
                    {display: 'Estado', name : 'estado', width : 90, sortable : false, align: 'left'},
                    {display: 'Usuario', name : 'usuario', width : 100, sortable : false, align: 'left'},
                    {display: 'Motivo', name : 'motivo', width : 350, sortable : false, align: 'left'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            height: 300,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(frondend.linkFilter);
        $("li a.menuAutorizacionOperaciones").click(frondend.loadSubPage);
        
    }
    
};
