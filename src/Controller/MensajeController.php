<?php

namespace App\Controller;

use App\Entity\Mensaje;
use App\Repository\MensajeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class MensajeController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine) {}



    #[Route('/mensaje', name: 'app_mensaje')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MensajeController.php',
        ]);
    }

    #[Route('/mensaje/list', name: 'app_mensaje_listar')]
    public function listar(Request $request, MensajeRepository $mensajeRepository): JsonResponse
    {
        $listMensajes = $mensajeRepository->findAll();

        $listJson = $this->toJson($listMensajes);

        return new JsonResponse($listJson, 200, [], true);

    }


    #[Route('/mensaje/save', name: 'app_mensaje_save', methods: ['POST'])]
    public function save(Request $request):JsonResponse
    {
        $json = json_decode($request->getContent(), true);

        $mensaje = new Mensaje();
        $mensaje->setDescripcion($json['descripcion']);
        $now = new \DateTime("now");
        $mensaje->setFecha($now);

        $em = $this->doctrine->getManager();
        $em->persist($mensaje);
        $em->flush();

        return new JsonResponse("{ mensaje: Mensaje creado correctamente}", 200, [], true);

    }

    #[Route('/mensaje/remove/{id}', name: 'app_mensaje_remove', methods: ['DELETE'])]
    public function remove(Request $request, MensajeRepository $mensajeRepository, int $id):JsonResponse
    {

        $criteria = array('id' => $id);
        $mensajeEliminar = $mensajeRepository-> findBy($criteria);

        if($mensajeEliminar != null){
            $em = $this->doctrine->getManager();
            $em->remove($mensajeEliminar[0]);
            $em->flush();
            return new JsonResponse("{ mensaje: Mensaje eliminado correctamente}", 200, [], true);
        } else {
            return new JsonResponse("{ mensaje: No se ha encontrado el mensaje}", 151, [], true);
        }


    }


    public function toJson($data): string
    {
        //Inicialización de serializador
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        //Conversion a JSON
        $json = $serializer->serialize($data, 'json');

        return $json;
    }

}
