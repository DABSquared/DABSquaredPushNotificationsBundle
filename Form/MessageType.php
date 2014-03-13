<?php

namespace DABSquared\PushNotificationsBundle\Form;

use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use DABSquared\PushNotificationsBundle\Device\Types;
use DABSquared\PushNotificationsBundle\Message\MessageStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;


class MessageType extends AbstractType
{


    public function __construct(){
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder
            ->add('message', 'textarea', array())
            ->add('type', 'choice',array(
                'choices' => array(Types::OS_IOS => 'iOS',Types::OS_SAFARI => 'Safari', Types::OS_BLACKBERRY => 'Blackberry' , Types::OS_WINDOWSMOBILE => 'Windows Mobile' , Types::OS_ANDROID_GCM => 'Android GCM' ,  Types::OS_ANDROID_C2DM => 'Android C2DM'),
                'preferred_choices' => array('iOS'),
                'mapped' => false,
                'label' => 'What devices to send messages to?',
                'expanded' => true,
                'multiple' => true
            ));
    }

    public function getName()
    {
        return 'dabsquared_pushbundle_messagetype';
    }
}
