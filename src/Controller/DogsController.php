<?php

namespace App\Controller;

use App\Entity\Owner;
use App\Form\DogFormType;
use App\Entity\Dog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation\Method;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DogsController extends AbstractController
{
    /**
     * @Route("/dogs", name="get_dogs", methods="GET")
     */
    public function getDogsAction() {
        $em = $this->getDoctrine()->getManager();

        $dogs = $em->getRepository(Dog::class)
            ->findAll();

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer(null, null, null, null, null, null, $defaultContext));

        $serializer = new Serializer($normalizer, [$encoder]);
        $serializedDogs = $serializer->serialize($dogs, 'json');

        return new Response($serializedDogs, 200);
    }

    /**
     * @Route("/dogs/{id}", name="get_dog", methods="GET")
     */
    public function getDogAction(int $id) {
        if(!$id) {
            throw new HttpException(400, "Invalid Id");
        }

        $em = $this->getDoctrine()->getManager();
        $dog = $em->getRepository(Dog::class)->find($id);

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer(null, null, null, null, null, null, $defaultContext));

        $serializer = new Serializer($normalizer, [$encoder]);
        $serializedDog = $serializer->serialize($dog, 'json');

        if (!$dog) {
            throw new HttpException(404, "Invalid data");
        }

        return new Response($serializedDog, 200);
    }

    /**
     * @Route("/dogs", name="post_dog", methods="POST")
     */
    public function createDogAction(Request $request) {

        $data = json_decode($request->getContent(), true);

        $dog = new Dog();
        $form = $this->createForm(DogFormType::class, $dog);
        $form->submit($data);

        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($dog);
            $em->flush();

            return new Response("Successfully created", 200);
        }

        throw new HttpException(400, "Invalid data");
    }

    /**
     * @Route("/dogs/{id}", name="update_dog", methods="PUT")
     */
    public function updateDogAction(int $id, Request $request) {
        if(!$id) {
            throw new HttpException(400, "Invalid Id");
        }

        $em = $this->getDoctrine()->getManager();
        $dog = $em->getRepository(Dog::class)->find($id);

        if (!$dog) {
            throw new HttpException(404, "Invalid data");
        }

        $data = json_decode($request->getContent(), true);

        empty($data['name']) ? true : $dog->setName($data['name']);
        empty($data['breed']) ? true : $dog->setBreed($data['breed']);
        empty($data['birthday']) ? true : $dog->setBirthday($data['birthday']);

        if(!empty($data['ownerId'])) {
            $owner = $em->getRepository(Owner::class)->find($data['ownerId']);

            if (!$owner) {
                throw new HttpException(404, "Invalid data");
            }

            $dog->setOwner($owner);
        }

        $em->persist($dog);
        $em->flush();


        return new Response("Successfully updated!", 200);
    }

    /**
     * @Route("/dogs/{id}", name="delete_dog", methods="DELETE")
     */
    public function deleteDogAction(int $id) {
        if(!$id) {
            throw new HttpException(400, "Invalid Id");
        }

        $em = $this->getDoctrine()->getManager();
        $dog = $em->getRepository(Dog::class)->find($id);

        if (!$dog) {
            throw new HttpException(404, "Invalid data");
        }

        $em->remove($dog);
        $em->flush();

        return new Response("Successfully deleted", 200);
    }

}
