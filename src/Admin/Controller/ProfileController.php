<?php

namespace App\Admin\Controller;

use App\Admin\Form\ProfileType;
use App\Entity\Admin\User;
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
     * @Route("/profile", name="admin_profile_edit")
     */
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

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
            $this->redirectToRoute('admin_profile_edit');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('danger', $this->translator->trans('form.invalid'));
        }

        return $this->render('admin/profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/{emailAddress}", name="admin_profile_view")
     */
    public function view(User $user): Response
    {
        return $this->render('admin/profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
