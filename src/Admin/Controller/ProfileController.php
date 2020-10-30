<?php

namespace App\Admin\Controller;

use App\ActionLog\Report\ActionLogReportFactory;
use App\Admin\Form\ProfileType;
use App\Entity\Admin\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for viewing and managing profiles of \App\Entity\Admin\User instances.
 */
class ProfileController extends AbstractController
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ActionLogReportFactory $actionLogReportFactory;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ActionLogReportFactory $actionLogReportFactory)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->actionLogReportFactory = $actionLogReportFactory;
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

            return $this->redirectToRoute('admin_profile_edit');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('danger', $this->translator->trans('form.invalid'));
        }

        $actionLogReport = $this->actionLogReportFactory->createForUserLastMonth($user);

        return $this->render('admin/profile/index.html.twig', [
            'user' => $user,
            'action_log_report' => $actionLogReport,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/{emailAddress}", name="admin_profile_view")
     */
    public function view(User $user): Response
    {
        $actionLogReport = $this->actionLogReportFactory->createForUserLastMonth($user);

        return $this->render('admin/profile/index.html.twig', [
            'user' => $user,
            'action_log_report' => $actionLogReport,
        ]);
    }
}
