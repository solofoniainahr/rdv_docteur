<?php

namespace App\Form;

use App\Data\SearchDoctorData;
use App\Entity\Speciality;
use App\Form\DataTransformer\SpecialityToNumberTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchFormDoctor extends AbstractType 
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => false,
                'required' => false, 
                'attr' => [
                    'placeholder' => 'Prénom de médécin'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
                'required' => false, 
                'attr' => [
                    'placeholder' => 'Nom de médécin'
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
                'placeholder' => 'Spécialité de médécin',
            ])
            ->add('location', TextType::class, [
                'label' => false,
                'required' => false, 
                'attr' => [
                    'placeholder' => 'Lieu de médécin'
                ]
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'data_class' => SearchDoctorData::class
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}