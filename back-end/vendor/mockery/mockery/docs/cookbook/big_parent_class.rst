ig Parent Class
================
In some application code, especially older legacy code, we can come across some
classes that extend a "big parent class" - a parent class that knows and does
too much:
e want to test our ``ChildClass`` and its ``doesOneThing`` method, but the
problem is that it calls on ``BigParentClass::doesEverything()``. One way to
handle this would be to mock out **all** of the dependencies ``BigParentClass``
has and needs, and then finally actually test our ``doesOneThing`` method. It's
an awful lot of work to do that.
What we can do, is to do something... unconventional. We can create a runtime
partial test double of the ``ChildClass`` itself and mock only the parent's
``doesEverything()`` method:
ith this approach we mock out only the ``doesEverything()`` method, and all the
unmocked methods are called on the actual ``ChildClass`` instance.
