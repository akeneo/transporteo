Contributor
-----------

:heart_eyes: Thanks for taking the time to contribute! You're awesome! :heart_eyes:

There is a tons of helpers availables through a Makefile, just type `make list`

In addition of usage requirements you need to install `dot` to be able to print the workflow.

For test purpose you need two additional databases `akeneo_pim_one_seven_for_test` filled by an Akeneo PIM CE 1.7
and `akenoe_pim_two_for_test` filled by a Akeneo PIM CE 2.0.

Please run CS-fixer, PHPSpec and PHPUnit before committing. (`make commit` if you want to use the Makefile)

```
  $ php ./vendor/bin/php-cs-fixer fix --config=./.php_cs.php
  $ php ./vendor/bin/phpspec run
  $ php ./vendor/bin/phpunit
```

If you have updated the workflow, please update the generated graph:

```
  $ php MigrationTool.php state-machine:dump
  $ dot -Tpng stateMachineMigrationTool.dot -o stateMachineMigrationTool.png
```

## HOW IT WORKS BEHIND THE SCENE

This project tries to respect Hexagonal Architecture (Ports and Adapters), if you want to discover more about that, you can
check these links (by Alistair Cockburn (inventor of the concept Hexagonal architecture)
- [Video 1](https://www.youtube.com/watch?v=th4AgBcrEHA)
- [Video 2](https://www.youtube.com/watch?v=iALcE8BPs94)
- [Video 3](https://www.youtube.com/watch?v=DAe0Bmcyt-4)


Concretly, our ports for us are in the Domain layer meanwhile Adapters are in the Infrastructure layer.

Each steps is represented as a folder in Domain

## The State Machine

The MigrationTool is like managing a whole state of a migration processus, this is the only thing that the tool
is able to do: Migrate.

So, the whole software is a state machine that goes from one state/step to another.

### What is a state machine?

A state machine can be seen as a directed graph like a workflow, edges are called "Transitions" and node are called "States".
A state is a state, nothing is happening when a state is marked.

It is only during transitions (so from one state to another) that something can happen.

A state machine can only handle one state at a time but you can have several transitions availables from one state.
To make a choice, there is a system called a "guard". A guard is the responsability of a transition to accept or not to be crossed.

To be able to implement quickly a state-machine pattern, The Symfony's workflow component has been choosed to benefits from
the Symfony environment which is well known at Akeneo.

Some links to understand how works the Symfony Workflow component
- [Official documentation](https://symfony.com/doc/current/components/workflow.html)
- [Video tutorials](https://www.google.com)

The MigrationToolStateMachine is the object handled by the state machine but also the state machine itself. Indeed, this state
machine is able to go from one step to another directly and so it is also where all the context information gathered during
the migration process are stored.

We differentiate business steps and technical steps in the state machine and the way we created our class to listen to workflow events 
have been created reflects business steps like "FromDestinationPimStructureMigratedToDestinationPimFamilyMigrated".

## The dependency injection

The dependency injection is also handled by Symfony with the usage of their new features the "autowiring" and "autoconfigure"

The container is build in [ContainerBuilder.php](../src/Infrastructure/Common/ContainerBuilder.php)

Here is some links to understand and see how it works Symfony Framework is not used in this project:
- [Symfony Autowiring](https://symfony.com/doc/current/service_container/autowiring.html)
- [Changes](https://symfony.com/doc/current/service_container/3.3-di-changes.html)
- [Symfony autoconfiguration](https://symfony.com/blog/new-in-symfony-3-3-service-autoconfiguration)


### Source Pim and Destination Pim management

Source Pim designates the Pim we want to migrate from and Destination Pim designates the Pim we want to migrate to.

For development purpose both Pim are installed locally with Mysql 5.7 but the goal is:

For the SourcePim:
- Locally
- On Server (through SSH)

For the Destination Pim:
- Locally
- Installed by the tool through docker-compose


The rule is : "THE SOURCE PIM SHOULD NEVER BE TOUCHED".
