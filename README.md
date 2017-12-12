[![Build Status](https://travis-ci.org/Pierstoval/CharacterManagerBundle.svg?branch=master)](https://travis-ci.org/Pierstoval/CharacterManagerBundle)
[![Coverage Status](https://coveralls.io/repos/github/Pierstoval/CharacterManagerBundle/badge.svg?branch=master)](https://coveralls.io/github/Pierstoval/CharacterManagerBundle?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/566f9019-3d3e-4606-95e6-0ab84ab0f735/mini.png)](https://insight.sensiolabs.com/projects/566f9019-3d3e-4606-95e6-0ab84ab0f735)

# Character manager bundle

This bundle is here to provide a customizable skeleton to create characters based on a list of actions representing each
step of character creation.

You can configure your steps both from configuration and services.

## Setup

* Install the bundle<br>
  If you are using Symfony with Flex, all you have to do is `composer require pierstoval/character-manager`.<br>
  If you do not have Flex, please refer to the next steps.
* Add it to your Kernel:

   ```php
   <?php
   class AppKernel extends Kernel
   {
       public function registerBundles()
       {
           $bundles = [
               // Sorry for the very long name.
               new Pierstoval\Bundle\CharacterManagerBundle\PierstovalCharacterManagerBundle(),
           ];
       }
   }
   ```

* Create a `Character` class (for now, only one is allowed per app. FOSUserBundle's style, sorry about that guys, but
  it's not very relevant for now to have more than one character class)

   ```php
   <?php
   namespace AppBundle\Entity;
  
   use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;
 
   class Character implements CharacterInterface {
       // Implement interface methods
   }
   ```

* **Pro tip:** You can also extend the abstract class `Pierstoval\Bundle\CharacterManagerBundle\Entity\Character`,
  which already implements the different methods AND is already a Doctrine ORM Entity for which you just have to add
  your own `id` property.
* Load the routing file:
  ```yaml
   generator_steps:
       resource: "@PierstovalCharacterManagerBundle/Resources/config/routing.yaml"
       prefix:   /character_generator/    # Or any prefix you like
  ```
  This routing file is important because it is the place where character generation will be handled.<br>
  **Note:** You can add the `{manager}` route option to your url prefix, when using multiple character managers.
* You're set for the base setup!<br>Now you have to create your Step actions, for you to be able to generate a character

## Character generation

### Step actions

To generate characters, you need what are called _Step Action_ classes.

One generation step = one class.

Each class must implement `StepActionInterface`, but you can also extend the abstract class `AbstractStepAction` which 
  implements the interface and adds cool logic, so you just have to implement the `execute()` method.

You can define it at a simple class like this:

```yaml
pierstoval_character_manager:
    managers:
        default:
            character_class: 'AppBundle\Entity\Character'
            steps:
                step_01:
                    action: App\Step\Step01
```

You can also refer to an already existing service:

```yaml
pierstoval_character_manager:
    managers:
        default:
            character_class: 'AppBundle\Entity\Character'
            steps:
                step_01:
                    action: app.steps.step_1

services:
    app.steps.step_1:
        class: App\Step\Step01
        arguments:
            - ...
```

**💯 Note:** You should know that **all action classes** that are **not set as service** will be **defined as service**,
 autowired and set private, because it is mandatory for the `ActionRegistry` to have them as services.<br>
However, **your services will be untouched**, to keep consistency with your own logic.

💠 Magic 

### Important things about steps

Step configuration reference:

```
pierstoval_character_manager:
    managers:
        # Prototype
        name:
            character_class:      ~ # Required
            steps:
                # Prototype
                name:
                    # Can be a class or a service. Must implement StepActionInterface or extend abstract Action class.
                    action:               ~ # Required
                    label:                ''
                    # Steps that the current step may depend on. If step is not set in session, will throw an exception.
                    dependencies:           []
                    # When this step will be updated, it will clear values for specified steps.
                    # Only available for the abstract class
                    onchange_clear:       []
```

* Step name must be **unique** for each character manager. You can refer to it in the application, so be sure it is
  verbose enough for you, and more informative than just "Step 1", "Step 2", etc.
* **Steps order matter**! The step number starts at 1 and if you change the order of a step, the whole order will change.
  Keep this in mind when using the `AbstractStepAction::goToStep($stepNumber)` method (see below).
* The `onchange_clear` parameter is only handled in the abstract `AbstractStepAction` class, but you can implement it
  manually in your `StepActionInterface::execute()` method for example.

### `AbstractStepAction` class

This is an example of a basic action:

```php
<?php

namespace AppBundle\Step;

use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction;
use Symfony\Component\HttpFoundation\Response;

class Step1 extends AbstractStepAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(): Response
    {
        // Implement your step logic here.
        
        return new Response('This step is now rendered');
    }
}
```

#### What is injected in the AbstractStepAction class

When you have defined all your character managers configurations, the `StepsPass` will process them and execute certain
  actions:

* Check if your action is an existing class extending `StepActionInterface`.<br>
  If it exists and is not defined as a service, it will:
  * Define it as a service
  * Make the service `private`, `autowired` and `lazy`.

  If it is already defined as a service, it will not do something else than the next steps:
* For all actions, now they should be defined as services, so the compiler pass will process them:
  * If it extends the `AbstractStepAction` class, it will also inject:
    * The router if available (via the `RouterInterface`)
    * The entity manager if available (it actually injects the `ObjectManager`, so works with both ORM and ODM).
    * The translator (it should already be available via `TranslatorInterface`, even if not enabled in the framework).
    * The Twig environment, if available.
  * It will inject the step configuration:
    * The `character_class` option
    * The `Step` object, retrieved from the `StepActionResolver`
    * All steps from this manager, again retrieved from the `StepActionResolver`. They're mostly used to manage the 
    `goToStep()` and `nextStep()` methods in the abstract action class.
    * Add the action to the `ActionRegistryInterface` service registered in the container.
    
And the abstract class has cool new methods, too.

##### Constructor

First, you must know that the AbstractStepAction has **no constructor**.

Then, you're free to have your own constructor without being forced to rely on the parent's logic, and inject all your
  necessary services and parameters in the constructor, via autowiring. 

The abstract class only adds some nice stuff to use (and if someone don't extend it, send me a message, I'd like to hear
  why you don't want to extend it), and this cool logic resides in other methods.
  
So you're free to implement your own constructor, **especially if you define your action as a service**

#### Injected services

If you define the step action as a service and extend the abstract class, you will have access to four services:

```php
/** @var EntityManager */
$this->em;

/** @var Twig\Environment */
$this->twig;

/** @var RouterInterface */
$this->router;

/** @var TranslatorInterface */
$this->translator;
```

Most of the time, you don't need many other things, but if you need other things, just add the `arguments:` or `calls:`
  options to your service definition.

#### The cool methods of the AbstractStepAction class

I won't talk about the methods that have to be implemented by the interface.
Just [look at the Interface's code](Action/StepActionInterface.php) if you need, comments are enough.

Here I'm just gonna talk about the **abstract** class and the methods it adds:

```php
<?php
// Get the character property for specified step name (or current step by default)
$this->getCharacterProperty($stepName = null);

// Returns a RedirectResponse object to the next step
$this->nextStep();

// Returns a RedirectResponse object to the specified step, but by number, not by name
$this->goToStep($stepNumber);

// A cool tool to use flash messages. By default, the "type" is "error".
// All flash messages are translated and the translation domain is set in the StepAction::$translationDomain static property.
// Of course you can override the translation domain if you create your own abstract StepAction class and override this var.
$this->flashMessage($msg, $type = null, array $msgParams = []);

// Updates the current character step value and sets it to $value
// This is the method that makes sure that "onchange_clear" steps are cleared with a simple "unset()" in the session
$this->updateCharacterStep($value);
```

## Roadmap

A list of "todos": 

* Add a factory, a service, or whatever, which goal will be to handle converting the final step submission into a
  proper `Character` object. Abstract, obviously, because it has to be implemented manually by the user.
* Add a `ServiceStepAction` interface or something similar for better injection system, because for now, compiler pass &
  injections are not very intuitive.
* Create tests for the controller class
* Try to find a way to reduce the size of the controller class
* Maybe propose some form types, services, or find cool stuff that makes things easier
* Create lots of character managers for different games so we find the flaws of this bundle and rip them off! (help
  appreciated 😁 )

## License

The project is published under MIT license. See the [license file](LICENSE) for more information.
