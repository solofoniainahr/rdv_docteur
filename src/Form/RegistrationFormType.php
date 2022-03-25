<?php

namespace App\Form;

use App\Entity\Clinic;
use App\Entity\Speciality;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use Vich\UploaderBundle\Form\Type\VichImageType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => false,
                //'required' => false,
                'allow_delete' => true,
                'delete_label' => '...',
                'download_label' => '...',
                'download_uri' => true,
                //'image_uri' => true,
                //'imagine_pattern' => 'square_thumbnail_small',
                //'asset_helper' => true,
            ])
            ->add('firstname', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre prénom',
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre adresse email',
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous dever accepter le terme de condition.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Votre mot de passe',
                    'class' => 'form-control-sm'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('adress', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => 'Votre addresse'
                ]
            ])
            ->add('is_doctor', CheckboxType::class, [
                'required'  => false,
                'label' => 'Êtes vous un docteur ',
                'attr' => [
                    'id' => 'is_doctor'
                ]
            ])
            ->add('speciality', EntityType::class, [
                'label'        => false,
                'required'     => false,
                'class'        => Speciality::class,
                'choice_label' => 'name',
                'multiple'     => false,
                'expanded'     => false,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('s')
                              ->orderBy('s.name', 'ASC');
                },
                'placeholder' => 'Choisissez une spécilité',
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('clini', EntityType::class, [
                'label'        => false,
                'required'     => false,
                'class'        => Clinic::class,
                'choice_label' => 'name',
                'multiple'     => false,
                'expanded'     => false,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('c')
                              ->orderBy('c.name', 'ASC');
                },
                'placeholder' => 'Choisissez un clinic',
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
        
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
