<?php

declare(strict_types=1);

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Models\EmpleadoModel;

class EmpleadoController
{
    private $OBJEmpleadoModel;

    public function __construct()
    {
        $this->OBJEmpleadoModel=new EmpleadoModel();
    }    

    //UTILIDADES
    public static function CheckParametros($requiredParams, Request $request, Response $response)
    {
        $error=false;
        $errorParams='';
        $requestParams=$request->getParsedBody();
        foreach($requiredParams as $param)
        {
            if(!isset($requestParams[$param]) || strlen($requestParams[$param])<=0 || $requestParams[$param]=='NaN')
            {
                $error=true;
                $errorParams=$errorParams.$param.' ';
            }
        }
        if ($error)
        {
            $json=array();
            $json["Estado"]="Error";
            $json["Mensaje"]="Faltan parametros: ".$errorParams." todos los datos del empleado son obligatorios.";
            $json["data"]=[];
            $response->getBody()->write(json_encode($json));
        }
        return $error;
    }

    /*
    Endpoint: Empleados
    Parameters: CodigoEmp
    Method: GET
    */
    public function recuperarEmpleado(Request $request, Response $response, array $args) {
        $codigoEmp=$args['id'];
        $DatosRecuperados=$this->OBJEmpleadoModel->RecuperarEmpleado($codigoEmp);
        $json=array();
        if ($DatosRecuperados!=null)
        {
            $codigoHTTP=200;
            $json["Estado"]="Exito";
            $json["Mensaje"]="Empleado $codigoEmp recuperado.";
            $json["data"]=$DatosRecuperados;
            $response->getBody()->write(json_encode($json));
        }
        else
        {
            $codigoHTTP=404;
            $json["Estado"]="Error";
            $json["Mensaje"]="Empleado $codigoEmp no encontrado.";
            $json["data"]=[];
            $response->getBody()->write(json_encode($json));
        }
        return $response
                    ->withHeader('Content-type','application/json')
                    ->withStatus($codigoHTTP);
    }

    /*
    Endpoint: Empleados
    Parameters: Ninguno
    Method: GET
    */
    public function VerTodos(Request $request, Response $response, array $args) {
        $ListaEmpleados=$this->OBJEmpleadoModel->VerEmpleados();
        $json=array();
        if ($ListaEmpleados!=null)
        {
            $codigoHTTP=200;
            $json["Estado"]="Exito";
            $json["Mensaje"]="Empleados recuperados..";
            $json["data"]=$ListaEmpleados;
            $response->getBody()->write(json_encode($json));
        }
        else
        {
            $codigoHTTP=404;
            $json["Estado"]="Error";
            $json["Mensaje"]="No hay empleados";
            $json["data"]=[];
            $response->getBody()->write(json_encode($json));
        }
        return $response
                    ->withHeader('Content-type','application/json')
                    ->withStatus($codigoHTTP);
    }    
}
