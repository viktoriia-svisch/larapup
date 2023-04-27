lternative shouldReceive Syntax
================================
As of Mockery 1.0.0, we support calling methods as we would call any PHP method,
and not as string arguments to Mockery ``should*`` methods.
The two Mockery methods that enable this are ``allows()`` and ``expects()``.
Allows
------
We use ``allows()`` when we create stubs for methods that return a predefined
return value, but for these method stubs we don't care how many times, or if at
all, were they called.
his is equivalent with the following ``shouldReceive`` syntax:
ote that with this format, we also tell Mockery that we don't care about the
arguments to the stubbed methods.
If we do care about the arguments, we would do it like so:
his is equivalent with the following ``shouldReceive`` syntax:
xpects
-------
We use ``expects()`` when we want to verify that a particular method was called:
his is equivalent with the following ``shouldReceive`` syntax:
y default ``expects()`` sets up an expectation that the method should be called
once and once only. If we expect more than one call to the method, we can change
that expectation:
