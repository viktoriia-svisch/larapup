omplex Argument Matching With Mockery::on
==========================================
When we need to do a more complex argument matching for an expected method call,
the ``\Mockery::on()`` matcher comes in really handy. It accepts a closure as an
argument and that closure in turn receives the argument passed in to the method,
when called. If the closure returns ``true``, Mockery will consider that the
argument has passed the expectation. If the closure returns ``false``, or a
"falsey" value, the expectation will not pass.
The ``\Mockery::on()`` matcher can be used in various scenarios — validating
an array argument based on multiple keys and values, complex string matching...
Say, for example, we have the following code. It doesn't do much; publishes a
post by setting the ``published`` flag in the database to ``1`` and sets the
``published_at`` to the current date and time:
n a test we would mock the model and set some expectations on the call of the
``save()`` method:
he important part of the example is inside the closure we pass to the
``\Mockery::on()`` matcher. The ``$argument`` is actually the ``$saveData`` argument
the ``save()`` method gets when it is called. We check for a couple of things in
this argument:
* the post ID is set, and is same as the post ID we passed in to the
  ``publishPost()`` method,
* the ``published`` flag is set, and is ``1``, and
* the ``published_at`` key is present.
If any of these requirements is not satisfied, the closure will return ``false``,
the method call expectation will not be met, and Mockery will throw a
``NoMatchingExpectationException``.
