<?php
namespace App\Auxiliares;
use Symfony\Component\HttpFoundation\JsonResponse;

class Auxiliar {
	public static function generadorRespuesta(string|array $mensaje, string $estado = "OK", array|object $datos = []):array{
        return 
            [
                "estado" => $estado,
                "mensaje" => $mensaje,
                "datos" => $datos
            ]
        ;
    }
}