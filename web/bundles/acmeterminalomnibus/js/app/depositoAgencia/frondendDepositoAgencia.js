frondendDepositoAgencia = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendAlquiler.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendAlquiler.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
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
                    {display: 'ID', name : 'id', width : 70, sortable : false, align: 'center', hide: true},
                    {display: 'Fecha', name : 'fecha', width : 100, sortable : false, align: 'center'},
                    {display: 'Agencia', name : 'estacion', width : 150, sortable : false, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 80, sortable : false, align: 'center'},
                    {display: 'Importe', name : 'importe', width : 100, sortable : false, align: 'left'},
                    {display: 'Aplica Bono', name : 'aplicaBono', width : 80, sortable : false, align: 'left'},
                    {display: 'Boleta', name : 'numeroBoleta', width : 100, sortable : false, align: 'left'},
                    {display: 'Creado', name : 'fechaCreacion', width : 100, sortable : false, align: 'center'},
                    {display: 'Usuario', name : 'usuarioCreacion', width : 100, sortable : false, align: 'left'},
                    {display: 'Observación', name : 'observacion', width : 200, sortable : false, align: 'left', hide: true},
                    {display: 'Motivo Rechazo', name : 'motivoRechazo', width : 200, sortable : false, align: 'left'}
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
        $("li a.menuDepositoAgencia").click(frondend.loadSubPage);
        
    }
    
    
};
