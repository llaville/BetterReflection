<?php
declare(strict_types=1);

namespace Roave\BetterReflectionTest\Reflector;

use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\FindReflectionsInTree;
use Roave\BetterReflection\SourceLocator\Ast\PhpParserFactory;
use Roave\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use Roave\BetterReflection\SourceLocator\Located\LocatedSource;

/**
 * @covers \Roave\BetterReflection\SourceLocator\Ast\FindReflectionsInTree
 */
class FindReflectionsInTreeTest extends \PHPUnit\Framework\TestCase
{
    private function getAstForString(string $php) : array
    {
        return PhpParserFactory::create()->parse($php);
    }

    public function testInvokeDoesNotCallReflectNodesWhenNoNodesFoundInEmptyAst() : void
    {
        /** @var NodeToReflection|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this->createMock(NodeToReflection::class);

        $strategy->expects($this->never())
            ->method('__invoke');

        /** @var Reflector|\PHPUnit_Framework_MockObject_MockObject $reflector */
        $reflector     = $this->createMock(Reflector::class);
        $locatedSource = new LocatedSource('<?php', null);

        self::assertSame(
            [],
            (new FindReflectionsInTree($strategy))->__invoke(
                $reflector,
                $this->getAstForString($locatedSource->getSource()),
                new IdentifierType(IdentifierType::IDENTIFIER_CLASS),
                $locatedSource
            )
        );
    }

    public function testInvokeDoesNotCallReflectNodesWhenNoNodesFoundInPopulatedAst() : void
    {
        /** @var NodeToReflection|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this->createMock(NodeToReflection::class);

        $strategy->expects($this->never())
            ->method('__invoke');

        /** @var Reflector|\PHPUnit_Framework_MockObject_MockObject $reflector */
        $reflector     = $this->createMock(Reflector::class);
        $locatedSource = new LocatedSource('<?php echo "Hello world";', null);

        self::assertSame(
            [],
            (new FindReflectionsInTree($strategy))->__invoke(
                $reflector,
                $this->getAstForString($locatedSource->getSource()),
                new IdentifierType(IdentifierType::IDENTIFIER_CLASS),
                $locatedSource
            )
        );
    }

    public function testInvokeCallsReflectNodesForClassWithoutNamespace() : void
    {
        /** @var NodeToReflection|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this->createMock(NodeToReflection::class);

        $mockReflection = $this->createMock(ReflectionClass::class);

        $strategy->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($mockReflection));

        /** @var Reflector|\PHPUnit_Framework_MockObject_MockObject $reflector */
        $reflector     = $this->createMock(Reflector::class);
        $locatedSource = new LocatedSource('<?php class Foo {}', null);

        self::assertSame(
            [
                $mockReflection,
            ],
            (new FindReflectionsInTree($strategy))->__invoke(
                $reflector,
                $this->getAstForString($locatedSource->getSource()),
                new IdentifierType(IdentifierType::IDENTIFIER_CLASS),
                $locatedSource
            )
        );
    }

    public function testInvokeCallsReflectNodesForNamespacedClass() : void
    {
        /** @var NodeToReflection|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this->createMock(NodeToReflection::class);

        $mockReflection = $this->createMock(ReflectionClass::class);

        $strategy->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($mockReflection));

        /** @var Reflector|\PHPUnit_Framework_MockObject_MockObject $reflector */
        $reflector     = $this->createMock(Reflector::class);
        $locatedSource = new LocatedSource('<?php namespace Foo { class Bar {} }', null);

        self::assertSame(
            [
                $mockReflection,
            ],
            (new FindReflectionsInTree($strategy))->__invoke(
                $reflector,
                $this->getAstForString($locatedSource->getSource()),
                new IdentifierType(IdentifierType::IDENTIFIER_CLASS),
                $locatedSource
            )
        );
    }

    public function testInvokeCallsReflectNodesForFunction() : void
    {
        /** @var NodeToReflection|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this->createMock(NodeToReflection::class);

        $mockReflection = $this->createMock(ReflectionFunction::class);

        $strategy->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($mockReflection));

        /** @var Reflector|\PHPUnit_Framework_MockObject_MockObject $reflector */
        $reflector     = $this->createMock(Reflector::class);
        $locatedSource = new LocatedSource('<?php function foo() {}', null);

        self::assertSame(
            [
                $mockReflection,
            ],
            (new FindReflectionsInTree($strategy))->__invoke(
                $reflector,
                $this->getAstForString($locatedSource->getSource()),
                new IdentifierType(IdentifierType::IDENTIFIER_FUNCTION),
                $locatedSource
            )
        );
    }

    public function testAnonymousClassCreatedInFunction() : void
    {
        /** @var NodeToReflection|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this->createMock(NodeToReflection::class);

        $mockReflectionFunction = $this->createMock(ReflectionFunction::class);
        $mockReflectionClass    = $this->createMock(ReflectionClass::class);

        $strategy->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnOnConsecutiveCalls($mockReflectionFunction, $mockReflectionClass);

        /** @var Reflector|\PHPUnit_Framework_MockObject_MockObject $reflector */
        $reflector     = $this->createMock(Reflector::class);
        $locatedSource = new LocatedSource('<?php function foo() {return new class {};}', null);

        self::assertSame(
            [
                $mockReflectionClass,
            ],
            (new FindReflectionsInTree($strategy))->__invoke(
                $reflector,
                $this->getAstForString($locatedSource->getSource()),
                new IdentifierType(IdentifierType::IDENTIFIER_CLASS),
                $locatedSource
            )
        );
    }
}
