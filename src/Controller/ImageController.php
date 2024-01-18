<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Image;
use App\Form\AddImageType;
use App\Repository\ImageRepository;
use App\Services\UploadFileService;
use App\Services\DeleteImageService;

#[Route('/image')]
class ImageController extends AbstractController
{
    public function __construct(
        private ImageRepository $imageRepository,
        private UploadFileService $uploadFileService,
        private DeleteImageService $deleteImageService
    ) {

    }

    #[Route('/add_image', name: 'add_image')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $profile = $user->getProfile();

        $image = new Image();

        $imageForm = $this->createForm(AddImageType::class, $image);
        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $imageFile = $imageForm->get('image')->getData();

            if ($imageFile) {
                $newImageName = $this->uploadFileService->uploadFile($imageFile);
                $image->setImageData($newImageName);
                $image->setProfile($profile);

                $this->imageRepository->save($image);
            }

            return $this->redirectToRoute('profile');
        }
        return $this->render('image/add_image.html.twig', [
            'image_form' => $imageForm->createView(),
        ]);
    }

    #[Route('/view_image/{id}', name: 'view_image', requirements: ['id' => '\d+'])]
    public function show(Image $image): Response
    {
        $user = $this->getUser();
        $profile = $user->getProfile();
        $profileId = $profile->getId();
        $imageProfile = $image->getProfile()->getId();

        if ($profileId === $imageProfile) {
            return $this->render('image/view_image.html.twig', [
                'image' => $image
            ]);
        } else {
            return $this->render('image/view_profile_images.html.twig', [
                'image' => $image
            ]);
        }
    }

    #[Route('/remove_image/{id}', name: 'remove_image', requirements: ['id' => '\d+'])]
    public function delete(Image $image): Response
    {
        $this->deleteImageService->deleteImage($image);

        return $this->redirectToRoute('profile');
    }
}