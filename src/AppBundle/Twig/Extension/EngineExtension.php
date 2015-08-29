<?php

namespace AppBundle\Twig\Extension;

use AppBundle\Manager\EngineManager;

/**
 * Class EngineExtension.
 */
class EngineExtension extends \Twig_Extension
{
    /**
     * @var EngineManager
     */
    private $engineManager;

    /**
     * Constructor.
     *
     * @param EngineManager $engineManager
     */
    public function __construct(EngineManager $engineManager)
    {
        $this->engineManager = $engineManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_engines', array($this, 'getEngines')),
        );
    }

    /**
     * Get engines.
     *
     * @return array
     */
    public function getEngines()
    {
        return $this->engineManager->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'engine_extension';
    }
}
