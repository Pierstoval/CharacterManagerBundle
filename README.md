# Character manager bundle

You can configure your steps both from configuration and services.

:warning: **Warning!** Be careful: configuration is processed **before** services,
 and services' configuration can (and will, if step name exists) override normal
 configuration.

Todos:

* Correctly use the "depends_on" feature.
* Correctly disable steps on change
