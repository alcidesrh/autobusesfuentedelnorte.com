<?php

namespace Acme\BackendBundle\Entity;

class MovilCode {
    
    const SERVIDOR_SATISFACTORIO = '1';
    const SERVIDOR_ERROR = '2';
    const CREDENCIALES_MAL = '3';
    const CODIGO_BARRA_FALSO = '4';
    const IDENTIFICADOR_BOLETO_NO_EXISTE = '5';
    const VALIDACION_ERROR = '6';
    const IDENTIFICADOR_ENCOMIENDA_NO_EXISTE = '7';
    const USUARIO_BLOQUEADO_DESHABILITADO = '8';
    const USUARIO_NOT_ACCESS_MOVIL = '9';
}
