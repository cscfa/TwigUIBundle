<?php
/**
 * This file is a part of CSCFA TwigUi project.
 *
 * The TwigUi project is a twig builder written in php
 * with Symfony2 framework.
 *
 * PHP version 5.5
 *
 * @category Test
 *
 * @author   Matthieu VALLANCE <matthieu.vallance@cscfa.fr>
 * @license  http://opensource.org/licenses/MIT MIT
 * @filesource
 *
 * @link     http://cscfa.fr
 */

namespace Cscfa\Bundle\TwigUIBundle\Test;

/**
 * SetPropertyObject.
 *
 * The SetPropertyObject is used to test an instance
 * that need object with setPropertyMethod.
 *
 * @category Test
 *
 * @author   Matthieu VALLANCE <matthieu.vallance@cscfa.fr>
 * @license  http://opensource.org/licenses/MIT MIT
 *
 * @link     http://cscfa.fr
 *
 * @SuppressWarnings(PHPMD)
 */
class AddPropertyObject
{
    /**
     * add property.
     *
     * This method is a null pattern
     * method to be used in test.
     *
     * @param mixed $param The parameter
     */
    public function addProperty($param)
    {
        return;
    }
}
