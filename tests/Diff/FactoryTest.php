<?php
namespace SemanticDiff\Diff;

use PHPUnit_Framework_TestCase;
use PhpParser\Parser;
use PhpParser\Lexer;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $factory;
    
    public function setUp()
    {
        $this->factory = new Factory;
    }
    
    /**
     * @dataProvider provideGetStatus
     */
    public function testGetStatus($expectedStatus, array $base = null, array $head = null)
    {
        $diff = $this->factory->createDiff($base, $head);
        $this->assertEquals($expectedStatus, $diff->getStatus());
    }
    
    public function provideGetStatus()
    {
        $parser = new Parser(new Lexer);
        
        foreach ($this->getTestCases() as $testId => $testCase) {
            yield $testId => [
                $testCase[0],
                $parser->parse($testCase[1]),
                $parser->parse($testCase[2]),
            ];
        }
    }
    
    public function getTestCases()
    {
        return [
            [
                Status::NO_CHANGES,
                <<<CODE
<?php
class Foo
{
    
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    // hello world!
}
CODE
                ,
            ],
            [
                Status::API_ADDITIONS,
                <<<CODE
<?php
class Foo
{
    
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld() {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld() {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{

}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld() {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar) {}
}
CODE
                ,
            ],
            [
                Status::API_ADDITIONS,
                <<<CODE
<?php
class Foo
{
    public function helloWorld() {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = 1) {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar) {}
}
CODE
                ,
            ],
            [
                Status::API_ADDITIONS,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function newMethod(\$foobar) {}
    public function helloWorld(\$foobar) {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function newMethod(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_ADDITIONS,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function newMethod(\$foobar) {}
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
final class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
final class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
abstract class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
abstract class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    const FOO_BAR = 'this';
    
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_ADDITIONS,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    const FOO_BAR = 'this';
    
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    const FOO_BAR = 'this';
                
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    const FOO_BAR = 'that';
    
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = 1) {}
}
CODE
                ,
            ],
            [
                Status::INTERNAL_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = null) { return 1; }
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    protected \$foobar;
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
}
CODE
                ,
            ],
            [
                Status::INTERNAL_CHANGES,
                <<<CODE
<?php
class Foo
{
    private \$foobar;
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
}
CODE
                ,
            ],
            [
                Status::INTERNAL_CHANGES,
                <<<CODE
<?php
class Foo
{
    private \$foobar;
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    protected \$foobar;
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
class Foo
{
    public \$foobar;
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    protected \$foobar;
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    public \$foobar = 'hello';
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public \$foobar;
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    protected \$foobar = 'hello';
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    protected \$foobar;
}
CODE
                ,
            ],
            [
                Status::INTERNAL_CHANGES,
                <<<CODE
<?php
class Foo
{
    private \$foobar = 'hello';
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    private \$foobar;
}
CODE
                ,
            ],
            [
                Status::INTERNAL_CHANGES,
                <<<CODE
<?php
namespace Foo;

class Bar
{
    private \$foobar = 'hello';
}

echo "Hello world!";
CODE
                ,
                <<<CODE
<?php
namespace Foo;

echo "Hello world!";

class Bar
{
    
}
CODE
                ,
            ],
            [
                Status::NO_CHANGES,
                <<<CODE
<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_DB
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * TODO
 *
 * @category    Mage
 * @package     Mage_Db
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_DB_Exception extends Exception {

}
CODE
                ,
                <<<CODE
<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_DB
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * TODO
 *
 * @category    Mage
 * @package     Mage_Db
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_DB_Exception extends Exception {

}
CODE
                ,
            ],
            [
                Status::NO_CHANGES,
                <<<CODE
<?php
namespace Foo;

class Bar implements \Iterator
{
    
}
CODE
                ,
                <<<CODE
<?php
namespace Foo;

class Bar implements \Iterator
{
    
}
CODE
                ,
            ],
            [
                Status::INCOMPATIBLE_API,
                <<<CODE
<?php
namespace Foo;

class Bar implements \Iterator
{
    
}
CODE
                ,
                <<<CODE
<?php
namespace Foo;

class Bar implements \IteratorAggregate
{
    
}
CODE
                ,
            ],
            [
                Status::NO_CHANGES,
                <<<CODE
<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
* Class for viewer
*
* @category   Mage
* @package    Mage_Connect
* @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Maged_View
{
    /**
    * Internal cache
    *
    * @var array
    */
    protected \$_data = array();

    /**
    * Constructor
    */
    public function __construct()
    {

    }

    /**
    * Retrieve Controller as singleton
    *
    * @return Maged_Controller
    */
    public function controller()
    {
        return Maged_Controller::singleton();
    }

    /**
    * Create url by action and params
    *
    * @param mixed \$action
    * @param mixed \$params
    * @return string
    */
    public function url(\$action='', \$params=array())
    {
        return \$this->controller()->url(\$action, \$params);
    }

    /**
    * Retrieve base url
    *
    * @return string
    */
    public function baseUrl()
    {
        return str_replace('\\\\', '/', dirname(\$_SERVER['SCRIPT_NAME']));
    }

    /**
    * Retrieve url of magento
    *
    * @return string
    */
    public function mageUrl()
    {
        return str_replace('\\\\', '/', dirname(\$this->baseUrl()));
    }

    /**
    * Include template
    *
    * @param string \$name
    * @return string
    */
    public function template(\$name)
    {
        ob_start();
        include \$this->controller()->filepath('template/'.\$name);
        return ob_get_clean();
    }

    /**
    * Set value for key
    *
    * @param string \$key
    * @param mixed \$value
    * @return Maged_Controller
    */
    public function set(\$key, \$value)
    {
        \$this->_data[\$key] = \$value;
        return \$this;
    }

    /**
    * Get value by key
    *
    * @param string \$key
    * @return mixed
    */
    public function get(\$key)
    {
        return isset(\$this->_data[\$key]) ? \$this->_data[\$key] : null;
    }

    /**
    * Translator
    *
    * @param string \$string
    * @return string
    */
    public function __(\$string)
    {
        return \$string;
    }

    /**
    * Retrieve link for header menu
    *
    * @param mixed \$action
    */
    public function getNavLinkParams(\$action)
    {
        \$params = 'href="'.\$this->url(\$action).'"';
        if (\$this->controller()->getAction()==\$action) {
            \$params .= ' class="active"';
        }
        return \$params;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return \$this->controller()->getFormKey();
    }
}
CODE
                ,
                <<<CODE
<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
* Class for viewer
*
* @category   Mage
* @package    Mage_Connect
* @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Maged_View
{
    /**
    * Internal cache
    *
    * @var array
    */
    protected \$_data = array();

    /**
    * Constructor
    */
    public function __construct()
    {

    }

    /**
    * Retrieve Controller as singleton
    *
    * @return Maged_Controller
    */
    public function controller()
    {
        return Maged_Controller::singleton();
    }

    /**
    * Create url by action and params
    *
    * @param mixed \$action
    * @param mixed \$params
    * @return string
    */
    public function url(\$action='', \$params=array())
    {
        return \$this->controller()->url(\$action, \$params);
    }

    /**
    * Retrieve base url
    *
    * @return string
    */
    public function baseUrl()
    {
        return str_replace('\\\\', '/', dirname(\$_SERVER['SCRIPT_NAME']));
    }

    /**
    * Retrieve url of magento
    *
    * @return string
    */
    public function mageUrl()
    {
        return str_replace('\\\\', '/', dirname(\$this->baseUrl()));
    }

    /**
    * Include template
    *
    * @param string \$name
    * @return string
    */
    public function template(\$name)
    {
        ob_start();
        include \$this->controller()->filepath('template/'.\$name);
        return ob_get_clean();
    }

    /**
    * Set value for key
    *
    * @param string \$key
    * @param mixed \$value
    * @return Maged_Controller
    */
    public function set(\$key, \$value)
    {
        \$this->_data[\$key] = \$value;
        return \$this;
    }

    /**
    * Get value by key
    *
    * @param string \$key
    * @return mixed
    */
    public function get(\$key)
    {
        return isset(\$this->_data[\$key]) ? \$this->_data[\$key] : null;
    }

    /**
    * Translator
    *
    * @param string \$string
    * @return string
    */
    public function __(\$string)
    {
        return \$string;
    }

    /**
    * Retrieve link for header menu
    *
    * @param mixed \$action
    */
    public function getNavLinkParams(\$action)
    {
        \$params = 'href="'.\$this->url(\$action).'"';
        if (\$this->controller()->getAction()==\$action) {
            \$params .= ' class="active"';
        }
        return \$params;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return \$this->controller()->getFormKey();
    }
}
CODE
                ,
            ],
            [
                Status::INTERNAL_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobaz) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobaz = 1) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobar = 2) {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobaz = 1) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld() {}
}
CODE
                ,
            ],
            [
                Status::API_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld(\$foobaz) {}
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld() {}
}
CODE
                ,
            ],
            [
                Status::NO_CHANGES,
                <<<CODE
<?php
class Foo
{
    public function helloWorld()
    {
        list(,,,\$hello) = [1,2,3,4];
    }
}
CODE
                ,
                <<<CODE
<?php
class Foo
{
    public function helloWorld()
    {
        list(,,,\$hello) = [1,2,3,4];
    }
}
CODE
                ,
            ],
        ];
    }
}
