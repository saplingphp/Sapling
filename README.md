Sapling
=======

Sapling is an HMVC microframework for PHP that aims to cover only the minimum requirements to start a new PHP project.

Specifically you will find here :
* an URL router / reverse router,
* an infrastructure for controllers,
* a class autoloader,
* a tiny template engine (optional).

But you won't find :
* database access classes,
* an ORM,
* helpers to handle file transfers, forms, etc.

Table of contents
-----------------

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Directory structure](#directory-structure)
4. [Constants](#constants)
5. [Global functions](#global-functions)
    1. [The e() function](#the-e-function)
6. [Classes autoloading](#classes-autoloading)
7. [Controllers](#controllers)
    1. [Inline definition](#inline-definition)
    2. [Definition in a separate file](#definition-in-a-separate-file)
    3. [Why closures and not a class hierarchy ?](#why-closures-and-not-a-class-hierarchy-)
8. [URI patterns](#URI-patterns)
9. [Bindings](#bindings)
    1. [Defining bindings](#defining-bindings)
    2. [Automatic bindings](#automatic-bindings)
    3. [Bindings VS accessing superglobal arrays](#bindings-vs-accessing-superglobal-arrays)
10. [Reverse routing](#reverse-routing)
11. [Wrappers](#wrappers)
    1. [Resources](#resources)
    2. [Wrappers](#wrappers)
    3. [Examples](#examples)
        1. [Website layout](#website-layout)
        2. [Authorizations](#authorizations)
12. [Controller collections](#controller-collections)
13. [Internal (HMVC) requests](#internal-hmvc-requests)
14. [Templating system](#templating-system)
    1. [Echo VS return](#echo-vs-return)
    2. [Views](#views)
    3. [Page](#page)

Requirements
------------
PHP 5.3, apache with mod_rewrite enabled.

Installation
------------
Drop the code in any directory under the Apache document root (for example `/dir`). Point your browser at the URL <http://localhost/dir/test/hello?b=world>. That should display the test page.

If it doesn't work :

1. Make sure mod_rewrite is enabled.
2. You may be using an Apache alias. Uncomment the Rewrite_Base property in the `.htaccess` file and edit it so that it contains the URI of the root dictory of the website.

Directory structure
-------------------

    --+-- bootstrap.php          // Define your routes here.
      +-- classes                // Put your own classes in there.
      +-- controllers            // Controllers that aren't defined inline go here.
      +-- media  --+-- style     // Stylesheets go here.
      |            +-- js        // Javascript go here.
      +-- system --+-- classes   // System classes are here.
      |            +-- index.php // Entry point defined in .htaccess file
      +-- views                  // Put your views here.
      
The code of the framework is inside the system directory and shouldn't be modified. Everything else is yours, including the file `bootstrap.php`.

Constants
---------
The framework defines the following constants that you may find useful should you want to build URLs that stay valid if the website directory moves around in the document tree :

* **__`URI_ROOT`__** : URI of the root of the website directory.
* **__`URI_ROOT_CSS`__** : URI of the root of the `style` directory.
* **__`URI_ROOT_JS`__** : URI of the root of the `js` directory.

So if you need to build a link to the stylesheet `site.css` in the `style` directory, you write `URI_ROOT_CSS . '/site.css'` .

Global functions
----------------
### The e() function
The file index.php defines the following shortcut for htmlspecialchars :

```PHP
<?php
function e($string) {
	return htmlspecialchars($string);
}
```

It's a small thing but this way there is no excuse for being lazy and not escaping variables in views.

Classes autoloading
-------------------
The first time you refer to a class called `A_B_C`, the file `/classes/a/b/c.php` will be automatically included. If the code of the class `A_B_C` is indeed in the file `/classes/a/b/c.php`, the class will be loaded automatically without any need for you to include anything explicitly.

**So a class called `A_B_C` should always be located in the file `/classes/a/b/c.php`.**

Additionally, and although this is not strictly required, if a class `B` extends a class `A`, then it should be called `A_B` (and thus be located in the file `/classes/a/b.php`). This way the directory structure of the `classes` folder will mirror the class hierarchy.

Controllers
-----------
In Sapling controllers aren't classes but closures associated with an URI pattern. When the requested URI matches the URI pattern, the closure is executed and whatever it **returns** is sent to the client as a response.

Controllers should be registered in the file `bootstrap.php`.

### Inline definition

If the code of the closure is relatively short, the controllers may be defined entirely in the `bootstrap.php` file, like so :

```PHP
<?php
Controller::register($name)->on($method, $pattern)->execute($bindings, $closure);
```

Where :
* `$name` is the name of the controller. It can be used to refer to it later on.
* `$method` is the HTTP method accepted by the controller. To accept more than one, pass an array.
* `$pattern` is the URI pattern that triggers the execution of the closure.
* `$bindings` is the array of bindings, one by closure argument, describing from where the data should be pulled.
* `$closure` is the closure that define the content returned by the controller.

For example let's take a closer look at the code that defines the test page :

```PHP
<?php
Controller::register("test")->on("GET", "/test/<a>")->execute(
	array(Bind::URI("a"), Bind::GET("b")),
	function($x, $y) {
		return "Test page called with parameters : $x, $y";
	}
);
```

The controller is called `"test"`. It reacts on `"GET"` HTTP requests, but only those that match the pattern `"/test/<a>"`. The closure has two arguments : `$x` and `$y`. The first one is bound to the URI parameter `a` while the second one is bound to the value of the key `b` in the `$_GET` superglobal array.

### Definition in a separate file

If the code of the closure is long, you may find it more convenient to define it in a separate file.

When registering a controller in `bootstrap.php`, you may omit the call to `->execute($bindings, $closure)`. In this case, the framework expects the bindings and closure to be defined in a file located in the `controllers` folder in a subpath matching the identifier of the controller.

For example if we have this in the file `bootstrap.php` :
```PHP
<?php
Controller::register("blog/post/edit")->on("GET", "edit/post/<id:\\d+>");
```

Then the following must be located in the file `/controllers/blog/post/edit.php` :
```PHP
<?php
return array(
	array(Bind::URI("id")),
	function($post_id) {
		// Return HTML of blog post of given id.
	}
);
```

As you can see, controller identifiers are actually structured like relative paths. Those path aren't related at all to what URI the controller matches. They should be chosen to describe at best the logical hierarchy of controllers.

### Why closures and not a class hierarchy ?
Because controllers can't be properly organized into a single class hiearchy without running into problems of code duplication. And because artificially grouping vaguely related functions into controller classes just to do it the OO way doesn't make sense.

To illustrate the first point, consider for example a situation where controllers could be hierarchically organized according to security requirements or according to page layout. If those two hierarchies match, there is no problem. But if they don't, you will have to choose one way to structure the code over the other. If you choose security, you will have code duplication for page layout and the other way around.

To avoid code duplication in this case, inheritance isn't the right tool. We need a flexible mechanism more akin to composition : see [wrappers](#wrappers).

URI patterns
------------
URI patterns are URI strings that may include named parameters, for example `"/hello/<a>"`. By default, parameters match any sequence of characters but `/`. The range of strings that a parameter matches can be restricted by using a [regex](http://www.php.net/manual/en/reference.pcre.pattern.syntax.php), for example `"/hello/<a:\\d+>"`.

More precisely, URI patterns are turned into regex by the following process :

* `URI_ROOT` is added at the beginning,
* anything regex special character that appear outside a parameter definition is escaped,
* parameters are turned into [named subpatterns](http://www.php.net/manual/en/regexp.reference.subpatterns.php),
* a leading `^` and a trailing `$i` are added.

If the website is located in the directory `/sub`, then the following URI pattern `/hello/<a:\\d+>` becomes `^"\\/sub\\/hello\\/(?<a>\\d+)$i"` and matches the URL `http://www.mydomain.com/sub/hello/123`.

Each named parameter in the URI pattern becomes available for binding with a closure argument using the syntax `Bind:URI($param)`.

Bindings
--------
For each controller, and for each closure argument, a binding must exist. Each binding describes from which parameter in the URI or from which key in the superglobal arrays the data should be pulled to feed the closure argument.

### Defining bindings explicitely
Bindings can be explicitely defined by using the first argument of the `->execute($bindings, $closure)` method.

For example, given the following controller :
```PHP
<?php
Controller::register("test")->on("POST", "/test/<a>")->execute(
	array(Bind::URI("a"), Bind::GET("b"), Bind::POST("c"), Bind::COOKIE("d"), Bind::REQUEST("e")),
	function($v, $w, $x, $y, $z) {
		// ...
	}
);
```

When the URI `/test/1?b=2&e=5` is requested with `$_POST['c'] === 3` and `$_COOKIE['d'] === 4`, the closure will be called with the arguments `1, 2, 3, 4, 5`.

### Automatic bindings
Sapling can also infer bindings automatically from the names of the closure parameters. The algorithm goes as follows :

Given a closure parameter called "$x"
* if there is an URI parameter called "`<x`>", the binding will be `Bind::URI('x')`,
* otherwise the binding will be `Bind::REQUEST('x')`.

According to those bindings, Sapling will look for each closure argument in the URI first, and if it doesn't find it, in the [$_REQUEST](http://php.net/manual/en/reserved.variables.request.php) superglobal array.

URI automatic generation will work as expected according to those bindings (see [reverse routing](#reverse-routing)).

### Bindings VS accessing superglobal arrays
One may wonder what's the difference between defining bindings like this :
```PHP
<?php
Controller::register("test")->on("GET", "/test/<a>")->execute(
	array(Bind::URI("a"), Bind::GET("b"), Bind::GET("c")),
	function($x, $y, $z) {
		// ...
	}
);
```
...and accessing superglobal arrays directly in the closure, like this :
```PHP
<?php
Controller::register("test")->on("GET", "/test/<a>")->execute(
	array(Bind::URI("a")),
	function($x) {
		$y = $_GET['b'];
		$z = $_GET['c'];
		// ...
	}
);
```

Apart from the purely esthetical value of treating all arguments the same way, the advantage of using bindings is twofold :
* If the bindings are defined explicitely, the framework can make use of them to help you automatically generate proper URIs : `Controller::get("test")->uri(1, 2, 3)` generates the URI `/test/1?b=2&c=3` (see [reverse routing](#reverse-routing)). This isn't possible otherwise.
* If superglobal arrays are accessed in the closure, you can't call it yourself without messing with the superglobal arrays to set up the right context before the call. This makes it difficult to use for internal requests (see [internal requests](#internal-requests)).

Reverse routing
---------------
While routing goes from URI to controller and arguments, reverse routing goes the other way around. Given a controller and arguments, it is the process of generating the URI that points to it.

You can generate the URI of a controller by calling **__`Controller::get($name)->uri($arg1, $arg2, ...)`__**. This will return the URI (query string included) that, if it is requested, will trigger the execution of the closure with the given arguments.

Always generating URLs this way in views instead of building them manually will ensure that they automatically adjust, should the URI patterns associated with the controller change in the future, or should the directory of the website move in the document tree.

Wrappers
--------
Wrappers generalize what some frameworks call filters, or before and after callbacks. It is the main mechanism of code reuse for controllers in Sapling.

### Resources
Wrappers act on a `Resource`. A `Resource` is an object able to generate some content. It has a single function that returns a string : `->content()`. For example, internally, when a request is made for a controller to execute with some arguments, the closure and its arguments are packed together into a `Resource` object ready to deliver some content when (and if) its `->content()` function is called.

### Wrappers
A `Wrapper` is also an object with a single function that returns a string, but it takes a `Resource` as parameter : `->wrap(Resource $resource)`. When `->wrap(...)` is called, it is supposed to get the content of the `Resource`, transform it in some way, and return it. But it may also decide not to execute the `Resource` at all and throw an exception, for example if some condition isn't met. It's up to you what you put in wrappers.

Wrappers can be wrapped on top of each other around controllers like this :
```PHP
<?php
Controller::get($identifier)->wrap($wrapper1)->wrap($wrapper2);
```

### Examples
The following examples illustrate two ways one can define wrappers : with a closure directly in `bootstrap.php`, or by extending the `Wrapper` class.

#### Website layout
Controllers should only return an HTML fragment. Wrapping that fragment into the full site layout should be accomplished through wrappers. Let's take a look at the `Wrapper` defined in `bootstrap.php`. It gets the content of its `$resource` parameter, wraps it into the website template (see [templating system](#templating-system)), and returns the result :

```PHP
<?php
$layout = Wrapper::create(function(Resource $resource) {
	View::page('site');
	return View::page()->set('content', $resource->content())->render();
});
```

It is then wrapped around all controllers like this (see [controller collections](controller-collections)) :
```PHP
<?php
Controller::find("**")->wrap($layout);
```

#### Authorizations
The following wrapper class prevents the execution of a controller if the current user doesn't have a given role.

File `classes/wrapper/hasrole.php`:
```PHP
<?php
class Wrapper_HasRole extends Wrapper {
	protected $role;
	
	public function __constuct($role) {
		$this->role = $role;
	}
	
	public function wrap(Resource $resource) {
		global $user;
		if ($user->hasRole($this->role))
			return $resource->content();
		else
			throw new Exception("..."); // Or return error page HTML, redirect, or something
	}
}
```

File `bootstrap.php` : we assign the wrapper to all controllers in the admin area of the site (see [controller collections](controller-collections)).
```PHP
<?php
Controller::find("admin/**")->wrap(new Wrapper_HasRole('admin'));
```

Controller collections
----------------------
It isn't convenient, and error prone, to assign wrappers to every controllers one by one. As a way around this, Sapling allows you to define controllers collections.

A controller collection is defined by calling the `Controller::find($expression)` function. The `$expression` argument is a pattern that will be matched agains the identifiers of every controllers defined so far. The pattern allows for two wildcards :

1. `*` : matches any sequence of characters but `/`,
2. `**` : matches any sequence of characters including `/`.

For example :
```PHP
<?php
$collection1 = Controller::find("admin/**"); // Matches "admin/x", "admin/x/y", "admin/x/y/z", etc...
$collection2 = Controller::find("*/faq");    // Matches "x/faq", "y/faq", "z/faq", etc...
```

For more complicated use cases, the `Controller::find($expression)` function also accepts a closure. For example, to define the collection of all controllers whose identifiers don't begin with `"admin"` :
```PHP
<?php
$collection = Controller::find(function (Controller $c) {
	return $c->match("admin/**") ? false : true;
});
```

Any function calls on a collection is forwarded to every controller in the collection. This is why the following code applies the admin role wrapper to all controller in the admin section :
```PHP
<?php
Controller::find("admin/**")->wrap(new Wrapper_HasRole('admin'));
```

Internal (HMVC) requests
------------------------
You can get the content generated by a controller by calling **__`Controller::get($identifier)->content($arg1, $arg2, ...)`__**. When you do so, the closure and all of its wrappers are executed. Should you need the raw content generated by the closure, not wrapped into wrappers, you can call **__`Controller::get($identifier)->raw($arg1, $arg2, ...)`__**. The above function calls are called internal requests (as opposed to client requests).

This allows for nesting of controller contents, thus the name Hierarchical MVC (HMVC).

Templating system
-----------------
Views are the small templating system that comes with Sapling.

### Echo VS return
**Using echo in a function is evil**. Once a function has echoed HTML, there is no way to apply some further processing to it, for example wrap it inside something else. Functions that echo things must also be called in a rigid order, instead of being called in whatever order is algorithmically more convenient. It is much more flexible to write functions that **return HTML**.

Consider the following code :
```PHP
<?php
echo header();
bad_function_that_echoes_content();
echo footer();
```
Header and footer are clearly tightly related things : there are tags opened in the header that have to be closed in the footer. And yet, to accommodate for the `bad_function_that_echoes_content()`, they had to be split apart into two different functions. This is much better :
```PHP
<?php
$content = good_function_that_returns_content();
echo layout($content);
```

However PHP makes functions that return HTML difficult to achieve. Consider the following function that returns the HTML of a blog post :
```PHP
<?php
function post($author, $content) {
   return "<div class=\"post\">\n" .
          "    <span class=\"author\">" . $author . "</span>\n" .
          "    <div class=\"content\">" . $content . "</div>\n" .
          "</div>\n";
}
```
This is ugly and impractical.

**The whole purpose of views is to make it as convenient as possible to build scripts that return complex HTML as a string.**

### Views
A View object is composed of two parts :
* a template file written in PHP,
* an array of key-value pairs.

Template files are located into the `/views` directory. You can create a new view by calling `View::create($path)` where `$path` is the path of template file relative to the `/views` directory. Key-value pairs can be associated with a view by calling `$view->set($key, $value)`.

When you call `$view->render()`, the key-value pairs are turned into real variables and the template script is executed inside that context. The output of the template script is captured using output buffering and is returned.

Consider for example the following template located in the file `/views/tpl.php`:
```PHP
<div class="post">
    <span class="author"><?php echo $author ?></span>
    <div class="content"><?php echo $content ?></div>
</div>
```

Calling `$html = View::create('tpl')->set('author', 'me')->set('content', 'hello world')->render();` will store into the `$html` variable the following string :
```PHP
<div class="post">
    <span class="author">me</span>
    <div class="content">hello world</div>
</div>
```

### Page
Consider the view introduced above that returns a blog post. Perhaps that view require a specific style sheet or script to be included into the page for it to work correctly. So, from everywhere in the code, we need access to some global object to be able to contribute a style sheet or a script to the page being generated. There is a special view for that : the **page**.

The template of the view to be used as a page can be set by calling `View::page($template_path)`. From that point on, the page view behaves as a singleton that can be accessed by calling `$page = View::page();`. The page view must at least be able to handle a `$content` variable, a `$stylesheets` variable and a `$scripts` variable.

To contribute a style sheet and a script to the page, one would do :
```PHP
<?php
View::page()->push('stylesheets', URI_ROOT_CSS . 'mystylesheet.css');
View::page()->push('scripts',     URI_ROOT_JS  . 'myscript.js');
```

A default page view is included with the package, see `/views/site.php`.