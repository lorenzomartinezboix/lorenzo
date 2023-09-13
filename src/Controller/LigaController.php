<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Auxiliares\Auxiliar;
use App\Entity\Liga;
use App\Entity\Participante;
use App\Entity\Partido;
use App\Entity\Gol;
use App\Entity\Equipo;

class LigaController extends AbstractController
{
//crear Liga
    public function crear(Request $req,EntityManagerInterface $em, ValidatorInterface $validator):JsonResponse{

        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal al crear Liga";
        $id = 0;

        if($datos){
        //ponemos esta función de ltrim para que que quite blancos y así da error si no está informado
            $temporada=ltrim($datos->temporada);
            $liga = new Liga();
        //esto quiere decir que si nombre viene informado y no es nulo, coge $nombre, si no, coge null
            $liga->setTemporada($temporada ?? null);  
            
            $errores = $validator->validate($liga);

            if (count($errores) > 0) {
                $mensaje = ["Hay errores en los campos",[]];
                foreach($errores as $error){
                $mensaje[1][] = $error->getMessage();
                }
            }else{
                try{
                    $em->persist($liga);
                    $em->flush();
                    $id = $liga->getId();
                    $mensaje = "Liga dada de alta correctamente";
                }catch(\Exception $e){
                    $mensaje = $e->getMessage();
                }
            }          
        }else{
            $mensaje = "Formato de mensaje incorrecto";
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$id ? "OK" : "KO",$id ? ["id"=>$id] : []));
    }
    public function addequipo(Request $req,EntityManagerInterface $em, ValidatorInterface $validator):JsonResponse{
        $datos = json_decode($req->getContent());
        $mensaje = "Algo ha ido mal añadiendo equipos a una liga";
        $estado = "OK";

        if($datos){
            $participante = new Participante();
            $equipo = $em->getRepository(Equipo::class)->find($datos->equipo);
            $liga = $em->getRepository(Liga::class)->find($datos->liga);

            if ($equipo && $liga){
                $nombrequipo=$equipo->getNombre();

                if ($equipo->isActivo() == true) {
                    $participante->setEquipo($equipo);
                    $participante->setLiga($liga);

                    $errores = $validator->validate($participante);

                    if (count($errores) > 0) {
                        $estado = "KO";
                        $mensaje = ["Hay errores en los campos",[]];
                        foreach($errores as $error){
                            $mensaje[1][] = $error->getMessage();
                        }
                    }else{
                        try{
                            $em->persist($participante);
                            $em->flush();              
                            $mensaje = "participante dado de alta";

                        }catch(\Exception $e){
                            $mensaje = $e->getMessage();
                        }
                   }
                }else{
                    $estado = "KO";
                    $mensaje = "El equipo $nombrequipo está dado de baja";

                }

            }else{
                $estado = "KO";
                $mensaje = "El equipo/liga debe existir";
            }
        }else{
            $mensaje = "Formato de mensaje incorrecto";
        }
        return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));
    }
    //Ver la lista de equipos participantes en la liga 
    public function ver(EntityManagerInterface $em, int $id){
        $mensaje = "Algo ha ido mal al intentar ver los equipos participantes de la liga";
        $estado = "KO";

        $datos = [];

        $liga = $em->getRepository(Liga::class)->find($id);
        
        try{            
            if($liga){
                $nombreLiga = $liga->getTemporada();

                $equipos = $em->getRepository(Participante::class)->findBy(['liga' => $id]);    
                if($equipos){
                    $mensaje = "Equipos participantes de la liga $nombreLiga";
                    $estado = "OK";                    
                    foreach($equipos as $filaEquipo){
                        $datosrecogidos = [];
                        $datosrecogidos["id"]= $filaEquipo->getEquipo()->getId();
                        $datosrecogidos["nombre"]= $filaEquipo->getEquipo()->getNombre();
                        $datos[] = $datosrecogidos;
                    }
                }else{
                    $mensaje = "Liga $nombreLiga no tiene equipos participantes";
                    $estado = "KO";

                }             
            }else{
                $mensaje = "Liga no existe";
                $estado = "KO";
            }        
        }catch(\Exception $e){
            $mensaje = $e->getMessage();
            $estado = "KO";
        }                    
    //    return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO",$datos));
    return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));    
    } 
    //clasificación de una liga
    public function resultado(EntityManagerInterface $em, int $id){
        $mensaje = "Algo ha ido mal al intentar ver la clasificación de liga";
        $estado = "KO";

        $datos = [];

        $liga = $em->getRepository(Liga::class)->find($id);
        
        try{            
            if($liga){
                $nombreLiga = $liga->getTemporada();

                $partido = $em->getRepository(Partido::class)->findBy(['liga' => $id]);    
                if($partido){
                    $mensaje = "Clasificación de la liga $nombreLiga";
                    $estado = "OK"; 

                    // Matriz para almacenar resultados de los equipos
                    $dataList = array();
                    $puntosLocal = 0;
                    $puntosVisitante = 0;
                    $datosrecogidos = [];

                    foreach($partido as $filaPartido){
                        $idPartido=$filaPartido->getId();

                        $idLocal=$filaPartido->getLocal()->getId();                                    
                        $idVisitante=$filaPartido->getVisitante()->getId();
                        $nombreEquipoLocal=$filaPartido->getLocal()->getNombre();
                        $nombreEquipoVisit=$filaPartido->getVisitante()->getNombre(); 
                        $golesLocal=$em->getRepository(Gol::class)->golesEquipo($idLocal,$idPartido);  
                        $golesVisitante=$em->getRepository(Gol::class)->golesEquipo($idVisitante,$idPartido); 

                        if ($golesLocal>$golesVisitante) {
                            $puntosLocal=2;
                        }
                        if ($golesLocal<$golesVisitante) {
                            $puntosVisitante=2;
                        }
                        if ($golesLocal == $golesVisitante) {
                            $puntosVisitante=1;
                            $puntosLocal=1;
                        }                        
                        $dataList[] = array('equipo' => $idLocal, 'puntos' => $puntosLocal, 'nombre' =>$nombreEquipoLocal);
                        $dataList[] = array('equipo' => $idVisitante, 'puntos' => $puntosVisitante, 'nombre' =>$nombreEquipoVisit);

                        $puntosLocal = 0;
                        $puntosVisitante = 0;
                    }
                    $accumulatedValues = array();

                    foreach ($dataList as $data) {
                        $equipo = $data['equipo'];
                        $puntos = $data['puntos'];
                        $nombre = $data['nombre'];
            
                        if (!isset($accumulatedValues[$equipo])) {
                            $accumulatedValues[$equipo] = 0;
                        }
            
                        $accumulatedValues[$equipo] += $puntos;
                        $puntosActuales = $accumulatedValues[$equipo];
                        $acumNombre[$equipo] = array('equipo' => $equipo, 'puntos' => $puntosActuales, 'nombre' => $nombre);
                    }                 

                    // Crear el nuevo array con códigos y sumas acumuladas
                    $sumArray = array();
                    
                    foreach ($acumNombre as $equipo => $accumulatedValue) {
                                $sumArray[] = array('equipo' => $equipo, 'puntos' => $accumulatedValue['puntos'], 'nombre' => $accumulatedValue['nombre']);
                    }
                    // Ordenar el array por el campo 'puntos'
                    usort($sumArray, function ($b, $a) {
                            return  $a['puntos']-$b['puntos'];
                    });   
                    
                    $clasifica = 1;                    
                    // Puedes imprimir o utilizar el nuevo array con códigos y sumas acumuladas
                    foreach ($sumArray as $item) {
                                $equipo = $item['equipo'];
                                $sum = $item['puntos'];

                                //$eq = $em->getRepository(Equipo::class)->find($equipo);
                                //$nombreEquipo = $eq->getNombre(); 
                                $nombreEquipo = $item['nombre'];
                                $datosrecogidos["Posición"] = $clasifica++;
                                $datosrecogidos["Equipo"]= $nombreEquipo;                          
                                $datosrecogidos["Puntos"]= $sum;                                  
                                $datos[] = $datosrecogidos;  
                            }                                                     
                }else{
                    $mensaje = "Liga $nombreLiga no tiene partidos";
                    $estado = "KO";
                }             
            }else{
                $mensaje = "Liga no existe";
                $estado = "KO";
            }        
        }catch(\Exception $e){
            $mensaje = $e->getMessage();
            $estado = "KO";
        }                    
    //    return $this->json(Auxiliar::generadorRespuesta($mensaje,empty($mensaje) ? "OK" : "KO",$datos));
    return $this->json(Auxiliar::generadorRespuesta($mensaje,$estado,$datos));    
    } 
}
