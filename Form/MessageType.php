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
            ))
            ->add('deviceName', 'text', array(
                'label' => 'Device Name:',
                'mapped' => false,
                'required'  => false,
                'attr'=>array('placeholder'=>'Device Name'),
            )) ;



        $refreshDevice = function ($form, $device) use ($factory) {
            $form->add($factory->createNamed('device','entity',null, array(
                'class'         => 'DABSquaredPushBundle:Device',
                'auto_initialize' => false,
                'label' => '',
                'required'  => false,
                'query_builder' => function (EntityRepository $repository) use ($device) {
                    $qb = $repository->createQueryBuilder('device');

                    if($device instanceof \DABSquared\PushNotificationsBundle\Model\Device) {
                        $qb = $qb->where('device.id = :device')
                            ->setParameter('device', $device->getId());
                    } elseif(is_numeric($device)) {
                        $qb = $qb->where('device.id = :device_id')
                            ->setParameter('device_id', $device);
                    } else {
                        $qb = $qb->where('device.id = 1');
                    }

                    return $qb;
                }
            )));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (\Symfony\Component\Form\FormEvent $event) use ($refreshDevice) {
            $form = $event->getForm();
            $data = $event->getData();

            if($data == null) {
                $refreshDevice($form, null);
            }

        });


    }

    public function getName()
    {
        return 'dabsquared_pushbundle_messagetype';
    }
}
