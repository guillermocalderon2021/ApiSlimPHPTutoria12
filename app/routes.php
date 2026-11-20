<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use Api\Models\EmpleadoModel;
use Api\Controllers\EmpleadoController;

//Vistas con TWIG
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    //MIS RUTAS
    $app->get('/', function (Request $request, Response $response) {
        $view=Twig::fromRequest($request);
        return $view->render($response,'home.html.twig',['Nombre' => 'Alex','Mensaje'=> 'mensaje desde el endpoint, esta pagina web se puede considerar como la pagina de documentacion de la api empleados, a manera de ejemplo']);
    });

    //UNA FORMA DE TRABAJAR CON LOS ENDPOINTS ES DEFINIR UN CONTROLADOR CON FUNCIONES QUE MANEJEN CADA ENDPOINT ACA TENEMOS 2 EJEMPLOS PARA ENDPOINTS GET
    /*
    Endpoint: Empleados
    Parameters: CodigoEmp
    Method: GET
    */
    $app->get('/empleados/{id}', [EmpleadoController::class,'recuperarEmpleado']);

    /*
    Endpoint: Empleados
    Parameters: Ninguno
    Method: GET
    */
    $app->get('/empleados', [EmpleadoController::class,'VerTodos']);

    /*
    Endpoint: Empleados
    Parameters: CodigoEmp, NombreEmp, EdadEmp, SueldoEmp
    Method: POST
    */
    $app->post('/empleados', function (Request $request, Response $response, array $args){

        if(!EmpleadoController::CheckParametros(array('CodigoEmp','NombreEmp','EdadEmp', 'SueldoEmp'),$request, $response))
       {

            $datosRecibidos=$request->getParsedBody();
            $codigoEmp=$datosRecibidos['CodigoEmp'];
            $nombreEmp=$datosRecibidos['NombreEmp'];
            $edadEmp=(int)$datosRecibidos['EdadEmp'];
            $sueldoEmp=(float)$datosRecibidos['SueldoEmp'];

            $EmpleadoDB=new EmpleadoModel();
            $resultadoInsert=$EmpleadoDB->insertarUsuario($codigoEmp,$nombreEmp,$edadEmp,$sueldoEmp);

            $json=array();
            if ($resultadoInsert==true)
            {
                $codigoHTTP=201;
                $json["Estado"]="Exito";
                $json["Mensaje"]="Empleado $codigoEmp registrado con exito.";
                $json["data"]=[];
                $response->getBody()->write(json_encode($json));
            }
            else
            {
                $codigoHTTP=202;
                $json["Estado"]="Error";
                $json["Mensaje"]="No se pudo insertar el empleado $codigoEmp codigo ya existe.";
                $json["data"]=[];
                $response->getBody()->write(json_encode($json));

            }
        }
        else
        {
            $codigoHTTP=202;
        }
        return $response
            ->withHeader('Content-type','application/json')
            ->withStatus($codigoHTTP);
    });

    /*
    Endpoint: Empleados
    Parameters: CodigoEmp, NombreEmp, EdadEmp, SueldoEmp
    Method: PUT
    */
    $app->put('/empleados/{id}', function (Request $request, Response $response, array $args){
        // recuperamos el cuerpo de la solicitud JSON o en formato x-www-form-urlencoded el cual es mas comun para PUT
        $contentType = $request->getHeaderLine('Content-Type');
        if ($contentType === 'application/json') {
            $datosRecibidos = json_decode($request->getBody()->getContents(), true);
        } else {
            $datosRecibidos = $request->getParsedBody();
        }

        if(!EmpleadoController::CheckParametros(array('NombreEmp','EdadEmp', 'SueldoEmp'),$request,$response))
       {
            //Recuperamos el parametro
            $codigoEmp=$args['id'];
            //recuperamos el resto de datos a actualizar del cuerpo de la peticion
            $nombreEmp=$datosRecibidos['NombreEmp'];
            $edadEmp=(int)$datosRecibidos['EdadEmp'];
            $sueldoEmp=(float)$datosRecibidos['SueldoEmp'];

            $EmpleadoDB=new EmpleadoModel();
            $resultadoUpdate=$EmpleadoDB->actualizarEmpleado($codigoEmp,$nombreEmp,$edadEmp,$sueldoEmp);

            $json=array();
            if ($resultadoUpdate==true)
            {
                $codigoHTTP=200;
                $json["Estado"]="Exito";
                $json["Mensaje"]="Empleado $codigoEmp actualizado con exito.";
                $json["data"]=[];
                $response->getBody()->write(json_encode($json));
            }
            else
            {
                $codigoHTTP=202;
                $json["Estado"]="Error";
                $json["Mensaje"]="No se pudo actualizar el empleado $codigoEmp no existe o no ha sido modificado ningun dato.";
                $json["data"]=[];
                $response->getBody()->write(json_encode($json));

            }
        }
        else
        {
            $codigoHTTP=400;
        }
        return $response
            ->withHeader('Content-type','application/json')
            ->withStatus($codigoHTTP);
    });

    /*
    Endpoint: Empleados
    Parameters: CodigoEmp
    Method: DELETE
    */
    $app->delete('/empleados/{id}',function (Request $request, Response $response, array $args){
        //Recuperamos el parametro
        $codigoEmp=$args['id'];
        $EmpleadoDB=new EmpleadoModel();
        $resultadoDelete=$EmpleadoDB->eliminarEmpleado($codigoEmp);
        $json=array();
        if ($resultadoDelete==true)
        {
            $codigoHTTP=200;
            $json["Estado"]="Exito";
            $json["Mensaje"]="Empleado $codigoEmp eliminado con exito.";
            $json["data"]=[];
            $response->getBody()->write(json_encode($json));
        }
        else
        {
            $codigoHTTP=404;
            $json["Estado"]="Error";
            $json["Mensaje"]="No se pudo eliminar al empleado $codigoEmp, o no existe.";
            $json["data"]=[];
            $response->getBody()->write(json_encode($json));

        }
        return $response
            ->withHeader('Content-type','application/json')
            ->withStatus($codigoHTTP);
    });

};

