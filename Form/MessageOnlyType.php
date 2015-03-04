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


class MessageOnlyType extends AbstractType
{


    public function __construct(){
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder->add('title', 'text', array());
        $builder->add('message', 'textarea', array());
        $builder->add('sound', 'text', array());
        $builder->add('badge', 'integer', array());
        $builder->add('customData', 'textarea', array('attr' => array(
            'style' => 'height:300px;'
        )));

    }

    public function getName()
    {
        return 'dabsquared_pushbundle_message_only_type';
    }
}
