<?php

namespace App\Admin\Controller;

use App\Admin\Security\Voter\AdminUserVoter;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminUserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private UserPasswordEncoderInterface $passwordEncoder;
    private PaginatorInterface $paginator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder, PaginatorInterface $paginator)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->passwordEncoder = $passwordEncoder;
        $this->paginator = $paginator;
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
                PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['u.firstName', 'u.emailAddress', 'u.lastActiveAt'],
            ],
        );

        return $this->render('admin/admin_user/index.html.twig', [
            'pagination' => $pagination,
            'searchQuery' => $searchQuery,
        ]);
    }
}
