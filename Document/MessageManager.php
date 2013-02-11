<?php

/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DABSquared\PushNotificationsBundle\Entity;

use DABSquared\PushNotificationsBundle\Model\MessageManager as BaseMessageManager;


use Doctrine\ODM\MongoDB\DocumentManager;
use DABSquared\PushNotificationsBundle\Model\DeviceInterface;
use DABSquared\PushNotificationsBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Default ODM CommentManager.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class MessageManager extends BaseMessageManager
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \Doctrine\ODM\MongoDB\DocumentManager                       $dm
     * @param string                                                      $class
     */
    public function __construct(EventDispatcherInterface $dispatcher, DocumentManager $dm, $class)
    {
        parent::__construct($dispatcher);

        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);

        $metadata = $dm->getClassMetadata($class);
        $this->class = $metadata->name;
    }


    /**
     * Performs persisting of the message.
     *
     * @param MessageInterface message
     */
    protected function doSaveMessage(MessageInterface $message)
    {
        $this->em->persist($message);
        $this->em->flush();
    }


    /**
     * {@inheritDoc}
     */
    public function isNewMessage(DeviceInterface $message)
    {
        return !$this->dm->getUnitOfWork()->isInIdentityMap($message);
    }

    /**
     * Returns the fully qualified comment thread class name
     *
     * @return string
     **/
    public function getClass()
    {
        return $this->class;
    }

}
