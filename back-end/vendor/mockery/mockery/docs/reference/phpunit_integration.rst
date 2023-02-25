HPUnit Integration
===================
Mockery was designed as a simple-to-use *standalone* mock object framework, so
its need for integration with any testing framework is entirely optional.  To
integrate Mockery, we need to define a ``tearDown()`` method for our tests
containing the following (we may use a shorter ``\Mockery`` namespace
alias):
his static call cleans up the Mockery container used by the current test, and
run any verification tasks needed for our expectations.
For some added brevity when it comes to using Mockery, we can also explicitly
use the Mockery namespace with a shorter alias. For example:
ockery ships with an autoloader so we don't need to litter our tests with
``require_once()`` calls. To use it, ensure Mockery is on our
``include_path`` and add the following to our test suite's ``Bootstrap.php``
or ``TestHelper.php`` file:
f we are using Composer, we can simplify this to including the Composer
generated autoloader file:
o integrate Mockery into PHPUnit and avoid having to call the close method
and have Mockery remove itself from code coverage reports, have your test case
extends the ``\Mockery\Adapter\Phpunit\MockeryTestCase``:
n alternative is to use the supplied trait:
xtending ``MockeryTestCase`` or using the ``MockeryPHPUnitIntegration``
trait is **the recommended way** of integrating Mockery with PHPUnit,
since Mockery 1.0.0.
PHPUnit listener
----------------
Before the 1.0.0 release, Mockery provided a PHPUnit listener that would
call ``Mockery::close()`` for us at the end of a test. This has changed
significantly since the 1.0.0 version.
Now, Mockery provides a PHPUnit listener that makes tests fail if
``Mockery::close()`` has not been called. It can help identify tests where
we've forgotten to include the trait or extend the ``MockeryTestCase``.
If we are using PHPUnit's XML configuration approach, we can include the
following to load the ``TestListener``:
ake sure Composer's or Mockery's autoloader is present in the bootstrap file
or we will need to also define a "file" attribute pointing to the file of the
``TestListener`` class.
f we are creating the test suite programmatically we may add the listener
like this:
