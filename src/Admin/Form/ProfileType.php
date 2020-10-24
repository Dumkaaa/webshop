<?php

namespace App\Admin\Form;

use App\Entity\Admin\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Profile form type for \App\Entity\Admin\User::class.
 */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'admin_user.full_name',
                'required' => false,
                'disabled' => true,
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('emailAddress', EmailType::class, [
                'label' => 'admin_user.email_address',
                'required' => false,
                'disabled' => true,
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'admin_user.new_password'],
                'second_options' => ['label' => 'admin_user.repeat_new_password'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
