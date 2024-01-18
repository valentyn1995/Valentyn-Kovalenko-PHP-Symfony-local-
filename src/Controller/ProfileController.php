<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Profile;
use App\Form\ProfileFormType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\Services\UploadFileService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private ProfileRepository $profileRepository,
        private UserRepository $userRepository,
        private UploadFileService $uploadFileService
    ) {

    }

    #[Route('/profile_form', name: 'profile_form')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $profile = $user->getProfile();

        if (!$profile) {
            $profile = new Profile();
        }

        $profileForm = $this->createForm(ProfileFormType::class, $profile);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $avatarFile = $profileForm->get('avatar_name')->getData();

            if ($avatarFile) {
                $newFileName = $this->uploadFileService->uploadFile($avatarFile);
                $profile->setAvatarName($newFileName);
            }

            $profile->setUser($user);
            $user->setProfile($profile);

            $this->userRepository->save($user);

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/profile_form.html.twig', [
            'profile_form' => $profileForm->createView(),
        ]);
    }

    #[Route('/{id?}', name: 'profile', requirements: ['id' => '\d+'])]
    public function show(?int $id): Response
    {
        if ($id) {
            $profile = $this->profileRepository->find($id);
            
            if (!$profile) {
                throw new NotFoundHttpException('Profile not found');
            }

            return $this->render('profile/view_profile.html.twig', [
                'profile_of_someone' => $profile
            ]);
        } else {
            $user = $this->getUser();
            $profile = $user->getProfile();

            if (!$profile) {
                return $this->redirectToRoute('profile_form');
            }

            return $this->render('profile/my_profile.html.twig', [
                'profile' => $profile,
            ]);
        }
    }

    #[Route('/list_of_profiles', name: 'list_of_profiles')]
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        $profile = $user->getProfile();

        if (!$profile) {
            return $this->redirectToRoute('profile_form');
        }
        
        $offset = max(0, $request->query->getInt('offset', 0));
        $profiles = $this->profileRepository->getProfilesPaginator($profile, $offset);

        return $this->render('profile/list_of_profiles.html.twig', [
            'profiles' => $profiles,
            'previous' => $offset - ProfileRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($profiles), $offset + ProfileRepository::PAGINATOR_PER_PAGE)
        ]);
    }
}