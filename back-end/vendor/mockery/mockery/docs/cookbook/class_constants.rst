lass Constants
===============
When creating a test double for a class, Mockery does not create stubs out of
any class constants defined in the class we are mocking. Sometimes though, the
non-existence of these class constants, setup of the test, and the application
code itself, it can lead to undesired behavior, and even a PHP error:
``PHP Fatal error:  Uncaught Error: Undefined class constant 'FOO' in ...```
While supporting class constants in Mockery would be possible, it does require
an awful lot of work, for a small number of use cases.
Named Mocks
-----------
We can, however, deal with these constants in a way supported by Mockery - by
using :ref:`creating-test-doubles-named-mocks`.
A named mock is a test double that has a name of the class we want to mock, but
under it is a stubbed out class that mimics the real class with canned responses.
Lets look at the following made up, but not impossible scenario:
ur ``MyClass`` calls a ``Fetcher`` that fetches some resource from somewhere -
maybe it downloads a file from a remote web service. Our ``MyClass`` prints out
a response message depending on the response from the ``Fetcher::fetch()`` call.
When testing ``MyClass`` we don't really want ``Fetcher`` to go and download
random stuff from the internet every time we run our test suite. So we mock it
out:
f we run this, our test will error out with a nasty
``PHP Fatal error:  Uncaught Error: Undefined class constant 'SUCCESS' in ..``.
Here's how a ``namedMock()`` can help us in a situation like this.
We create a stub for the ``Fetcher`` class, stubbing out the class constants,
and then use ``namedMock()`` to create a mock named ``Fetcher`` based on our
stub:
his works because under the hood, Mockery creates a class called ``Fetcher``
that extends ``FetcherStub``.
The same approach will work even if ``Fetcher::fetch()`` is not a static
dependency:
nd the test will have something like this:
onstants Map
-------------
Another way of mocking class constants can be with the use of the constants map configuration.
Given a class with constants:
t can be mocked with:
