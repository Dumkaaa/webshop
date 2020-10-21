<?php

namespace App\Admin\Controller;

use App\Admin\Form\ProfileType;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/profile/{emailAddress}", name="admin_profile", defaults={"emailAddress"=null})
     */
    public function index(Request $request, ?string $emailAddress = null): Response
    {
        if ($emailAddress) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['emailAddress' => $emailAddress]);
            if (!$user) {
                throw $this->createNotFoundException(sprintf('No user found for email address "%s".', $emailAddress));
            }
        } else {
            // Fallback to the user's own profile if no email address is given.
            /** @var User $user */
            $user = $this->getUser();
        }

        if ($user === $this->getUser()) {
            // This is the logged in user, allow editing.
            $form = $this->createForm(ProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newPassword = $user->getPlainPassword();
                if ($newPassword) {
                    // Encode the new password.
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('profile.saved'));
                $this->redirectToRoute('admin_profile', [
                    'emailAddress' => $emailAddress,
                ]);
            } elseif ($form->isSubmitted()) {
                $this->addFlash('danger', $this->translator->trans('form.invalid'));
            }
        } else {
            $form = null;
        }

        return $this->render('admin/profile/index.html.twig', [
            'user' => $user,
            'form' => $form ? $form->createView() : null,
        ]);
    }
}
