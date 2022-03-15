frondendBoletos = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: core.modDate(+30),
            dateLimit : moment.duration(6, 'months')
        });
        $('#rangoFecha').data('daterangepicker').clickApply();
        
        $("#clienteDocumento").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#clienteDocumento").data("pathlistarclientespaginando"),
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
        $("#clienteBoleto").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#clienteBoleto").data("pathlistarclientespaginando"),
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
                    {display: 'Fecha Salida', name : 'fecha', width : 110, sortable : true, align: 'center'},
                    {display: 'Número', name : 'numeroAsiento', width : 80, sortable : true, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 100, sortable : false, align: 'left'},
                    {display: 'Documento', name : 'tipoDocumento', width : 150, sortable : false, align: 'left'},
                    {display: 'Ruta', name : 'ruta', width : 300, sortable : true, align: 'left'},
                    {display: 'Respetar', name : 'utilizarDesdeEstacionOrigenSalida', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Sube En', name : 'estacionOrigen', width : 150, sortable : false, align: 'left'},
                    {display: 'Baja En', name : 'estacionDestino', width : 150, sortable : false, align: 'left'},
                    {display: 'Baja En (Obs)', name : 'observacionDestinoIntermedio', width : 50, sortable : false, align: 'left'},
                    {display: 'Clase', name : 'claseAsiento', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Ref. Externa', name : 'referenciaExterna', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Autorización Tarjeta', name : 'autorizacionTarjeta', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Cliente Documento', name : 'clienteDocumento', width : 300, sortable : true, align: 'left'},
                    {display: 'Cliente Boleto', name : 'clienteBoleto', width : 300, sortable : true, align: 'left'},
                    {display: 'Tipo Pago', name : 'tipoPago', width : 80, sortable : true, align: 'left'},
                    {display: 'Precio Documento', name : 'precioCalculado', width : 100, sortable : false, align: 'left'},
                    {display: 'Precio Moneda Base', name : 'precioCalculadoMonedaBase', width : 150, sortable : false, align: 'left'},
//                    {display: 'En Camino', name : 'revendidoEnCamino', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Reasignado', name : 'reasignado', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Fecha Creación', name : 'fechaCreacion', width : 100, sortable : true, align: 'center'},
                    {display: 'Usuario Creación', name : 'usuarioCreacion', width : 100, sortable : true, align: 'left'},
                    {display: 'Estación Creación', name : 'estacionCreacion', width : 100, sortable : true, align: 'left'}
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
        $("li a.menuBoletos").click(frondend.loadSubPage);
        
    }
    
};
