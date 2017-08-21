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
