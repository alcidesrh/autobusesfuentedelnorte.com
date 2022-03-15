listarDesembarcarMovil = {
    
    funcionesAddOnload : function() {
//        console.debug("listarDesembarcarMovil.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("listarDesembarcarMovil.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $("#listar_desembarcar_command_salida").select2({
            allowClear: false
        });
        
        $("#encomiedasGrid").flexigrid({
            url: $("#encomiedasGrid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: listarDesembarcarMovil.getQueryString(),
            rpOptions: [100],
            colModel : [
                    {display: 'ID', name : 'id', width : 70, sortable : false, align: 'center'},
                    {display: 'Grupo', name : 'idPadre', width : 100, sortable : false, align: 'center'},
                    {display: 'Tipo', name : 'tipoEncomienda', width : 100, sortable : false, align: 'left'},
                    {display: 'Estaciones', name : 'estaciones', width : 150, sortable : false, align: 'left'},
                    {display: 'Remitente', name : 'clienteRemitente', width : 150, sortable : false, align: 'left'},
                    {display: 'Destinatario', name : 'clienteDestinatario', width : 150, sortable : false, align: 'left'},
                    {display: 'Descripción', name : 'descripcion', width : 200, sortable : false, align: 'left'},
                    ],
            usepager: true,
            useRp: true,
            rp: 100,
            showTableToggleBtn: false,
            height: 400
        });
     },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
        
        $("#listar_desembarcar_command_salida").on("change", listarDesembarcarMovil.buscarEncomiendasPendientes);
    },
    
    buscarEncomiendasPendientes: function() {
        $("#encomiedasGrid").flexOptions({
            newp: 1, 
            query: listarDesembarcarMovil.getQueryString()
        }).flexReload();
    },
    
    getQueryString : function() {
        var parametros = $('#listar_desembarcar_command_salida').fieldSerialize();
        parametros = parametros.replace("listar_desembarcar_command%5Bsalida%5D","salida"); 
        return parametros;
    }
};
