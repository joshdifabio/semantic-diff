<?php
namespace SemanticDiff\Diff;

use PHPUnit_Framework_TestCase;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Parser;
use PhpParser\Lexer;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ClassDiffTest extends PHPUnit_Framework_TestCase
{
    private $factory;
    
    public function setUp()
    {
        $this->factory = new Factory;
    }
    
    /**
     * @dataProvider provideGetStatus
     */
    public function testGetStatus($expectedStatus, Class_ $base = null, Class_ $head = null)
    {
        $diff = $this->factory->createDiff($base, $head);
        $this->assertEquals($expectedStatus, $diff->getStatus());
    }
    
    public function provideGetStatus()
    {
        $parser = new Parser(new Lexer);
        
        foreach ($this->getTestCases() as $testId => $testCase) {
            $baseClassNode = null;
            $headClassNode = null;
            
            foreach ($parser->parse($testCase[1]) as $node) {
                if ($node instanceof Class_) {
                    $baseClassNode = $node;
                    break;
                }
            }
            
            foreach ($parser->parse($testCase[2]) as $node) {
                if ($node instanceof Class_) {
                    $headClassNode = $node;
                    break;
                }
            }
            
            yield $testId => [
                $testCase[0],
                $baseClassNode,
                $headClassNode,
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
        ];
    }
}
