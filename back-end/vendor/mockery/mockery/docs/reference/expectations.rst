xpectation Declarations
========================
nce we have created a mock object, we'll often want to start defining how
exactly it should behave (and how it should be called). This is where the
Mockery expectation declarations take over.
Declaring Method Call Expectations
----------------------------------
To tell our test double to expect a call for a method with a given name, we use
the ``shouldReceive`` method:
his is the starting expectation upon which all other expectations and
constraints are appended.
We can declare more than one method call to be expected:
ll of these will adopt any chained expectations or constraints.
It is possible to declare the expectations for the method calls, along with
their return values:
here's also a shorthand way of setting up method call expectations and their
return values:
ll of these will adopt any additional chained expectations or constraints.
We can declare that a test double should not expect a call to the given method
name:
his method is a convenience method for calling ``shouldReceive()->never()``.
Declaring Method Argument Expectations
--------------------------------------
For every method we declare expectation for, we can add constraints that the
defined expectations apply only to the method calls that match the expected
argument list:
e can add a lot more flexibility to argument matching using the built in
matcher classes (see later). For example, ``\Mockery::any()`` matches any
argument passed to that position in the ``with()`` parameter list. Mockery also
allows Hamcrest library matchers - for example, the Hamcrest function
``anything()`` is equivalent to ``\Mockery::any()``.
It's important to note that this means all expectations attached only apply to
the given method when it is called with these exact arguments:
his allows for setting up differing expectations based on the arguments
provided to expected calls.
Argument matching with closures
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Instead of providing a built-in matcher for each argument, we can provide a
closure that matches all passed arguments at once:
he given closure receives all the arguments passed in the call to the expected
method. In this way, this expectation only applies to method calls where passed
arguments make the closure evaluate to true:
ny, or no arguments
^^^^^^^^^^^^^^^^^^^^
We can declare that the expectation matches a method call regardless of what
arguments are passed:
his is set by default unless otherwise specified.
We can declare that the expectation matches method calls with zero arguments:
eclaring Return Value Expectations
-----------------------------------
For mock objects, we can tell Mockery what return values to return from the
expected method calls.
For that we can use the ``andReturn()`` method:
his sets a value to be returned from the expected method call.
It is possible to set up expectation for multiple return values. By providing
a sequence of return values, we tell Mockery what value to return on every
subsequent call to the method:
he first call will return ``$value1`` and the second call will return ``$value2``.
If we call the method more times than the number of return values we declared,
Mockery will return the final value for any subsequent method call:
he same can be achieved using the alternative syntax:
t accepts a simple array instead of a list of parameters. The order of return
is determined by the numerical index of the given array with the last array
member being returned on all calls once previous return values are exhausted.
The following two options are primarily for communication with test readers:
hey mark the mock object method call as returning ``null`` or nothing.
Sometimes we want to calculate the return results of the method calls, based on
the arguments passed to the method. We can do that with the ``andReturnUsing()``
method which accepts one or more closure:
losures can be queued by passing them as extra parameters as for ``andReturn()``.
f we are mocking fluid interfaces, the following method will be helpful:
t sets the return value to the mocked class name.
Throwing Exceptions
-------------------
We can tell the method of mock objects to throw exceptions:
t will throw the given ``Exception`` object when called.
Rather than an object, we can pass in the ``Exception`` class and message to
use when throwing an ``Exception`` from the mocked method:
etting Public Properties
-------------------------
Used with an expectation so that when a matching method is called, we can cause
a mock object's public property to be set to a specified value, by using
``andSet()`` or ``set()``:
n cases where we want to call the real method of the class that was mocked and
return its result, the ``passthru()`` method tells the expectation to bypass
a return queue:
t allows expectation matching and call count validation to be applied against
real methods while still calling the real class method with the expected
arguments.
Declaring Call Count Expectations
---------------------------------
Besides setting expectations on the arguments of the method calls, and the
return values of those same calls, we can set expectations on how many times
should any method be called.
When a call count expectation is not met, a
``\Mockery\Expectation\InvalidCountException`` will be thrown.
e can declare that the expected method may be called zero or more times:
his is the default for all methods unless otherwise set.
To tell Mockery to expect an exact number of calls to a method, we can use the
following:
here ``$n`` is the number of times the method should be called.
A couple of most common cases got their shorthand methods.
To declare that the expected method must be called one time only:
o declare that the expected method must be called two times:
o declare that the expected method must never be called:
all count modifiers
^^^^^^^^^^^^^^^^^^^^
The call count expectations can have modifiers set.
If we want to tell Mockery the minimum number of times a method should be called,
we use ``atLeast()``:
`atLeast()->times(3)`` means the call must be called at least three times
(given matching method args) but never less than three times.
Similarly, we can tell Mockery the maximum number of times a method should be
called, using ``atMost()``:
`atMost()->times(3)`` means the call must be called no more than three times.
If the method gets no calls at all, the expectation will still be met.
We can also set a range of call counts, using ``between()``:
his is actually identical to using ``atLeast()->times($min)->atMost()->times($max)``
but is provided as a shorthand. It may be followed by a ``times()`` call with no
parameter to preserve the APIs natural language readability.
Expectation Declaration Utilities
---------------------------------
Declares that this method is expected to be called in a specific order in
relation to similarly marked methods.
he order is dictated by the order in which this modifier is actually used when
setting up mocks.
Declares the method as belonging to an order group (which can be named or
numbered). Methods within a group can be called in any order, but the ordered
calls from outside the group are ordered in relation to the group:
e can set up so that method1 is called before group1 which is in turn called
before method2.
When called prior to ``ordered()`` or ``ordered(group)``, it declares this
ordering to apply across all mock objects (not just the current mock):
his allows for dictating order expectations across multiple mocks.
The ``byDefault()`` marks an expectation as a default. Default expectations are
applied unless a non-default expectation is created:
hese later expectations immediately replace the previously defined default.
This is useful so we can setup default mocks in our unit test ``setup()`` and
later tweak them in specific tests as needed.
Returns the current mock object from an expectation chain:
seful where we prefer to keep mock setups as a single statement, e.g.:
