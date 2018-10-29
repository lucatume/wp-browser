## Third party Plugins
This folder should contain any third-party plugin that should be copied over in `/wp-content/plugins` folder in the container.  
The plugins are copied in the container, not bound with a `volume` directive hence any change made to the code locally will not affect the code in the container and vice-versa.

## Third party themes
The `themes` folder should contain any third-party themes that should be copied over in `/wp-content/themes` folder in the container.  
The plugins are copied in the container, not bound with a `volume` directive hence any change made to the code locally will not affect the code in the container and vice-versa.  

> Keep in mind required plugins and themes will not apply when using the local stack.

## Updating the plugins
Update the third-party plugins adding or modifying them and rebuild the container image using:
```bash
docker-compose -f ci-stack.yml build
```
