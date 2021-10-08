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
            ->add('name', TextType::class)
            ->add('users', EntityType::class, array(
                'class'     => User::class,
                'choice_value' => 'id',
                'choice_label' => 'username',
                'expanded'  => true,
                'multiple'  => true,
            ))
//            ->add('users', ChoiceType::class, [
//                    'label'     => false,
//                    'required' => true,
//                    'choices'  => $users,
//                    'choice_value' => 'id',
//                    'choice_label' => function(?User $user) {
//                        return $user ? strtoupper($user->getUsername()) : '';
//                    },
//                    'expanded' => true,
//                    'multiple' => true,
//                ]
//            )
            ->add('Enregistrer', SubmitType::class)
        ;
    }
}