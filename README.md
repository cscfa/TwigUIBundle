# TwigUIBundle
### Version: 1.0.0

The TwigUIBundle is a symfony2 bundle that provide process to use modules.

#####Installation

Register the bundle into app/appKernel.php

```
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            [...]
            new Cscfa\Bundle\TwigUIBundle\CscfaTwigUIBundle(),
        );
        
        [...]
    }
}
```

### Create a module

The TwigUIBundle allow to create modules to process an twig rendering as hierarchic modules.

Two modules types exists :
 * The TopLevelModule
 * The StepModule

The TopLevelModules represents the modules that are directly injected into the module set of a controller. To create
a TopLevelModule, you must create a class that extends AbstractTopLevelModule. This abstract class implements the
base methods and process to inject the template and arguments to render into the module environment.

The StepModule represents the childs modules of a TopLevelModule. It work as the TopLevelModule but you must extends the
AbstractStepModules.

```php
use Cscfa\Bundle\TwigUIBundle\Modules\AbstractTopLevelModule;
// or
use Cscfa\Bundle\TwigUIBundle\Modules\AbstractStepModule;
```

You must define the 'getName()' and 'render(EnvironmentContainer $environment)' methods. The 'getName' method must return
an unique as possible string to be used as template's calling definition alias. The 'render' method is the method where
you can write you'r php logic.

The 'render' method is allowed to return 'null' or an instance of 'TwigRequest'. Returning null indicate that the module not create any twig view.

The 'EnvironmentContainer' instance passed to 'render' method allow to access to the informations given by the controller.

```php
// Get a registed object. By default, only the User instance is given by the controller
$environment->getObjectsContainer()->getObject($alias);

// Get the current controller name
$environment->getControllerInfo()->getControllerName();

// Get the current controller method
$environment->getControllerInfo()->getMethodName();
```

### Create a twig request

The 'TwigRequest' class allow to store informations to render a twig template behind the usage of EnvironmentContainer.

A 'TwigRequest' instance store a template name and the calling arguments. 

```php
$twigRequest = new TwigRequest();
$twigRequest->setTwigPath("AcmeBundle:Default:index.html.twig");
$twigRequest->setArguments(array(
    "fooArgument" => "foo",
    "barArgument" => "bar",
));
$twigRequest->remArgument("barArgument");
$twigRequest->hasArgument("fooArgument");
```

The 'TwigRequest' class allow to store childs but this is currently managed by the hierarchization of the modules.
```php
// Note the constructor allow to pass the template path and the arguments.
$twigRequest->addChildRequest(
    new TwigRequest(
        "AcmeBundle:Default:index.html.twig",
        array("fooArgument" => "foo")
    ),
    "ChildAliasName"
);
```

### Register you'r module

The modules are registered as tagged services.

The tags that needs to be defined are:
* name : 'cs.module'
* cs.module.parent : the name of the parent module
* cs.module.hydrate : the method to call to register into the parent

The value of the 'name' tag can be customize into the TwigUIBundle configuration.

The tag that target the parent can be customize into the TwigUIBundle configuration.

The tag that define the method to call can be customize into the TwigUIBundle configuration.

Here an exemple of TwigUIBundle configuration :
```yaml
# /app/config.yml
cscfa_twig_ui:
    modules:
        - tag_name: cs.module
        - parent_tag: cs.module.parent
        - method_tag: cs.module.hydrate
```

The services are stored by default into an instance of ModuleSet that process the contained modules in order of they priority. The priority can be define into the service definition. The priority is stored as float value.

Here an example of module definition present into the unit tests:
```yaml
cscfa_twig_ui.test.main:
    class: Cscfa\Bundle\TwigUIBundle\Modules\ModuleSet

cscfa_twig_ui.test.firstTop:
    class: Cscfa\Bundle\TwigUIBundle\FunctionalTest\Object\FirstTopLevel
    calls:
        - [ setPriority, [ 10 ]]
    tags: 
        - { name: cs.module, cs.module.parent: cscfa_twig_ui.test.main, cs.module.hydrate: addModule }
```

### Use modules into you'r controller

In the last example, you can see that the first service is an instance of ModuleSet. It's the service that the controller will call to process the modules, so you must create a service that instanciate ModuleSet.

To create a controller that process modules, you must extends the 'ModulableController'. This class provide access to the 'processModule' method.

In the following example, the controller return directly the twig result of the the first argument template. The second argument define the current method and the third indicate the main ResultSet service that contain the modules.

```php
class AcmeController extends ModulableController
{
    public function processAction()
    {
        return $this->processModule(
            'CscfaTwigUIBundle:test:controllerResult.html.twig',
            __METHOD__,
            'cscfa_twig_ui.test.main'
        );
    }
}
```

The 'processModule' method take a fourth optional 'EnvironmentOptionBuilder' argument. It allow to give optional parameters to the Environment.

The followed exemple make the current accessible from the modules :

```php
class AcmeController extends ModulableController
{
    public function processAction(Request $request)
    {
        $builder = new EnvironmentOptionBuilder();
        $builder->addOption(
            $builder::OBJECT_CONTAINER_OBJECT, 
            array($request, "request")
        );
        
        return $this->processModule(
            'CscfaTwigUIBundle:test:controllerResult.html.twig',
            __METHOD__,
            'cscfa_twig_ui.test.main',
            $builder
        );
    }
}

class module extends AbstractTopLevelModule
{
    public function render(EnvironmentContainer $environment)
    {
        $environment->getObjectsContainer()
            ->getObject("request");
    }
}
```

The 'EnvironmentOptionBuilder' allow to register options withe the following types :

type | argument format
---- | ---------------
OBJECT_CONTAINER_OBJECT | array($object, "alias")
CONTROLLER_INFO_CONTROLLER | "controller name"
CONTROLLER_INFO_METHOD | "controller method"
TWIG_REQUEST_TWIG_REQUEST | array($twigRequest, "alias")

### Create a module template

The template that display the result of the controller must call the 'twigUIEnvironment' function. The module environment is directly passed as 'environment' variable.

```html
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Insert title here</title>
</head>
<body>
    {{ twigUIEnvironment(environment) }}
</body>
</html>
```

The step templates receive the arguments as defined into the TwigRequest and the additionals 'moduleName' and 'moduleChilds' variable. They must call the 'twigUIModule' function to render the childs modules.

```html
<!-- module : {{ moduleName }} -->
<div>
    <p>{{ argument }}</p>
    
    {{ twigUIModule(moduleChilds) }}
</div>
```