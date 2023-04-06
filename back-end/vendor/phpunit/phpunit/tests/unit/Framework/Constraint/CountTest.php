<?php
namespace PHPUnit\Framework\Constraint;
class CountTest extends ConstraintTestCase
{
    public function testCount(): void
    {
        $countConstraint = new Count(3);
        $this->assertTrue($countConstraint->evaluate([1, 2, 3], '', true));
        $countConstraint = new Count(0);
        $this->assertTrue($countConstraint->evaluate([], '', true));
        $countConstraint = new Count(2);
        $it              = new \TestIterator([1, 2]);
        $ia              = new \TestIteratorAggregate($it);
        $ia2             = new \TestIteratorAggregate2($ia);
        $this->assertTrue($countConstraint->evaluate($it, '', true));
        $this->assertTrue($countConstraint->evaluate($ia, '', true));
        $this->assertTrue($countConstraint->evaluate($ia2, '', true));
    }
    public function testCountDoesNotChangeIteratorKey(): void
    {
        $countConstraint = new Count(2);
        $it = new \TestIterator([1, 2]);
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(1, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(2, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertFalse($it->valid());
        $it = new \TestIterator2([1, 2]);
        $countConstraint = new Count(2);
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(1, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(2, $it->current());
        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertFalse($it->valid());
        $it = new \TestIterator([1, 2]);
        $ia = new \TestIteratorAggregate($it);
        $countConstraint = new Count(2);
        $countConstraint->evaluate($ia, '', true);
        $this->assertEquals(1, $it->current());
        $it->next();
        $countConstraint->evaluate($ia, '', true);
        $this->assertEquals(2, $it->current());
        $it->next();
        $countConstraint->evaluate($ia, '', true);
        $this->assertFalse($it->valid());
        $it  = new \TestIterator([1, 2]);
        $ia  = new \TestIteratorAggregate($it);
        $ia2 = new \TestIteratorAggregate2($ia);
        $countConstraint = new Count(2);
        $countConstraint->evaluate($ia2, '', true);
        $this->assertEquals(1, $it->current());
        $it->next();
        $countConstraint->evaluate($ia2, '', true);
        $this->assertEquals(2, $it->current());
        $it->next();
        $countConstraint->evaluate($ia2, '', true);
        $this->assertFalse($it->valid());
    }
    public function testCountGeneratorsDoNotRewind(): void
    {
        $generatorMaker = new \TestGeneratorMaker;
        $countConstraint = new Count(3);
        $generator = $generatorMaker->create([1, 2, 3]);
        $this->assertEquals(1, $generator->current());
        $countConstraint->evaluate($generator, '', true);
        $this->assertEquals(null, $generator->current());
        $countConstraint = new Count(2);
        $generator = $generatorMaker->create([1, 2, 3]);
        $this->assertEquals(1, $generator->current());
        $generator->next();
        $this->assertEquals(2, $generator->current());
        $countConstraint->evaluate($generator, '', true);
        $this->assertEquals(null, $generator->current());
        $countConstraint = new Count(1);
        $generator = $generatorMaker->create([1, 2, 3]);
        $this->assertEquals(1, $generator->current());
        $generator->next();
        $this->assertEquals(2, $generator->current());
        $generator->next();
        $this->assertEquals(3, $generator->current());
        $countConstraint->evaluate($generator, '', true);
        $this->assertEquals(null, $generator->current());
    }
    public function testCountTraversable(): void
    {
        $countConstraint = new Count(5);
        $datePeriod = new \DatePeriod('R4/2017-05-01T00:00:00Z/P1D');
        $this->assertInstanceOf(\Traversable::class, $datePeriod);
        $this->assertNotInstanceOf(\Iterator::class, $datePeriod);
        $this->assertNotInstanceOf(\IteratorAggregate::class, $datePeriod);
        $this->assertTrue($countConstraint->evaluate($datePeriod, '', true));
    }
}
