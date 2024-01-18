<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'label' => 'First name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 25]),
                ],
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Last name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 25]),
                ],
            ])
            ->add('age', TextType::class, [
                'label' => 'Age',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 3]),
                ],
            ])
            ->add('biography', TextType::class, [
                'label' => 'Biography',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 10, 'max' => 500]),
                ],
            ])
            ->add('avatar_name', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Avatar',
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg'
                        ]
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
