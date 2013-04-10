Step 3: Import DABSquaredPushNotificationsBundle routing
=======================================
Import the bundle routing:

``` yaml
DABSquaredPushNotificationsBundle:
    resource: "@DABSquaredPushNotificationsBundle/Resources/config/routing.yml"
    prefix:   /push


> The defaults configuration may not be necessary unless you have
> changed FOSRestBundle's default format.