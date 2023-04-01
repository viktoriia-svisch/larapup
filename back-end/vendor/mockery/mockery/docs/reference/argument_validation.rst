rgument Validation
===================
The arguments passed to the ``with()`` declaration when setting up an
expectation determine the criteria for matching method calls to expectations.
Thus, we can setup up many expectations for a single method, each
differentiated by the expected arguments. Such argument matching is done on a
"best fit" basis.  This ensures explicit matches take precedence over
generalised matches.
An explicit match is merely where the expected argument and the actual
argument are easily equated (i.e. using ``===`` or ``==``). More generalised
matches are possible using regular expressions, class hinting and the
available generic matchers. The purpose of generalised matchers is to allow
arguments be defined in non-explicit terms, e.g. ``Mockery::any()`` passed to
``with()`` will match **any** argument in that position.
Mockery's generic matchers do not cover all possibilities but offers optional
support for the Hamcrest library of matchers. Hamcrest is a PHP port of the
similarly named Java library (which has been ported also to Python, Erlang,
etc). By using Hamcrest, Mockery does not need to duplicate Hamcrest's already
impressive utility which itself promotes a natural English DSL.
The examples below show Mockery matchers and their Hamcrest equivalent, if there
is one. Hamcrest uses functions (no namespacing).
he most common matcher is the ``with()`` matcher:
t tells mockery that it should receive a call to the ``foo`` method with the
integer ``1`` as an argument. In cases like this, Mockery first tries to match
the arguments using ``===`` (identical) comparison operator. If the argument is
a primitive, and if it fails the identical comparison, Mockery does a fallback
to the ``==`` (equals) comparison operator.
When matching objects as arguments, Mockery only does the strict ``===``
comparison, which means only the same ``$object`` will match:
 different instance of ``stdClass`` will **not** match.
f we need a loose comparison of objects, we can do that using Hamcrest's
``equalTo`` matcher:
n cases when we don't care about the type, or the value of an argument, just
that any argument is present, we use ``any()``:
nything and everything passed in this argument slot is passed unconstrained.
Validating Types and Resources
------------------------------
The ``type()`` matcher accepts any string which can be attached to ``is_`` to
form a valid type check.
To match any PHP resource, we could do the following:
t will return a ``true`` from an ``is_resource()`` call, if the provided
argument to the method is a PHP resource. For example, ``\Mockery::type('float')``
or Hamcrest's ``floatValue()`` and ``typeOf('float')`` checks use ``is_float()``,
and ``\Mockery::type('callable')`` or Hamcrest's ``callable()`` uses
``is_callable()``.
The ``type()`` matcher also accepts a class or interface name to be used in an
``instanceof`` evaluation of the actual argument. Hamcrest uses ``anInstanceOf()``.
A full list of the type checkers is available at
`php.net <http://www.php.net/manual/en/ref.var.php>`_ or browse Hamcrest's function
list in
`the Hamcrest code <https://github.com/hamcrest/hamcrest-php/blob/master/hamcrest/Hamcrest.php>`_.
omplex Argument Validation
---------------------------
If we want to perform a complex argument validation, the ``on()`` matcher is
invaluable. It accepts a closure (anonymous function) to which the actual
argument will be passed.
f the closure evaluates to (i.e. returns) boolean ``true`` then the argument is
assumed to have matched the expectation.
e can also perform argument validation by passing a closure to ``withArgs()``
method. The closure will receive all arguments passed in the call to the expected
method and if it evaluates (i.e. returns) to boolean ``true``, then the list of
arguments is assumed to have matched the expectation:
he closure can also handle optional parameters, so if an optional parameter is
missing in the call to the expected method, it doesn't necessary means that the
list of arguments doesn't match the expectation.
f we would like to match an argument against a regular expression, we can use
the ``\Mockery::pattern()``:
he ``ducktype()`` matcher is an alternative to matching by class type:
t matches any argument which is an object containing the provided list of
methods to call.
dditional Argument Matchers
----------------------------
The ``not()`` matcher matches any argument which is not equal or identical to
the matcher's parameter:
`anyOf()`` matches any argument which equals any one of the given parameters:
`notAnyOf()`` matches any argument which is not equal or identical to any of
the given parameters:
`subset()`` matches any argument which is any array containing the given array
subset:
his enforces both key naming and values, i.e. both the key and value of each
actual element is compared.
`contains()`` matches any argument which is an array containing the listed
values:
he naming of keys is ignored.
``hasKey()`` matches any argument which is an array containing the given key
name:
`hasValue()`` matches any argument which is an array containing the given
value:
