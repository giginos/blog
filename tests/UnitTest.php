<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;

abstract class UnitTest extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        require_once dirname(__FILE__) . '/../bootstrap.php';

        if (!isset($entityManager)) {
            throw new \Exception('Unable to load entityManager.');
        }

        $this->entityManager = $entityManager;
    }
}
