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
            new \Twig_SimpleFunction('get_description', array($this, 'getDescription')),
        );
    }

    /**
     * @param string $description
     *
     * @return string
     */
    public function getDescription($description)
    {
        return mb_substr(trim($description), 0, 255);
    }

    /**
     * @param string $description
     *
     * @return string
     */
    public function getSeoDescription($description)
    {
        $value = strip_tags(trim($description));
        return mb_strlen($value) <= 255 ? $value : mb_substr($value, 0, 255) . '...';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'seo_extension';
    }
}
