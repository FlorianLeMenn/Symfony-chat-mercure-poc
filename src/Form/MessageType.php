<?php

namespace App\Form;

use App\Entity\GroupConversation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;


class MessageType extends AbstractType
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
            ->add('content', TextareaType::class)
            ->add('conversation', EntityType::class, array(
                'class'     => GroupConversation::class,
                'choice_value' => 'id',
                'choice_label' => 'name',
                'expanded'  => true,
                'multiple'  => false,
            ))
            ->add('Enregistrer', SubmitType::class)
        ;
    }
}