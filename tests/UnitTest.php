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

        require_once __DIR__ . '/../bootstrap.php';

        $this->entityManager = $entityManager;
    }
}
