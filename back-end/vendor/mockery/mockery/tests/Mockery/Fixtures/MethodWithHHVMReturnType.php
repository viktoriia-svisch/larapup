<?hh
namespace test\Mockery\Fixtures;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MethodWithHHVMReturnType extends MockeryTestCase
{
    public function nullableHHVMArray() : ?array<string, bool>
    {
        return array('key' => true);
    }
    public function HHVMVoid() : void
    {
        return;
    }
    public function HHVMMixed() : mixed
    {
        return null;
    }
    public function HHVMThis() : this
    {
        return $this;
    }
    public function HHVMString() : string
    {
        return 'a string';
    }
    public function HHVMImmVector() : ImmVector<int>
    {
        return new ImmVector([1, 2, 3]);
    }
}
