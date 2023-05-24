# Code Generator

The code generator can create PHP classes based on the database tables. It can create the following classes:

* Model
* Repository
* Basic CRUD Rest API (GET, POST, PUT)
* Functional Test for the CRUD API

## How to use

The code generator is a command line tool. You can run it using the command:

```bash
APP_ENV=dev composer run codegen -- --table <table> [--save] <class> [--debug]
```

The command above will connect to your database and create the classes based on the table `<table>`.

You can specify which classes you want to create using the parameter `<class>`. The possible values are:

* `model` - Create the model class
* `repo` - Create the repository class
* `config` - Create the configuration class
* `rest` - Create the REST API
* `test` - Create the functional test for the REST API
* `all` - Create all classes

If the parameter `--save` is specified, the classes will be saved to the file in your project folder. It will overwrite existing content you might have. Be cautious. If you don't specify the parameter, the classes will be printed to the console. The `config` class will be printed to the console regardless of the `--save` parameter.

If you specify the parameter `--debug`, the code generator will print the array with the table structure.

## Customizing the code generator

You can change the existing templates or create your own templates. The templates are located in the folder `templates/codegen`. It uses the [Jinja template engine for PHP](https://github.com/byjg/jinja_php).
