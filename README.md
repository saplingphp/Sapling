Sapling
=======

Sapling is an HMVC microframework for PHP that aims to cover only the minimum requirements to start a new PHP project.

More specifically you will find here :
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

Directory structure
-------------------

    --+-- bootstrap.php          // Define your routes here.
      +-- classes                // Put your own classes in there.
      +-- media  --+-- style     // Stylesheets go here.
      |            +-- js        // Javascript go here.
      +-- system --+-- classes   // System classes are here.
      |            +-- index.php // Entry point defined in .htaccess file
      +-- views                  // Put your views here.
      
The code of the framework is inside the system directory and shouldn't be modified. Everything else is yours, including the file `bootstrap.php`.

Constants
---------
The file `index.php` defines the following constants that you may find useful should you want to build URLs that stay valid if the website directory moves around in the document tree :

* **__`URI_ROOT`__** : URI of the root of the website directory.
* **__`URI_ROOT_CSS`__** : URI of the root of the `style` directory.
* **__`URI_ROOT_JS`__** : URI of the root of the `js` directory.

So if you need to build a link to the stylesheet `site.css` in the `style` directory, you write `URI_ROOT_CSS . '/site.css'` .

Classes autoloading
-------------------
The first time you refer to a class called `A_B_C`, the file `/classes/a/b/c.php` will be automatically included. If the code of the class `A_B_C` is indeed in the file `/classes/a/b/c.php`, the class will be loaded automatically without any need for you to include anything explicitly.

**So a class called `A_B_C` should always be located in the file `/classes/a/b/c.php`.**

Additionally, and although this is not strictly required, if a class `B` extends a class `A`, then it should be called `A_B` (and thus be located in the file `/classes/a/b.php`). This way the directory structure of the `classes` folder will mirror the class hierarchy.

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

Controllers
-----------
In Sapling controllers aren't classes but closures with parameters that return HTML (or whatever). Let's take a look at the code that defines the test page in the file `bootstrap.php` :

```PHP
<?php
Controller::register("test")->on("GET", "/test/<a>")->execute(
	array(Bind::URI("a"), Bind::GET("b")),
	function($x, $y) {
		return "Test page called with parameters : $x, $y";
	}
);
```
* `"test"` is the name of the controller. It can be used to refer to the controller later on.
* `"GET"` is the HTTP method accepted by this controller. If more than one method is needed, they can be passed as an array, for example `array("GET", "POST")`.
* `"/test/<a>"` is an URI pattern. Any URI matching this pattern will trigger the execution of the controller.
* `array(Bind::URI("a"), Bind::GET("b"))` are bindings. There is one binding by function parameter. Bindings describe from where in the request the function parameters should be pulled.
* next comes the function that defines the content that this controller generates. Note that the function RETURNS that content, it doesn't echo it.
 
Resources
---------
A resource is something that can be reached through an URL and returns some content.

In this framework, resources are controller functions associated with an URI pattern. When the requested URI matches the pattern of a resource, it is executed and whatever it returns is sent to the client as a response.

Resources must be explicitly registered in the `index.php` file, using the **__`Resource::register($controller, $method, $pattern)`__** function, where :
* `$controller` is the name of the controller class (without the `Controller_` prefix),
* `$method` is the name of the method,
* `$pattern` is the URI pattern that will, when the requested URI matches it, trigger the execution of the resource.

URI patterns
------------
URI patterns are URI strings that may include parameter segments. Parameters are regex delimited by parentheses following the [PCRE syntax](http://www.php.net/manual/en/reference.pcre.pattern.syntax.php).

More precisely, URI patterns are turned into regex by the following process :

* `URI_ROOT` is added at the beginning,
* slashes `/` are prefixed with a `\`,
* a leading `^` and a trailing `$` are added.

If the website is located in the directory `sub`, then the following URI pattern `/hello/world` becomes `^\\/sub\\/hello\\/world$` and matches the URL `http://www.mydomain.com/sub/hello/world`.

Routing
-------
Routing is the process of calling the right resource with the right parameters, given a requested URI.

This is done by looping over all the registered resource patterns until we find one that matches the requested URI. When that happens, the associated controller is instanciated and the resource function is called, with parameters values extracted from the URI passed as arguments.

Thus, if you register a resource like this `Resource::register('test','index', 'test/(\w+)/(\w+)');` then for the requested URL `http://www.mydomain.com/test/hello/world`, the method `->index($a, $b)` will be called on the controller `Controller_Test` with `$a == 'hello'` and `$b == 'world'`.

Reverse routing
---------------
While routing goes from URI to resource and parameters, reverse routing goes the other way around. Given a resource and some parameters, it is the process of generating the URI that points to it.

You can generate the URI of a resource by calling **__`Resource::get($controller, $method)->uri($param1, $param2, ...)`__**. This will return the URI pattern associated with the resource where each parameter segment has been replaced by `$param1`, `$param2`, etc.

Always generating URLs this way in views instead of building them manually will ensure that they automatically adjust, should the URI patterns associated with the resources change in the future, or should the directory of the website move in the document tree.

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