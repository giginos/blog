<?php

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
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name , $data, $dataName);

        require_once dirname(__FILE__) . '/../bootstrap.php';

        if (!isset($entityManager)) {
            throw new \Exception(sprintf('Unable to load entityManager %s.', dirname(__FILE__) . '/../bootstrap.php'));
        }

        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Paste some global ramp-up stuff here
    }
}
