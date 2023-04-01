ocking Hard Dependencies (new Keyword)
=======================================
One prerequisite to mock hard dependencies is that the code we are trying to test uses autoloading.
Let's take the following code for an example:
he way we can test this without doing any changes to the code itself is by creating :doc:`instance mocks </reference/instance_mocking>` by using the ``overload`` prefix.
f we run this test now, it should pass. Mockery does its job and our ``App\Service`` will use the mocked external service instead of the real one.
The problem with this is when we want to, for example, test the ``App\Service\External`` itself, or if we use that class somewhere else in our tests.
When Mockery overloads a class, because of how PHP works with files, that overloaded class file must not be included otherwise Mockery will throw a "class already exists" exception. This is where autoloading kicks in and makes our job a lot easier.
To make this possible, we'll tell PHPUnit to run the tests that have overloaded classes in separate processes and to not preserve global state. That way we'll avoid having the overloaded class included more than once. Of course this has its downsides as these tests will run slower.
Our test example from above now becomes:
