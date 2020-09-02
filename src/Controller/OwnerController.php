<?php

namespace App\Controller;

use App\Entity\Dog;
use App\Entity\Owner;
use App\Form\OwnerFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

class OwnerController extends AbstractController
{
    /**
     * @Route("/owners", name="get_owners", methods="GET")
     */
    public function getOwnersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $owners = $em->getRepository(Owner::class)->findAll();

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer(null, null, null, null, null, null, $defaultContext));

        $serializer = new Serializer($normalizer, [$encoder]);
        $serializedOwners = $serializer->serialize($owners, 'json');

        return new Response($serializedOwners, 200);

    }

    /**
     * @Route("/owners/{id}", name="get_owner", methods="GET")
     */
    public function getOwnerAction(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $owners = $em->getRepository(Owner::class)->find($id);

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer(null, null, null, null, null, null, $defaultContext));

        $serializer = new Serializer($normalizer, [$encoder]);
        $serializedOwners = $serializer->serialize($owners, 'json');

        return new Response($serializedOwners, 200);

    }

    /**
     * @Route("/owners", name="post_owner", methods="POST")
     */
    public function addOwnerAction(Request $request) {
        $data = json_decode($request->getContent(), true);

        $owner = new Owner();
        $form = $this->createForm(OwnerFormType::class, $owner);
        $form->submit($data);

        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($owner);
            $em->flush();

            return new Response("Successfully created", 200);
        }

        throw new HttpException(400, "Invalid data");
    }

    /**
     * @Route("/owners/{id}", name="update_owner", methods="PUT")
     */
    public function updateOwnerAction(int $id, Request $request) {
        if(!$id) {
            throw new HttpException(400, "Invalid Id");
        }

        $em = $this->getDoctrine()->getManager();
        $owner = $em->getRepository(Owner::class)->find($id);

        if (!$owner) {
            throw new HttpException(404, "Invalid data");
        }

        $data = json_decode($request->getContent(), true);

        empty($data['name']) ? true : $owner->setName($data['name']);
        empty($data['surname']) ? true : $owner->setSurname($data['surname']);
        empty($data['email']) ? true : $owner->setEmail($data['email']);
        empty($data['phone']) ? true : $owner->setPhone($data['phone']);

        if(!empty($data['dogId'])) {
            $dog = $em->getRepository(Dog::class)->find($data['dogId']);

            if (!$dog) {
                throw new HttpException(404, "Invalid data");
            }

            $owner->addDog($dog);
        }

        $em->persist($owner);
        $em->flush();


        return new Response("Successfully updated!", 200);
    }

    /**
     * @Route("/owners/{id}", name="delete_owner", methods="DELETE")
     */
    public function deleteOwnerAction(int $id) {
        if(!$id) {
            throw new HttpException(400, "Invalid Id");
        }

        $em = $this->getDoctrine()->getManager();
        $owner = $em->getRepository(Owner::class)->find($id);

        if (!$owner) {
            throw new HttpException(404, "Invalid data");
        }

        $em->remove($owner);
        $em->flush();

        return new Response("Successfully deleted", 200);
    }


    private function toJson($object) {

        $object = $this->get('serializer')->normalize($object, null, [AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);
        return $object;
    }

}
