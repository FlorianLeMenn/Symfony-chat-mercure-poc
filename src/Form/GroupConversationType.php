<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class GroupConversationType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $users = $this->em->getRepository(User::class)->findAll();
        //dd($users);

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du groupe',
            ])
            ->add('users', EntityType::class, [
                    'class'         => User::class,
                    'label'         => 'Membres du group',
                    'choice_value'  => 'id',
                    'choice_label'  => 'username',
                    'expanded'      => true,
                    'multiple'      => true,
                ]
            )
            ->add('private', ChoiceType::class, [
                    'choices' => array(
                        'Oui' => '1',
                        'Non' => '0'
                    ),
                    'label'     => 'Groupe privé',
                    'expanded'  => true,
                    'required'  => true,
                    'multiple'  => false,
                ]
            )
            ->add('Enregistrer', SubmitType::class)
        ;
    }
}