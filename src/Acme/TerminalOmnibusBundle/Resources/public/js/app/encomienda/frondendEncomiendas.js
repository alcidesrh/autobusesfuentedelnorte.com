frondendEncomiendas = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendEncomiendas.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendEncomiendas.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#rangoFechaCreacion').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(120, 'd'),
//            startDate: new Date(),
//            endDate: new Date()
        });
        
        $("#clienteRemitente").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#clienteRemitente").data("pathlistarclientespaginando"),
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        $("#clienteDestinatario").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#clienteDestinatario").data("pathlistarclientespaginando"),
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
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
                    {display: 'ID', name : 'id', width : 60, sortable : true, align: 'center'},
                    {display: 'Fecha Creación', name : 'fechaCreacion', width : 110, sortable : true, align: 'center'},
                    {display: 'Empresa', name : 'empresa', width : 60, sortable : false, align: 'center'},
                    {display: 'Tipo Encomienda', name : 'tipoEncomienda', width : 100, sortable : false, align: 'left'},
                    {display: 'Cantidad', name : 'cantidad', width : 70, sortable : true, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 100, sortable : false, align: 'left'},
                    {display: 'Bus', name : 'bus', width : 50, sortable : false, align: 'center'},
                    {display: 'Documento', name : 'tipoDocumento', width : 120, sortable : false, align: 'left'},
                    {display: 'Origen', name : 'estacionOrigen', width : 150, sortable : false, align: 'left', hide: true},
                    {display: 'Destino', name : 'estacionDestino', width : 150, sortable : false, align: 'left', hide: true},
                    {display: 'Estaciones', name : 'estaciones', width : 200, sortable : false, align: 'left'},
//                    {display: 'Ruta', name : 'ruta', width : 300, sortable : true, align: 'left'},
                    {display: 'Cliente Remitente', name : 'clienteRemitente', width : 300, sortable : true, align: 'left'},
                    {display: 'Cliente Destinatario', name : 'clienteDestinatario', width : 300, sortable : true, align: 'left'},
                    {display: 'Códigos', name : 'codigoExternoCliente', width : 70, sortable : false, align: 'left'},
                    {display: 'Boleto', name : 'boleto', width : 100, sortable : true, align: 'center', hide: true},
                    {display: 'Descripción', name : 'descripcion', width : 300, sortable : false, align: 'left', hide: true},
//                    {display: 'Precio Real', name : 'precioCalculado', width : 100, sortable : false, align: 'left'},
//                    {display: 'Precio Moneda Base', name : 'precioCalculadoMonedaBase', width : 150, sortable : false, align: 'left'}
                    {display: 'Precio', name : 'precioCalculadoMonedaBase', width : 150, sortable : false, align: 'left'}
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
        $("li a.menuEncomiendas").click(frondend.loadSubPage);
        
    }
    
    
};
