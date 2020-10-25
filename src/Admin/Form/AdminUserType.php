<?php

namespace App\Admin\Form;

use App\Admin\Security\Voter\AdminUserVoter;
use App\Entity\Admin\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

/**
 * Create/edit form type for \App\Entity\Admin\User::class.
 */
class AdminUserType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $builder->getData();

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'admin_user.first_name',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'admin_user.last_name',
                'required' => true,
            ])
            ->add('emailAddress', EmailType::class, [
                'label' => 'admin_user.email_address',
                'required' => true,
            ])
        ;

        if ($this->security->isGranted(AdminUserVoter::UPDATE_STATUS, $user)) {
            $builder->add('isEnabled', CheckboxType::class, [
                'label' => 'admin_user.is_enabled',
                'required' => false,
            ]);
        }

        if ($this->security->isGranted(AdminUserVoter::UPDATE_ROLES, $user)) {
            // Add a checkbox for if the user is an admin, transform it to a roles array.
            $builder->add('roles', CheckboxType::class, [
                'label' => 'admin_user.is_admin',
                'required' => false,
            ]);
            $builder->get('roles')->addModelTransformer(new CallbackTransformer(
                function (array $roles) {
                    return in_array(User::ROLE_ADMIN, $roles);
                },
                function (bool $isAdmin) {
                    return $isAdmin ? [User::ROLE_ADMIN] : [];
                }
            ));
        }

        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => $options['require_password'],
            'validation_groups' => $options['require_password'] ? 'new' : null,
            'invalid_message' => 'The password fields must match.',
            'first_options' => ['label' => 'admin_user.new_password'],
            'second_options' => ['label' => 'admin_user.repeat_new_password'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => false,
        ]);
    }
}
