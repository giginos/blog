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

        if ($this->entityManager === null) {
            require_once dirname(__FILE__) . '/../bootstrap.php';
        }

        if (!isset($entityManager)) {
            throw new \Exception(sprintf('Unable to load entityManager %s.', dirname(__FILE__) . '/../bootstrap.php'));
        }

        $this->entityManager = $entityManager;
    }
}
