<?php

namespace App\Admin\Controller;

use App\Admin\Form\AdminUserType;
use App\Admin\Security\Voter\AdminUserVoter;
use App\Admin\User\AdminUserManager;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for managing \App\Entity\Admin\User::class instances.
 */
class AdminUserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private UserPasswordEncoderInterface $passwordEncoder;
    private PaginatorInterface $paginator;
    private AdminUserManager $adminUserManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder, PaginatorInterface $paginator, AdminUserManager $adminUserManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->passwordEncoder = $passwordEncoder;
        $this->paginator = $paginator;
        $this->adminUserManager = $adminUserManager;
    }

    /**
     * @Route("/admin-users", name="admin_admin_user_index")
     * @IsGranted(AdminUserVoter::VIEW)
     */
    public function index(Request $request): Response
    {
        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        $searchQuery = $request->get('q');
        if ($searchQuery) {
            $queryBuilder->setParameter('searchQuery', '%'.$searchQuery.'%')
                ->orWhere('CONCAT(u.firstName, \' \', u.lastName) LIKE :searchQuery')
                ->orWhere('u.emailAddress LIKE :searchQuery')
            ;
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'u.lastActiveAt',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'DESC',
                PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['u.firstName', 'u.emailAddress', 'u.lastActiveAt', 'u.isEnabled'],
            ],
        );

        return $this->render('admin/admin_user/index.html.twig', [
            'pagination' => $pagination,
            'search_query' => $searchQuery,
        ]);
    }

    /**
     * @Route("/admin-users/new", name="admin_admin_user_new")
     * @IsGranted(AdminUserVoter::CREATE)
     */
    public function create(Request $request): Response
    {
        $user = new User();
        $user->setIsEnabled(true);

        // Make sure the password is required.
        $form = $this->createForm(AdminUserType::class, $user, [
            'require_password' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the password.
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin_user.created'));

            return $this->redirectToRoute('admin_admin_user_edit', [
                'emailAddress' => $user->getEmailAddress(),
            ]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('danger', $this->translator->trans('form.invalid'));
        }

        return $this->render('admin/admin_user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin-users/edit/{emailAddress}", name="admin_admin_user_edit")
     * @IsGranted(AdminUserVoter::EDIT, subject="user")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $user->getPlainPassword();
            if ($newPassword) {
                // Encode the new password.
                $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('admin_user.saved'));

            return $this->redirectToRoute('admin_admin_user_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('danger', $this->translator->trans('form.invalid'));
        }

        return $this->render('admin/admin_user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin-users/bulk-enable", name="admin_admin_user_bulk_enable")
     */
    public function bulkEnable(Request $request): RedirectResponse
    {
        // Read the email addresses from the request.
        $values = $request->get('values', '');
        $emailAddresses = array_filter(explode(',', $values));
        if (empty($emailAddresses)) {
            throw new BadRequestHttpException('Provide comma separated email addresses via the "values" GET parameter.');
        }

        $this->adminUserManager->toggleEnabled($emailAddresses);

        // Check if the request has an referer, otherwise redirect to the index.
        $referer = $request->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('admin_admin_user_index');
    }

    /**
     * @Route("/admin-users/bulk-disable", name="admin_admin_user_bulk_disable")
     */
    public function bulkDisable(Request $request): RedirectResponse
    {
        // Read the email addresses from the request.
        $values = $request->get('values', '');
        $emailAddresses = array_filter(explode(',', $values));
        if (empty($emailAddresses)) {
            throw new BadRequestHttpException('Provide comma separated email addresses via the "values" GET parameter.');
        }

        $this->adminUserManager->toggleEnabled($emailAddresses, false);

        // Check if the request has an referer, otherwise redirect to the index.
        $referer = $request->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('admin_admin_user_index');
    }
}
