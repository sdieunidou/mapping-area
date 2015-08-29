<?php

namespace AppBundle\Twig\Extension;

/**
 * Class SeoExtension.
 */
class SeoExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_seo_description', array($this, 'getSeoDescription')),
        );
    }

    /**
     * @param string $description
     *
     * @return string
     */
    public function getSeoDescription($description)
    {
        return mb_substr(strip_tags(trim($description)), 0, 255);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'seo_extension';
    }
}
