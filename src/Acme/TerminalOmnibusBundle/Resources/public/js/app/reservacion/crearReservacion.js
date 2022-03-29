crearReservacion = {
  funcionesAddOnload: function () {
    //        console.debug("crearReservacion.funcionesAddOnload-init");
    this._init();
    this._conectEvents();
    //	console.debug("crearReservacion.funcionesAddOnload-end");
  },

  _init: function () {
    var pathlistarclientespaginando = $("#clienteBase").data(
      "pathlistarclientespaginando"
    );
    $("#clienteBase").select2({
      minimumInputLength: 1,
      allowClear: true,
      ajax: {
        url: pathlistarclientespaginando,
        dataType: "json",
        type: "POST",
        data: function (term, page) {
          return { term: term, page_limit: 5 };
        },
        results: function (data, page) {
          return { results: data.options };
        },
      },
    });

    $("#crear_reservacion_command_fechaSalida").datepicker({
      format: "dd/mm/yyyy",
      startDate: "d",
      endDate: "+2m",
      todayBtn: true,
      language: "es",
      autoclose: true,
      todayHighlight: true,
    });

    $("#crear_reservacion_command_estacionOrigen").select2({
      allowClear: true,
    });

    $("#salidaGrid").flexigrid({
      url: $("#salidaGrid").data("url"),
      dataType: "json",
      singleSelect: true,
      query: crearReservacion.getQueryString(),
      rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
      colModel: [
        {
          display: "ID",
          name: "id",
          width: 70,
          sortable: true,
          align: "center",
          hide: true,
        },
        {
          display: "Fecha y Hora",
          name: "fecha",
          width: 120,
          sortable: false,
          align: "center",
        },
        {
          display: "Origen",
          name: "origen",
          width: 120,
          sortable: false,
          align: "left",
        },
        {
          display: "Destino",
          name: "destino",
          width: 120,
          sortable: false,
          align: "left",
        },
        {
          display: "Empresa",
          name: "empresa",
          width: 80,
          sortable: false,
          align: "left",
        },
        //                    {display: 'Tipo Bus', name : 'tipoBus', width : 80, sortable : false, align: 'left'},
        //                    {display: 'Clase de Bus', name : 'claseBus', width : 80, sortable : false, align: 'left'},
        {
          display: "Itinerario",
          name: "itinerario",
          width: 130,
          sortable: false,
          align: "left",
        },
        {
          display: "Bus",
          name: "bus",
          width: 130,
          sortable: false,
          align: "left",
        },
        {
          display: "Piloto",
          name: "piloto",
          width: 130,
          sortable: false,
          align: "left",
          hide: true,
        },
        {
          display: "Estado",
          name: "estado",
          width: 70,
          sortable: false,
          align: "center",
        },
      ],
      usepager: true,
      useRp: true,
      rp: 15,
      showTableToggleBtn: false,
      height: 250,
      onSuccess: function () {
        $("#salidaGrid tr").on("click", crearReservacion.checkSelectedSalida);
        crearReservacion.checkSelectedSalida();
      },
    });

    crearReservacion.checkSelectedSalida();
  },

  _conectEvents: function () {
    $("#cancelar").click(function (e) {
      //            console.debug("clic");
      //            console.debug($(this));
      e.preventDefault();
      e.stopPropagation();
      confirm(
        "¿Está seguro que desea cancelar la operación?",
        function (confirmed) {
          //                console.debug(confirmed);
          if (confirmed === true) {
            core.getPageForMenu($("#cancelar").attr("href"));
          }
        }
      );
    });

    $("#addClienteBase").click(function (e) {
      frondend.loadSubPage(e, $(this), function (id) {
        var element = $("#clienteBase");
        if (id !== "") {
          core.request({
            url: element.data("pathlistarclientespaginando"),
            type: "POST",
            dataType: "json",
            async: false,
            extraParams: { id: id },
            successCallback: function (data) {
              if (data.options && data.options[0]) {
                $("#clienteBase").select2("data", data.options[0]);
              }
            },
          });
        }
      });
    });
    $("#updateClienteBase").click(function (e) {
      frondend.loadSubPage(e, $(this), function () {
        var element = $("#clienteBase");
        var id = element.val();
        if (id !== "") {
          core.request({
            url: element.data("pathlistarclientespaginando"),
            type: "POST",
            dataType: "json",
            async: false,
            extraParams: { id: id },
            successCallback: function (data) {
              if (data.options && data.options[0]) {
                $("#clienteBase").select2("data", data.options[0]);
              }
            },
          });
        }
      });
    });
    $("#seachClienteBase").click(function (e) {
      frondend.loadSubPage(e, $(this), function (id) {
        if (id !== "") {
          var element = $("#clienteBase");
          core.request({
            url: element.data("pathlistarclientespaginando"),
            type: "POST",
            dataType: "json",
            async: false,
            extraParams: { id: id },
            successCallback: function (data) {
              if (data.options && data.options[0]) {
                $("#clienteBase").select2("data", data.options[0]);
              }
            },
          });
        }
      });
    });

    $("#aceptar").click(crearReservacion.clickAceptar);
    $("#crear_reservacion_command_fechaSalida").on(
      "change",
      crearReservacion.changeFiltersSalida
    );
    $("#crear_reservacion_command_estacionOrigen").on(
      "change",
      crearReservacion.changeFiltersSalida
    );
  },

  checkSelectedSalida: function () {
    //        console.log("changeSelectedSalida-init");
    crearReservacion.clearItemGridReservaciones();
    var selected = core.getSelectedItemId("#salidaGrid");
    $("#crear_reservacion_command_salida").prop("value", selected);
    if (selected === null) {
      $("#listaAsientosHidden").val(JSON.stringify([]));
      $("#listaSenalesHidden").val(JSON.stringify([]));
      $("#listaBoletosHidden").val(JSON.stringify([]));
      $("#listaReservacionesHidden").val(JSON.stringify([]));
      $("#dependenciasSelecccionSalidaGrid").hide();
      crearReservacion.mostrarIconos();
    } else {
      core.request({
        url: $("#pathGetInformacionPorSalida").prop("value"),
        type: "POST",
        dataType: "json",
        async: false,
        extraParams: { idSalida: selected },
        successCallback: function (data) {
          //                    console.debug("Actulizando datos del combo...data");
          //                    console.debug(data);
          $("#dependenciasSelecccionSalidaGrid").show();

          if (data.optionListaAsientos) {
            $("#listaAsientosHidden").val(
              JSON.stringify(data.optionListaAsientos)
            );
          }

          if (data.optionListaSenales) {
            $("#listaSenalesHidden").val(
              JSON.stringify(data.optionListaSenales)
            );
          }

          if (data.optionBoletos) {
            $("#listaBoletosHidden").val(JSON.stringify(data.optionBoletos));
          }

          if (data.optionReservaciones) {
            $("#listaReservacionesHidden").val(
              JSON.stringify(data.optionReservaciones)
            );
          }

          crearReservacion.mostrarIconos();
        },
      });
    }
  },

  changeFiltersSalida: function () {
    $("#salidaGrid")
      .flexOptions({
        newp: 1,
        query: crearReservacion.getQueryString(),
      })
      .flexReload();
  },

  getQueryString: function () {
    return $(".filterSalida").fieldSerialize();
  },

  buscarEstadoAsiento: function (numero, lista) {
    //        console.debug("buscarEstadoAsiento asiento:" + numero);
    var estado = "libre";
    $.each(lista, function () {
      //           console.debug(this);
      if (this.numero === numero) {
        estado = crearReservacion.buscarEstadoAsientoItem(this);
      }
    });
    //        console.log("NRO:"+numero+", estado:"+estado);
    return estado;
  },

  buscarEstadoAsientoItem: function (item) {
    //        console.debug("buscarEstadoAsientoItem-init");
    //        console.debug(item);
    if (item.tipo === "R") {
      //            console.log("reservado:" + item.numero);
      return "reservado";
    } else if (item.tipo === "B") {
      if (
        item.tipoDocumento === 1 ||
        item.tipoDocumento === 2 ||
        item.tipoDocumento === 4 ||
        item.tipoDocumento === 5 ||
        item.tipoDocumento === 6 ||
        item.tipoDocumento === 7 ||
        item.tipoDocumento === 8
      ) {
        //                console.log("vendido:" + item.numero);
        return "vendido";
      } else if (item.tipoDocumento === 3) {
        //                console.log("cortesia:" + item.numero);
        return "cortesia";
      } else {
        throw new Error(
          "No se pudo determinar el tipo de documento del boleto con id:" +
            item.id +
            ", y numero:" +
            item.numero
        );
      }
    } else {
      throw new Error(
        "No se pudo determinar el tipo del asiento con id:" +
          item.id +
          ", y numero:" +
          item.numero
      );
    }
  },

  mostrarIconos: function () {
    //        console.debug("mostrarIconos...Item..");

    $(".item").remove(); //Elimino todos los item para generarlos nuevamente con los for.

    var listaBoletos = $("#listaBoletosHidden").val();
    if (listaBoletos) {
      listaBoletos = JSON.parse(listaBoletos);
    } else {
      listaBoletos = [];
    }
    var listaReservaciones = $("#listaReservacionesHidden").val();
    if (listaReservaciones) {
      listaReservaciones = JSON.parse(listaReservaciones);
    } else {
      listaReservaciones = [];
    }
    var all = listaBoletos.concat(listaReservaciones);

    $("#nav2").addClass("hidden");
    var asientosPosicionados = [];
    var listaAsientos = $("#listaAsientosHidden").val();
    if (listaAsientos) {
      listaAsientos = JSON.parse(listaAsientos);
    } else {
      listaAsientos = [];
    }
    jQuery.each(listaAsientos, function () {
      var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB
      if (clase === "1") {
        clase = "claseA";
      } else if (clase === "2") {
        clase = "claseB";
      } else {
        throw new Error(
          "El item debe tener uno de los id de clases siguientes: 1 o 2."
        );
      }
      asientosPosicionados[this.numero] = this;
      var estado = crearReservacion.buscarEstadoAsiento(this.numero, all);
      var item = $(".icono." + clase + "." + estado).clone();
      item.removeClass("ui-draggable icono");
      item.addClass("item");
      var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2
      if (eval(nivel2)) {
        $("#nav2").removeClass("hidden");
        $(".nivel2").append(item);
      } else {
        $(".nivel1").append(item);
      }
      item.css("left", core.ajustarPosicion(this.coordenadaX));
      item.css("top", core.ajustarPosicion(this.coordenadaY));
      this.jsId = core.uniqId();
      item.attr("jsId", this.jsId);
      var numero = this.numero;
      item.find(".detalle").text(this.numero);
      item.data("numero", this.numero);
      item.data("id", this.id);
      item.css("cursor", "hand");
    });
    $("#listaAsientosHidden").val(JSON.stringify(listaAsientos));

    //        console.debug(asientosPosicionados);
    $("#pendientes").addClass("hidden");
    jQuery.each(all, function () {
      var asiento = asientosPosicionados[this.numero];
      if (!asiento) {
        //                console.log("El item no existe...");
        //                console.debug(this);
        $("#pendientes").removeClass("hidden");
        var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB
        if (clase === "1") {
          clase = "claseA";
        } else if (clase === "2") {
          clase = "claseB";
        } else {
          throw new Error(
            "El item debe tener uno de los id de clases siguientes: 1 o 2."
          );
        }
        var estado = crearReservacion.buscarEstadoAsientoItem(this);
        var item = $(".icono." + clase + "." + estado).clone();
        item.removeClass("ui-draggable icono");
        item.addClass("item2");
        $(".pendientes").append(item);
        item.css("left", core.ajustarPosicion(this.coordenadaX));
        item.css("top", core.ajustarPosicion(this.coordenadaY));
        this.jsId = core.uniqId();
        item.attr("jsId", this.jsId);
        var numero = this.numero;
        item.find(".detalle").text(this.numero);
        item.data("numero", this.numero);
        item.data("id", this.id);
      }
    });

    $(".containment .active").removeClass("active");
    $("#tab1").addClass("active");
    $("#nav1").addClass("active");

    var listaSenales = $("#listaSenalesHidden").val();
    if (listaSenales) {
      listaSenales = JSON.parse(listaSenales);
    } else {
      listaSenales = [];
    }
    jQuery.each(listaSenales, function () {
      var tipo = this.tipo; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB
      if (tipo === "1") {
        tipo = "salida";
      } else if (tipo === "2") {
        tipo = "chofer";
      } else {
        throw new Error(
          "El item debe tener uno de los id de tipo siguientes: 1 o 2."
        );
      }
      var item = $(".icono." + tipo).clone();
      item.removeClass("ui-draggable icono");
      item.addClass("item");
      var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2
      if (eval(nivel2)) {
        $(".nivel2").append(item);
      } else {
        $(".nivel1").append(item);
      }
      item.css("left", core.ajustarPosicion(this.coordenadaX));
      item.css("top", core.ajustarPosicion(this.coordenadaY));
      var id = this.id;
      this.jsId = core.uniqId();
      item.attr("jsId", this.jsId);
    });
    $("#listaSenalesHidden").val(JSON.stringify(listaSenales));

    $(".asiento .showInfo").bind("click", crearReservacion.info);
    //Solo se conectan los asietos que esten en un nivel valido, los que estan en pendientes hay que reasignarlos.
    $(".nivel1 .asiento img").bind("click", crearReservacion.clickAsiento);
    $(".nivel2 .asiento img").bind("click", crearReservacion.clickAsiento);
  },

  info: function (e) {
    //        console.log("showInfo-init");
    e.preventDefault();
    e.stopPropagation();
    var item = $(this);
    var numero = item.parent().data("numero");
    //        console.debug(numero);
    if (numero === null || $.trim(numero) === "") {
      throw new Error("No se pudo determinar el numero de asiento.");
    }
    var idSalida = core.getSelectedItemId("#salidaGrid");
    if (idSalida === null || $.trim(idSalida) === "") {
      throw new Error("No se pudo determinar el id de la salida.");
    }

    core.request({
      url: $("#pathInfoAsientoSalida").attr("value"),
      method: "GET", //Obligatorio
      extraParams: {
        idSalida: idSalida,
        numeroAsiento: numero,
      },
      dataType: "html",
      async: true,
      successCallback: function (success) {
        //                console.debug(success);
        core.showMessageDialog({
          title: "Consultar Asiento Bus",
          fullscreen: true,
          compact: true,
          text: success,
          defaultButtonOFF: true,
          buttons: {
            Cancelar: {
              primary: true,
              type: "info",
              click: function () {
                //                                console.log("Cancelar - click - init");
                $("body").css("overflow-y", "auto");
                this.dialog2("close");
              },
            },
          },
        });
      },
    });
  },

  clickAsiento: function (e) {
    //        console.log("clickAsiento-init");
    e.preventDefault();
    e.stopPropagation();
    //        console.debug(e);
    //        console.debug($(this));
    var figure = $(this).parent();
    var numero = figure.data("numero");
    if (figure.hasClass("reservado")) {
      alert("El asiento con el número " + numero + " se encuentra reservado.");
      return;
    } else if (figure.hasClass("cortesia")) {
      alert("El asiento con el número " + numero + " es una cortesía.");
      return;
    } else if (figure.hasClass("vendido")) {
      alert("El asiento con el número " + numero + " ya está vendido.");
      return;
    }
    var listaClienteReservacion = $(
      "#crear_reservacion_command_listaClienteReservacion"
    ).val();
    if (listaClienteReservacion) {
      listaClienteReservacion = JSON.parse(listaClienteReservacion);
    } else {
      listaClienteReservacion = [];
    }
    var item = crearReservacion.findAsiento(listaClienteReservacion, numero);
    if (item === null) {
      item = {
        numero: figure.data("numero"),
        id: figure.data("id"),
        idCliente: "",
      };
      listaClienteReservacion.push(item);
      figure.addClass("selected");
      crearReservacion.renderItemGridAddReservacion(item);
    } else {
      listaClienteReservacion = core.removeItemArray(
        listaClienteReservacion,
        item
      );
      figure.removeClass("selected");
      crearReservacion.renderItemGridDeletedReservacion(
        item,
        listaClienteReservacion
      );
    }
    //        console.debug(listaClienteReservacion);
    $("#crear_reservacion_command_listaClienteReservacion").val(
      JSON.stringify(listaClienteReservacion)
    );
  },

  findAsiento: function (listaClienteReservacion, numero) {
    var result = null;
    numero = $.trim(numero);
    $.each(listaClienteReservacion, function () {
      if ($.trim(this.numero) === numero) {
        result = this;
        return;
      }
    });
    return result;
  },

  //Adiciona un item especifico de la tabla
  renderItemGridAddReservacion: function (item) {
    $("#clienteReservacionBody").find("#clienteReservacionVacioTR").hide(); //Oculto el TR vacio
    var numero = item.numero;
    var id = core.uniqId("cliente_reservacion");
    var placeholder = "Seleccione el cliente de la reservación";
    var inputSelect = $("#inputClienteHidden").clone(); //FALTA ESTO...........
    inputSelect.prop("id", id);
    inputSelect.prop("name", id);
    inputSelect.data("numero", numero);
    inputSelect.prop("placeholder", placeholder);
    inputSelect.attr("placeholder", placeholder);
    inputSelect.data("placeholder", placeholder);
    inputSelect.prop("required", "required");
    inputSelect.removeClass("hidden");
    inputSelect.val("");
    inputSelect.removeClass("select2-offscreen");
    var itemTR = $(
      "<tr class='trSelect" +
        numero +
        "'><td>" +
        numero +
        "</td><td class='inputSelect'></td></tr>"
    );
    itemTR.find(".inputSelect").append(inputSelect);
    var clienteActionsHidden = $("#clienteActionsHidden").clone();
    clienteActionsHidden.prop("id", id);
    clienteActionsHidden.removeClass("hidden");
    itemTR.find(".inputSelect").append(clienteActionsHidden);
    $("#clienteReservacionBody").append(itemTR);
    //        console.debug("clienteActionsHidden...");
    clienteActionsHidden.find("#addCliente").click(crearReservacion.addCliente);
    clienteActionsHidden
      .find("#updateCliente")
      .click(crearReservacion.updateCliente);
    clienteActionsHidden.find("#updateCliente").data("index", id);
    clienteActionsHidden
      .find("#seachCliente")
      .click(crearReservacion.seachCliente);

    var pathlistarclientespaginando = inputSelect.data(
      "pathlistarclientespaginando"
    );
    //        console.debug(inputSelect);
    inputSelect.select2({
      minimumInputLength: 1,
      allowClear: true,
      placeholder: placeholder,
      ajax: {
        url: pathlistarclientespaginando,
        dataType: "json",
        type: "POST",
        data: function (term, page) {
          return { term: term, page_limit: 5 };
        },
        results: function (data, page) {
          return { results: data.options };
        },
      },
    });

    $(inputSelect).select2("data", $("#clienteBase").select2("data"));
  },

  //Elimina un item especifico de la tabla
  renderItemGridDeletedReservacion: function (item, listaClienteReservacion) {
    if (!listaClienteReservacion) {
      listaClienteReservacion = $(
        "#crear_reservacion_command_listaClienteReservacion"
      ).val();
      if (listaClienteReservacion) {
        listaClienteReservacion = JSON.parse(listaClienteReservacion);
      } else {
        listaClienteReservacion = [];
      }
    }

    if (listaClienteReservacion.length === 0) {
      $("#clienteReservacionBody")
        .find("tr")
        .not("#clienteReservacionVacioTR")
        .remove(); //Elimino todos los tr
      $("#clienteReservacionBody").find("#clienteReservacionVacioTR").show(); //Muestro el vacio
    } else {
      $("#clienteReservacionBody").find("#clienteReservacionVacioTR").hide(); //Oculto el vacio
      var numero = item.numero;
      $("#clienteReservacionBody")
        .find(".trSelect" + numero)
        .remove();
    }
  },

  //Se elimina toda la informacion de boletos hasta el momento, y se muestra el tr vacio
  clearItemGridReservaciones: function () {
    $("#crear_reservacion_command_listaClienteReservacion").val(
      JSON.stringify([])
    );
    $("#clienteReservacionBody")
      .find("tr")
      .not("#clienteReservacionVacioTR")
      .remove(); //Elimino todos los tr
    $("#clienteReservacionBody").find("#clienteReservacionVacioTR").show(); //Muestro el vacio
  },

  imprimirDocumentos: function () {
    //        console.log("imprimirDocumentos-init");
    //        console.log("imprimirDocumentos-end");
  },

  syncronizarListaReservaciones: function () {
    var listaClienteReservacion = $(
      "#crear_reservacion_command_listaClienteReservacion"
    ).val();
    if (listaClienteReservacion) {
      listaClienteReservacion = JSON.parse(listaClienteReservacion);
    } else {
      listaClienteReservacion = [];
    }
    $.each(listaClienteReservacion, function () {
      this.idCliente = $(".trSelect" + this.numero)
        .find('input[id^="cliente_reservacion"]')
        .val();
    });
    //        console.debug(listaClienteReservacion);
    $("#crear_reservacion_command_listaClienteReservacion").val(
      JSON.stringify(listaClienteReservacion)
    );
  },

  clickAceptar: function (e) {
    //        console.debug("clickAceptar-init");
    //        console.debug($(this));
    e.preventDefault();
    e.stopPropagation();
    var reservacionForm = $("#reservacionForm");
    if (
      core.customValidateForm(reservacionForm) === true &&
      crearReservacion.customValidate() === true
    ) {
      crearReservacion.syncronizarListaReservaciones();
      $(reservacionForm).ajaxSubmit({
        target: reservacionForm.attr("action"),
        type: "POST",
        dataType: "html",
        cache: false,
        async: false,
        beforeSubmit: function () {
          core.showLoading({ showLoading: true });
        },
        error: function () {
          core.hideLoading({ showLoading: true });
        },
        success: function (responseText) {
          //                    console.log("submitHandler....success");
          //                    console.debug(responseText);
          core.hideLoading({ showLoading: true });
          if (!core.procesarRespuestaServidor(responseText)) {
            //                        console.log("procesarRespuestaServidor....okook");
            alert("Operación realizada satisfactoriamente.", function () {
              crearReservacion.imprimirDocumentos();
              core.getPageForMenu(reservacionForm.attr("action"));
            });
          }
        },
      });
    }
  },

  customValidate: function (dialog) {
    cantidadReservaciones = $("#clienteReservacionBody")
      .find("tr")
      .not("#clienteReservacionVacioTR").length;
    if (cantidadReservaciones <= 0) {
      core.hiddenDialog2(dialog);
      alert("Debe seleccionar al menos un asiento.", function () {
        core.showDialog2(dialog);
      });
      return false;
    }

    return true;
  },

  addCliente: function (e) {
    //        console.log("addCliente -- init ....");
    var inputCliente = $(this)
      .parent()
      .parent()
      .find("input[id^='cliente_reservacion']");
    frondend.loadSubPage(e, $(this), function (id) {
      if (id !== "") {
        core.request({
          url: inputCliente.data("pathlistarclientespaginando"),
          type: "POST",
          dataType: "json",
          async: false,
          extraParams: { id: id },
          successCallback: function (data) {
            if (data.options && data.options[0]) {
              inputCliente.select2("data", data.options[0]);
            }
          },
        });
      }
    });
  },

  updateCliente: function (e) {
    //        console.log("updateCliente -- init ....");
    //        console.debug(this);
    var inputCliente = $(this)
      .parent()
      .parent()
      .find("input[id^='cliente_reservacion']");
    frondend.loadSubPage(e, $(this), function () {
      //            console.debug("Actulizando datos del combo....");
      var id = inputCliente.val();
      if (id !== "") {
        core.request({
          url: inputCliente.data("pathlistarclientespaginando"),
          type: "POST",
          dataType: "json",
          async: false,
          extraParams: { id: id },
          successCallback: function (data) {
            //                        console.debug("Actulizando datos del combo...data");
            //                        console.debug(data);
            if (data.options && data.options[0]) {
              inputCliente.select2("data", data.options[0]);
            }
          },
        });
      }
    });
  },
  seachCliente: function (e) {
    //        console.log("seachCliente -- init ....");
    //        console.debug(this);
    var inputCliente = $(this)
      .parent()
      .parent()
      .find("input[id^='cliente_reservacion']");
    frondend.loadSubPage(e, $(this), function (id) {
      //            console.debug("Seteando elemento seleccionado...");
      //            console.debug(id);
      if (id !== "") {
        core.request({
          url: inputCliente.data("pathlistarclientespaginando"),
          type: "POST",
          dataType: "json",
          async: false,
          extraParams: { id: id },
          successCallback: function (data) {
            //                        console.debug("Actulizando datos del combo...data");
            //                        console.debug(data);
            if (data.options && data.options[0]) {
              inputCliente.select2("data", data.options[0]);
            }
          },
        });
      }
    });
  },
};
