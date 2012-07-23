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

When registering a controller in `bootstrap.php`, you may omit the call to `->execute($bindings, $closure)`. In this case, the framework expects the bindings and closure to be defined in a file located in the `controllers` folder in a subpath matching the name of the controller.

For example in the file `bootstrap.php` :
```PHP
<?php
Controller::register("blog/post")->on("GET", "/blog/post/<id:\\d+>");
);
```

And in the file `/controllers/blog/test.php` :
```PHP
<?php
return array(
	array(Bind::URI("id")),
	function($post_id) {
		// Return HTML of blog post of given id.
	}
);
```

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



Reverse routing
---------------
While routing goes from URI to controller and arguments, reverse routing goes the other way around. Given a controller and arguments, it is the process of generating the URI that points to it.

You can generate the URI of a controller by calling **__`Controller::get($name)->uri($arg1, $arg2, ...)`__**. This will return the URI (query string included) that, if it is requested, will trigger the execution of the closure with the given arguments.

Always generating URLs this way in views instead of building them manually will ensure that they automatically adjust, should the URI patterns associated with the controller change in the future, or should the directory of the website move in the document tree.

Controller callbacks
--------------------
**Controller functions that are registered as resources should only return the resource content**, NOT the full page complete with header, footer, etc.

Wrapping the content of a resource into the website layout is the role of two callbacks :
* `Controller->before()` : called just before the resource function,
* `Controller->after($content)` : called just after the resource function with the content it returned.

The `before()` callback is where whatever template engine you decide to use shall be initialized. The `after($content)` callback is where the content generated by the resource should be wrapped in the full site layout. **The return of the `after($content)` callback will be echoed as the HTTP response to the request.**

These callbacks should be defined in the base controller class, and overridden in those controllers that require a special layout to be used.

Separating the content of a resource from the full page layout this way is more flexible because it allows resources to include other resources content into their own (see HMVC and internal requests below) and more DRY because the before and after callbacks are likely to be the same for many resources.

Internal (HMVC) requests
------------------------
You can get the content generated by a resource by calling **__`Resource::get($controller, $method)->content($param1, $param2, ...)`__**. When you do so, the `before()` and `after($content)` callbacks are NOT called. The resource content is returned raw, not wrapped into the website layout (should you need that, you may call `Resource::get($controller, $method)->page($param1, $param2, ...)`). This is called an internal request (as opposed to a client request).

This allows for nesting of resources contents, thus the name Hierarchical MVC (HMVC).

Templating system
-----------------
Views are the small templating system that comes with PicoPHP.

### Echo VS return
**Using echo in a function is evil**. Once a function echoes HTML, it is forever gone, there is no way to apply some further processing to it, for example wrap it inside something else. Functions that echo things must also be called in a rigid order, instead of being called in whatever order is algorithmically more convenient. It is **much more flexible to write functions that return HTML**.

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

Base controller class
---------------------
This is the default implementation of the base controller class, which you are free to change according to your needs :
```PHP
<?php
class Controller {
	public function before() {
		View::page("site");
	}

	public function after($content) {
		return View::page()->set('content', $content)->render();
	}
}
```

Constants
---------
The file `index.php` defines the following constants that you may find useful should you want to build URLs that stay valid if the website directory moves around in the document tree :

* **__`URI_ROOT`__** : URI of the root of the website directory.
* **__`URI_ROOT_CSS`__** : URI of the root of the `style` directory.
* **__`URI_ROOT_JS`__** : URI of the root of the `js` directory.

So if you need to build a link to the stylesheet `site.css` in the `style` directory, you write `URI_ROOT_CSS . '/site.css'` .

The e() function
----------------
The file index.php defines the following shortcut for htmlspecialchars :

```PHP
<?php
function e($string) {
	return htmlspecialchars($string);
}
```

It's a small thing but this way there is no excuse for being lazy and not escaping variables in views.