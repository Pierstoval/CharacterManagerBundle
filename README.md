[![CircleCI](https://circleci.com/gh/Pierstoval/CharacterManagerBundle.svg?style=svg)](https://circleci.com/gh/Pierstoval/CharacterManagerBundle)
[![Build Status](https://travis-ci.org/Pierstoval/CharacterManagerBundle.svg?branch=master)](https://travis-ci.org/Pierstoval/CharacterManagerBundle)
[![Coverage Status](https://coveralls.io/repos/github/Pierstoval/CharacterManagerBundle/badge.svg?branch=master)](https://coveralls.io/github/Pierstoval/CharacterManagerBundle?branch=master)

# Character manager bundle

You can configure your steps both from configuration and services.

## Setup

* Install the bundle
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
  which already implements the different methods AND is already a Doctrine ORM entity for which you just have to add your
  own `id` property.
* Load the routing file:

   ```yaml
    generator_steps:
        resource: "@PierstovalCharacterManagerBundle/Controller/StepController.php"
        type:     annotation
        prefix:   /character_generator/    # Or any prefix you like, actually
   ```

  This routing file is important because it is the place where character generation will be handled.
* You're set for the base setup!<br>Now you have to create your Step actions, for you to be able to generate a character

## Character generation


### Step actions

To generate characters, you need what I call _Step Action_ classes.

One generation step = one class.

Each class must implement `StepActionInterface`, but you can also extend the abstract class `StepAction` which already
 implements the interface and just misses the `execute()` method that you have to implement yourself.

You can define it at a simple class like this:

```yaml
pierstoval_character_manager:
    character_class: 'AppBundle\Entity\Character'
    steps:
        step_01:
            action: AppBundle\StepAction\Step1

services:
    app.steps.step_1:
        class: AppBundle\Step\Step01
        tags: [{ name: pierstoval_character_step }]
```

Or as a service like this, by adding the `pierstoval_character_step` tag:

**Note:** Tagged services will work **only if they extend** the abstract class, not only the interface.

```yaml
pierstoval_character_manager:
    character_class: 'AppBundle\Entity\Character'
    steps:
        step_01:    # Step name, mandatory to reference steps in the app, and must be unique
            action: app.steps.step_1   # This is the service name

services:
    app.steps.step_1:
        class: AppBundle\Step\Step01   # Extends abstract StepAction class
        tags: [{ name: pierstoval_character_step }]
```

### Important things about steps

Step configuration reference:

```
pierstoval_character_manager:
    steps:
        name:
            action: ~  # Required
            label:  '' # "Humanized" step name by default, but you can use a translation key too 

            # Steps that the current step may depend on. If step is not set in session, will throw an exception.
            depends_on: []

            # When this step will be updated, it will clear values for specified steps.
            onchange_clear: []
```

* Step name must be **unique**, you can refer to it in the application, so be sure it is verbose enough for you, and
  more informative than just "Step 1", "Step 2", etc.
* **Steps order matter**! The step number starts at 1 (not zero) and if you change the order of a step, the whole order
  will change. Keep this in mind when using the `StepAction::goToStep($stepNumber)` method (see below).
* If you define your steps as services and use the `pierstoval_character_step` tag, your actions **must** extend the
  abstract `StepAction` class (it has already been said before, but hey, this is important, else your Action class
  will not be injected the needed services).
* The `onchange_clear` parameter is only handled in the abstract `StepAction` class, but you can implement it manually
  in your `StepAction::execute()` methods for example.

### `StepAction` class

This is an example of a basic action:

```php
<?php

namespace AppBundle\Step;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction;

class Step1 extends StepAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Must return a `Response` object
    }
}
```

#### What is injected in the StepAction class

When using the abstract class and if the action is **defined as a service**, other services will be injected.

And the abstract class has cool new methods, too.

##### Constructor

First, you must know that the StepAction has **no constructor**.

Why?

Because you're free to have your own constructor without being forced to rely on the parent's logic.

The abstract class only adds some nice stuff to use (and if someone don't extend it, send me a message, I'd like to hear
  why you don't want to extend it), and this cool logic resides in other methods.
  
So you're free to implement your own constructor, **especially if you define your action as a service**

#### Injected services

If you define the step action as a service and extend the abstract class, you will have access to four services:

```php
/** @var EntityManager */
$this->em;

/** @var TwigEngine */
$this->templating;

/** @var RouterInterface */
$this->router;

/** @var TranslatorInterface */
$this->translator;
```

Most of the time, you don't need many other things, but if you need other things, just add the `arguments:` or `calls:`
  options to your service definition.

#### The cool methods of the StepAction class

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

The project is published under GPL license. See the [license file](LICENSE) for more information.
