# Migration Tool

## Install

A docker-compose is present to help you to setup everything but the tool does not need it to work.

```
 docker-compose run php php MigrationTool.php akeneo-pim:migrate
```

Also there is a Makefile to help to do some stuff:

```
  make update
  make launch
```

### Requirements

- php-7.1
- php7.1-gmp
- composer
- ssh-client
 

Run the tool !

```
    composer update
    php MigrationTool.php akeneo-pim:migrate
```


## Developer part:

If you use docker you have a tons of helpers, just type `make list`

In addition of usage requirements you need to install `dot` to be able to print the workflow.

Please run CS-fixer, PHPSpec and PHPUnit before commit. (`make commit` if you use docker)

## Troubleshooting

As the test of the parts using SSH are complicated we are not sure that your configuration fits our usage.
