<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Auxiliares\Auxiliar;
use App\Entity\Equipo;

class EquipoController extends AbstractController
{
//Alta Equipo
    public function crear(Request $req,EntityManagerInterface $em, ValidatorInterface $validator):JsonResponse{

        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal al crear Equipo";
        $id = 0;

        if($datos){
            //ponemos esta función de ltrim para que que quite blancos y así da error si no está informado
            $nombre=ltrim($datos->nombre);
            $entrenador=ltrim($datos->entrenador);
            $equipo = new Equipo();
            //esto quiere decir que si nombre existe y no es nulo, coge $nombre, si no, coge null
            $equipo->setNombre($nombre ?? null);
            $equipo->setActivo(true);
            $equipo->setEntrenador($entrenador ?? null);         
            
            $errores = $validator->validate($equipo);
             if (count($errores) > 0) {
                 $mensaje = ["Hay errores en los campos",[]];
                 foreach($errores as $error){
                     $mensaje[1][] = $error->getMessage();
                 }
             }else{
                 try{
                    $em->persist($equipo);
                    $em->flush();
                    $id = $equipo->getId();
                    $mensaje = "Equipo dado de alta correctamente";
                 }catch(\Exception $e){
                     $mensaje = $e->getMessage();
                 }
             }
              
         }else{
             $mensaje = "Formato de mensaje incorrecto";
        }
         return $this->json(Auxiliar::generadorRespuesta($mensaje,$id ? "OK" : "KO",$id ? ["id"=>$id] : []));
    }
//Modificar Equipo
    public function modificar(Request $req,EntityManagerInterface $em, ValidatorInterface $validator,int $id){
        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal modificando datos del Equipo";
        if($datos){
            try{
                $equipo = $em->getRepository(Equipo::class)->find($id);
                if($equipo){
                    $nombre=ltrim($datos->nombre);
                    $entrenador=ltrim($datos->entrenador);                    
                    if(isset($nombre)){
                        $equipo->setNombre($nombre);
                    }
                    if(isset($entrenador)){
                        $equipo->setEntrenador($entrenador);
                    }
                    $errores = $validator->validate($equipo);
                    if (count($errores) > 0) {
                        $mensaje = ["Hay errores en los campos",[]];
                        foreach($errores as $error){
                            $mensaje[1][] = $error->getMessage();
                        }
                    }else{
                            $em->flush();
                            $mensaje = "";
                        }
                }else{
                    $mensaje = "Equipo no encontrada";
                }
            }catch(\Exception $e){
                $mensaje = $e->getMessage();
            }
        }else{
            $mensaje = "Formato de mensaje incorrecto";
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO"));
    }
//Borrar(=dar de baja,inactivar) Equipo
public function borrar(Request $req,EntityManagerInterface $em, int $id){
    //$datos = json_decode($req->getContent());
    //aquí no hace falta $datos porque no hay body
            $mensaje = "Algo ha ido mal";
                try{
                    $equipo = $em->getRepository(Equipo::class)->find($id);
                    if($equipo){
                        if ($equipo->isActivo() == false) {
                            $mensaje = "El equipo ya está inactivo";
                        }else{
                            $equipo->setActivo(false);
                            $em->flush();
                            $mensaje = "";
                        }
                    }else{
                        $mensaje = "Equipo no encontrada";
                    }
                }catch(\Exception $e){
                    $mensaje = $e->getMessage();
                }
            return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO"));
        }
    public function ver(EntityManagerInterface $em, int $id){
        $mensaje = "Algo ha ido mal";
        $datos = [];
        try{
            $equipo = $em->getRepository(Equipo::class)->find($id);
            if($equipo){
                //array asociativo $datos con los campos de equipo y array vacío de jugadores
                    $datos = [
                        'id' => $equipo->getId(),
                        'nombre' => $equipo->getNombre(),
                        'activo' => $equipo->isActivo(),
                        'entrenador' => $equipo->getEntrenador(),
                        'jugadores' => []    
                        ];
    
                    //En objeto equipo la colección jugadoresequipos es un array de los jugadores
                    //Recorro ese array con la función getJugadoresequipos. Cada fila del array (datos del jugador del equipo)
                    //la trato con funcion toObject para convertir el objeto en string para poder sacarlo en postman
                    foreach($equipo->getJugadoresequipos() as $jugadorEquipo){
                        //Luego lo añado al array asociativo $datos en la columna 'jugadores'
                        $datos["jugadores"][] = $jugadorEquipo->toObject();    
                    }                
                $mensaje = "";                
            }else{
                $mensaje = "Equipo no encontrada";
            }
        }catch(\Exception $e){
            $mensaje = $e->getMessage();
        }
        
        return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO",$datos));
    }  
    public function listado(EntityManagerInterface $em, Request $request){
        $mensaje = "El proceso de filtrar equipo ha ido mal";
        $datos = [];
        $estado = "OK";
        //Cojo el parametro introducido por Params
        $activo = $request->query->get("activo");
        try{
            if (!isset($activo)) {
                //Obtener el objeto equipo sin filtro
                $equipo = $em->getRepository(Equipo::class)->findAll();
                $mensaje = "Sin filtrar";
            } //if (empty($activo))
            else{
                //Si $activo vale 1, lo cambio a 1, sino, lo cambio a 0 para poder
                //acceder a la base con el findby
                //$activo = $activo == 1 ? 1 : 0;
                //a mí me funciona sin esto, así que lo dejo sin eso
                //Obtener el objeto equipo correspondiente al activo recibido por url ($activo)
                $equipo = $em->getRepository(Equipo::class)->findBy(['activo' => $activo]);
                if ($activo == 1) {
                    $estado = 'activo';
                }else {
                    $estado = 'inactivo';
                }
                $mensaje = "Equipos en estado $estado";
            }
            if($equipo){  
                foreach($equipo as $filaEquipo){
                    $datosrecogidos = [];
                    $datosrecogidos["id"]= $filaEquipo->getId();
                    $datosrecogidos["nombre"]= $filaEquipo->getNombre();
                    $datosrecogidos["entrenador"]= $filaEquipo->getEntrenador();
                    $datosrecogidos["activo"]= $filaEquipo->isActivo();
                    $datos[] = $datosrecogidos;
                }
            }else{
                $estado = "KO";
                $mensaje = "No existen equipos para mostrar";
            }
        }catch(\Exception $e){
            $estado = "KO";
            $mensaje = $e->getMessage();
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));
    }        
          
}