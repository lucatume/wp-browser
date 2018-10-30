# WPBrowser CI Docker container
>The container stack in this folder is meant for CI usage and might not be the best way to locally develop your project: please keep that in mind.
 
## Stack overview
The stack contains all you should need to run an enclosed testing environment.
It's defined in the `ci-stack.yml` (`local-stack.yml` for a local development environment).  
The stack contains:
* WordPress with PHP 5.6 and Apache and, optionally, XDebug
* A Chromedriver and Chrome dedicated container to run the acceptance tests
* A database container with MySQL
* A Mailhog container to test emails
* An Adminer container to manage the database

## Using the stack 
### Using the stack in CI
Clone or copy any required third-party plugins in the `wordpress/plugins` folder and any required theme in the 
`wordpress/themes`; these will be copied, during the build phase, in the container and put in place by 
the entry script; if any one of those plugins or themes needs a "build" phase (e.g. `composer install`) then 
take care of that before starting the stack.
Start the stack using
```bash
start ci
```
The stack will serve WordPress on `http:://localhost:8081` by default, from within the `docker` folder a number of binary 
wrapper are available to run them in the container.  
Browse to the [stack welcome page](http://localhost:8081/stack/welcome.html) page to find out more about the local stack and its setup.  

### Using the stack for local development
Start the stack using the `start` script:
```bash
start local
```
Use the `-h` option to have more information about supported options:
```bash
start -h
```
Browse to the [stack welcome page](http://localhost:8080/stack/welcome.html) page to find out more about the local stack and its setup.  

### Stopping the stack
You can stop the stack using the `docker-compose -f <stack>-stack.yml stop` command or by using the `stop <stack>` command.

### Building the stack
The `docker` folder contains a `build` command that will build the WordPress image; it's just a wrapper around the `docker-compose` command.

### Changing the WordPress image contents in CI context
The WordPress docker image will be built on the first `docker-compose up`, or `start`, run.  
By default the image will copy, and **not bind**, the third-party plugins available in the `wordpress/plugins` folder and the themes in the `worpress/themes` folder.  
Read more about the difference between volumes and copies [here](http://coderbro.com/docker/2017/10/24/docker-volumes-vs-copy.html).  
When the contents of the third-party plugins or themes change you should stop the stack using `stop local` or `stop ci` and rebuild the WordPress image using `build`.

## Interacting with stack
### Accessing the container database
The container database can be reaced on `localhost:4406`, username `root`, password `root`.  
Four your convenience the MySQL client command is available as a binary in the `docker` folder called `stack-mysql`; you can use it 
to show, as an example, the current tables like this:
```bash
mysql "use wordpress; show tables;"
```
The command is **not** using the `wordpress` database by default.

### Using the container WP-CLI binary
The container PHP version might not match the one you run on your local installation and installed package versions 
might differ. To get around this you can the use the WordPress container own [WP-CLI](https://wp-cli.org/) 
using the `docker/stack-cli` wrapper; as an example:
```bash
stack-cli --version
```
will return the version of WP-CLI installed in the WordPress container.

### Using the container Composer binary
The container PHP version might not match the one you run on your local installation and installed package versions 
might differ. To get around this you can the use the WordPress container own [Composer](https://getcomposer.org/) 
using the `docker/stack-composer` wrapper; as an example:
```bash
stack-composer --version
```
will return the version of Composer installed in the WordPress container.

### Using the container Codeception binary
You can call the container `stack-codecept` binary, and run it **inside** the container, using the `tests/docker/stack-codecept` 
wrapper; commands after `stack-codecept` will be forwarded to the container binary; as an example:
```bash
stack-codecept run acceptance
```
will run the `acceptance` suite **in the container**.

### Opening a bash prompt in a container
The `bash-into` binary in the `docker` folder will open a `bash` prompt into a container; when the container name is not specified the command will open into the `WordPress` container:
```bash
bash-into
```
If the container name is specified using the container name in the stack file then the bask will open in the working directory of that container:
```bash
bash-into wordpress
bash-into adminer
```
