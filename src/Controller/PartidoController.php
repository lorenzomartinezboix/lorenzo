<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Auxiliares\Auxiliar;
use App\Entity\Partido;
use App\Entity\Equipo;
use App\Entity\Liga;
use App\Entity\Gol;
use App\Entity\Jugador;

class PartidoController extends AbstractController
{
//Registrar un Partido
    public function crear(Request $req,EntityManagerInterface $em, ValidatorInterface $validator):JsonResponse{

        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal al registrar Partido";
        $id = 0;

        if($datos){
            $partido = new Partido();
            //OJO! NO CONSIGO SACAR MENSAJE DE ERROR EN FORMATO DEL DATETIME
            try{
                $fechaTime = new \Datetime($datos->fecha);
                $partido->setFecha($fechaTime); 
            }catch (\Exception $e) {
                $mensaje = "Error al procesar la fecha: " . $e->getMessage();
            }
               
            $liga = $em->getRepository(Liga::class)->find($datos->liga ?? 0);    
            if($liga){
                $partido->setLiga($liga);             
            }else {
                $mensaje = "Liga no encontrada";
            } 
        //busca si existe el equipo
            $local = $em->getRepository(Equipo::class)->find($datos->local ?? 0); 
            if ($local) {
                $partido->setLocal($local);         
            }else {
                $mensaje = "Equipo local no encontrada";
            }     
            $visitante = $em->getRepository(Equipo::class)->find($datos->visitante ?? null);    
            if ($visitante) {
                $partido->setVisitante($visitante); 

            }else {
                $mensaje = "Equipo visitante no encontrada";

            }                  
            if($liga&&$local&&$visitante){
                $errores = $validator->validate($partido);            

                if (count($errores) > 0) {
                    $mensaje = ["Hay errores en los campos",[]];
                    foreach($errores as $error){
                         $mensaje[1][] = $error->getMessage();
                    }
                }else{
                    try{
                        $em->persist($partido);
                        $em->flush();
                        $id = $partido->getId();
                        $mensaje = "Partido dado de alta correctamente";
                    }catch(\Exception $e){
                        $mensaje = $e->getMessage();
                    }
                }       
            }   
        }else{
            $mensaje = "Formato de mensaje incorrecto";
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$id ? "OK" : "KO",$id ? ["id"=>$id] : []));
    }
    public function addresultado(Request $req,EntityManagerInterface $em, ValidatorInterface $validator):JsonResponse{
        $datos = json_decode($req->getContent());        
        $mensaje = "Algo ha ido mal al añadir resultado";
        $estado = "OK";       

        if($datos){
            $gol = new Gol();
            $jugador = $em->getRepository(Jugador::class)->find($datos->jugador);
            $equipo = $em->getRepository(Equipo::class)->find($datos->equipo);
            $partido = $em->getRepository(Partido::class)->find($datos->partido);           

            if ($jugador && $equipo && $partido){
                $gol->setMinuto($datos->minuto);
                $gol->setJugador($jugador);
                $gol->setEquipo($equipo);
                $gol->setPartido($partido);

                $errores = $validator->validate($gol);

                if (count($errores) > 0) {
                    $mensaje = ["Hay errores en los campos",[$partido]];
                    foreach($errores as $error){
                        $mensaje[1][] = $error->getMessage();
                    }
                }else{
                    try{
                        $em->persist($gol);
                        $em->flush();
                        $mensaje = "alta de gol";
                    }catch(\Exception $e){
                        $mensaje = $e->getMessage();
                    }
                }
            }else{
                $estado = "KO";
                $mensaje = "El Jugador/equipo/partido deben de existir";
            }
        }else{
            $estado = "KO";
            $mensaje = "Formato de mensaje incorrecto";
        }
        //return $this->json(Auxiliar::generadorRespuesta($mensaje,$id ? "OK" : "KO",$id ? ["id"=>$id] : []));
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));        
    }    
    //Ver el listado de partidos de la liga, mostrando la fecha, el nombre de los equipos implicados, y el resultado si procede
    //OJO! igual faltaría añadir participantes que participantes es equipo-liga
    public function verListado(EntityManagerInterface $em, int $id){
        $mensaje = "Algo ha ido mal al intentar ver los partidos de una liga";
        $estado = "KO";

        $datos = [];

        $nombreLiga = $em->getRepository(Liga::class)->find($id)->getTemporada();
        
        try{            
            if($nombreLiga){
                $estado = "OK";

                $mensaje = "Partidos de la liga $nombreLiga";

                $partido = $em->getRepository(Partido::class)->findBy(['liga' => $id]);
                
                if($partido){

                    foreach ($partido as $filaPartido) {                        
                        $fechaDatetime=$filaPartido->getFecha();
                        $fechaFormateada = $fechaDatetime->format('d-m-Y');
//se necesita este array $datosrecogidos dentro del array datos porque si no solo saca 1 resultado, no acumula todos, saca el último                        
                        $datosrecogidos = [];
                        $datosrecogidos["Fecha"]= $fechaFormateada; 
                        $idPartido=$filaPartido->getId();
                        $idLocal=$filaPartido->getLocal()->getId();                                    
                        $idVisitante=$filaPartido->getVisitante()->getId(); 
                        $equipoLocal = $em->getRepository(Equipo::class)->find($idLocal);
                        $nombreLocal = $equipoLocal->getNombre();                        
                        $equipoVisitante = $em->getRepository(Equipo::class)->find($idVisitante);
                        $nombreVisitante = $equipoVisitante->getNombre();          
                        $datosrecogidos["Equipos"]= "$nombreLocal - $nombreVisitante";                          
                        $golesLocal=$em->getRepository(Gol::class)->golesEquipo($idLocal,$idPartido);  
                        $golesVisitante=$em->getRepository(Gol::class)->golesEquipo($idVisitante,$idPartido); 
                        $datosrecogidos["Resultado"]= "$golesLocal-$golesVisitante";
                        $datos[] = $datosrecogidos;                           
                    }               
                }else{
                    $mensaje = "No hay partidos en esa liga";
                    $estado = "KO";                    
                }

             
            }else{
                $mensaje = "Liga $id no encontrada";
                $estado = "KO";
            }        
        }
        catch(\Exception $e){
            $mensaje = $e->getMessage();
            $estado = "KO";
        }                    
    //    return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO",$datos));
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));  
    }   
    
}