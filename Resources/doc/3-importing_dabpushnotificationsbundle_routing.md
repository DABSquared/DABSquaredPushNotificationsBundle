Step 3: Import DABSquaredPushNotificationsBundle routing
=======================================
Import the bundle routing:

``` yaml
DABSquaredPushNotificationsBundle:
    resource: "@DABSquaredPushNotificationsBundle/Resources/config/routing.yml"
    prefix:   /push

DABSquaredPushNotificationsBundle_Safari:
    resource: "@DABSquaredPushNotificationsBundle/Resources/config/routing_safari.yml"

DABSquaredPushNotificationsBundle_Admin:
    resource: "@DABSquaredPushNotificationsBundle/Resources/config/routing_admin.yml"

> The defaults configuration may not be necessary unless you have
> changed FOSRestBundle's default format.
```

[Step 4: Integration with FOSUserBundle](4-integrating_with_FOSUserBundle.md).
