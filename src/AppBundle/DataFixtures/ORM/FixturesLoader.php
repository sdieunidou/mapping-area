<?php

namespace AppBundle\DataFixtures\ORM;

use Hautelook\AliceBundle\Alice\DataFixtureLoader;

class FixturesLoader extends DataFixtureLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getFixtures()
    {
        return  array(
            __DIR__.'/engine.yml',

        );
    }
}
