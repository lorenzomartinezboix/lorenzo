<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Auxiliares\Auxiliar;
use App\Entity\Jugador;
use App\Entity\Equipo;
use App\Entity\Liga;
use App\Entity\Gol;
class JugadorController extends AbstractController
{
//Registrar un Jugador
    public function registrar(Request $req,EntityManagerInterface $em, ValidatorInterface $validator):JsonResponse{

        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal al registrar Jugador";
        $id = 0;

        if($datos){
        //ponemos esta función de ltrim para que que quite blancos y así da error si no está informado
            $nombre=ltrim($datos->nombre);
            $jugador = new Jugador();
        //esto quiere decir que si nombre viene informado y no es nulo, coge $nombre, si no, coge null
            $jugador->setNombre($nombre ?? null);
            $jugador->setDorsal($datos->dorsal ?? null);
        //busca si existe el equipo
            $equipo = $em->getRepository(Equipo::class)->find($datos->equipo ?? 0);
        
            $jugador->setEquipo($equipo);         
        
            $errores = $validator->validate($jugador);

            if (count($errores) > 0) {
                $mensaje = ["Hay errores en los campos",[]];
                foreach($errores as $error){
                $mensaje[1][] = $error->getMessage();
                }
            }else{
                try{
                    $em->persist($jugador);
                    $em->flush();
                    $id = $jugador->getId();
                    $mensaje = "Jugador dado de alta correctamente";
                }catch(\Exception $e){
                    $mensaje = $e->getMessage();
                }
            }          
        }else{
            $mensaje = "Formato de mensaje incorrecto";
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$id ? "OK" : "KO",$id ? ["id"=>$id] : []));
    }
//Editar sus campos o cambiarlo de equipo
    public function modificar(Request $req,EntityManagerInterface $em, ValidatorInterface $validator,int $id){
        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal modificando datos del Jugador";
        if($datos){
            try{
                $jugador = $em->getRepository(Jugador::class)->find($id);
                if($jugador){
                    if(isset($datos->nombre)){
                        $nombre=ltrim($datos->nombre);
                        $jugador->setNombre($nombre);
                    }
                    if(isset($datos->dorsal)){
                        $jugador->setDorsal($datos->dorsal);
                    }
                    if(isset($datos->equipo)){
                        $equipo = $em->getRepository(Equipo::class)->find($datos->equipo ?? 0);
                        
                        $jugador->setEquipo($equipo); 
                    }                    
                    $errores = $validator->validate($jugador);
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
                    $mensaje = "Jugador no encontrada";
                }
            }catch(\Exception $e){
                $mensaje = $e->getMessage();
            }
        }else{
            $mensaje = "Formato de mensaje incorrecto";
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO"));
    }
    public function borrar(Request $req,EntityManagerInterface $em, int $id){
        //aquí no ponemos $datos = json_decode($req->getContent()); porque si lo dejamos, el POSTMAN espera algo en el Body y al no poner body, da error de "Formato de mensaje incorrecto"       
                $mensaje = "Algo ha ido mal al intentar borrar el jugador";
                    try{
                        $jugador = $em->getRepository(Jugador::class)->find($id);
                        if($jugador){
                            $em->remove($jugador);
                            $em->flush();
                            $mensaje = "";
                        }else{
                            $mensaje = "Jugador no encontrada";
                        }
                    }catch(\Exception $e){
                        $mensaje = $e->getMessage();
                    }
                return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO"));
    }
    public function ver(EntityManagerInterface $em, int $id){
        $mensaje = "Algo ha ido mal al intentar ver los jugadores de un equipo";
        $estado = "KO";

        $datos = [];

        $equipo = $em->getRepository(Equipo::class)->find($id);
        
        try{            
            if($equipo){
                $nombreEquipo = $equipo->getNombre();

                $jugadores = $em->getRepository(Jugador::class)->findBy(['equipo' => $id]);    
                if($jugadores){
                    $mensaje = "Jugadores del $nombreEquipo";
                    $estado = "OK";                    
                    foreach($jugadores as $filaJugador){
                        $datosrecogidos = [];
                        $datosrecogidos["id"]= $filaJugador->getId();
                        $datosrecogidos["nombre"]= $filaJugador->getNombre();
                        $datosrecogidos["dorsal"]= $filaJugador->getDorsal();
                        $datos[] = $datosrecogidos;
                    }
                }else{
                    $mensaje = "Equipo $nombreEquipo no tiene jugadores";
                    $estado = "KO";

                }             
                //$mensaje = "";                
            }else{
                $mensaje = "Equipo no encontrado";
                $estado = "KO";
            }        
        }catch(\Exception $e){
            $mensaje = $e->getMessage();
            $estado = "KO";
        }                    
    //    return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO",$datos));
    return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));    
    }  
    public function verGoles(EntityManagerInterface $entityManager, int $idliga,int $idjugador):JsonResponse{
        $mensaje = "Algo ha ido mal obteniendo la cantidad de goles por jugador de una liga específica ";
        $datos=[];
        //Contador goles del jugador (no por partido)
        $totalGoles=0;  
        $nombreJugador = " ";
        try{
            //Acceder al objeto liga para recuperar los equipos participantes de la Liga.
            $liga = $entityManager->getRepository(Liga::class)->find($idliga);
            if($liga){
                $jugador = $entityManager->getRepository(Jugador::class)->find($idjugador);
                if($jugador){
                    //nombre del jugador
                    $nombreJugador = $jugador->getNombre();
                    $idequipo = $jugador->getEquipo()->getId();
                    //Acceder a los partidos de la liga y mediante los goles comprobar como ha quedado el equipo
//                    foreach($liga->getLigaspartidos() as $filaPartido){
                        //En $goles es o objeto de tipo gol  con el array de goles de cada partido
  //                      $goles = $filaPartido->getGolespartido();
                            //Si hay goles
    //                        if ($goles) {
                                // //Dentro de cada fila de partido ($filaPartido) recorro el array de goles
                                // foreach($goles as $filaGoles){
                                //     //Si idjugador de la url coincide con el campo id del objeto
                                //     //   $filaGoles->getJugador() de la tabla goles aumento contador de goles  
                                //     if ($idjugador == $filaGoles->getJugador()->getId()) {
                                //         $totalGoles++;
                                //         $nombreJugador = $filaGoles->getJugador()->getNombre();
                                //     }
                                // }
                    //llamamos a esta función countGoles que creé en el Repository, para sacar los goles del jugador
                    $totalGoles=$entityManager->getRepository(Gol::class)->countGoles($idjugador,$idequipo,$idliga);
      //                      }
        //            }
                    $datos['Jugador'] = $nombreJugador;
                    $datos['Goles jugador'] = $totalGoles;
                    $datos['Temporada'] = $liga->getTemporada();
                    $datos['Equipo'] = $jugador->getEquipo()->getNombre();
                    $mensaje = "";
                }else{
                    $mensaje = "Jugador no encontrado.";
                }   
            }else{
                $mensaje = "Liga no encontrada.";
            }            
        }catch(\Exception $e){
            $mensaje = $e->getMessage();
        }  
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$mensaje ? "KO" : "OK",$datos));
    }
}
