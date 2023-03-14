uick Reference
===============
The purpose of this page is to give a quick and short overview of some of the
most common Mockery features.
Do read the :doc:`../reference/index` to learn about all the Mockery features.
Integrate Mockery with PHPUnit, either by extending the ``MockeryTestCase``:
r by using the ``MockeryPHPUnitIntegration`` trait:
reating a test double:
reating a test double that implements a certain interface:
xpecting a method to be called on a test double:
xpecting a method to **not** be called on a test double:
xpecting a method to be called on a test double, once, with a certain argument,
and to return a value:
xpecting a method to be called on a test double and to return a different value
for each successive call:
reating a runtime partial test double:
reating a spy:
xpecting that a spy should have received a method call:
ot so simple examples
^^^^^^^^^^^^^^^^^^^^^^
Creating a mock object to return a sequence of values from a set of method
calls:
reating a mock object which returns a self-chaining Undefined object for a
method call:
reating a mock object with multiple query calls and a single update call:
xpecting all queries to be executed before any updates:
reating a mock object where all queries occur after startup, but before finish,
and where queries are expected with several different params:
