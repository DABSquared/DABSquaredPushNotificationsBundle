# DABSquared Push Notifications (Dashboard Data) #

### `POST` /push/data/app_backgrounded_graph ###

_Gets the data to be converted for the graph_

#### Requirements ####


#### Parameters ####

device_state:

  * type:
  * required: true
  * description: What device state to grab

internal_app_ids:

  * type:
  * required: true
  * description: The internal app ids to see information for.

device_types:

  * type:
  * required: true
  * description: The device types to show data for

start_date:

  * type:
  * required: false
  * description: Start date

end_date:

  * type:
  * required: false
  * description: End date


### `POST` /push/data/app_open_graph ###

_Gets the data to be converted for the graph_

#### Requirements ####


#### Parameters ####

device_state:

  * type:
  * required: true
  * description: What device state to grab

internal_app_ids:

  * type:
  * required: true
  * description: The internal app ids to see information for.

device_types:

  * type:
  * required: true
  * description: The device types to show data for

start_date:

  * type:
  * required: false
  * description: Start date

end_date:

  * type:
  * required: false
  * description: End date


### `POST` /push/data/app_terminated_graph ###

_Gets the data to be converted for the graph_

#### Requirements ####


#### Parameters ####

device_state:

  * type:
  * required: true
  * description: What device state to grab

internal_app_ids:

  * type:
  * required: true
  * description: The internal app ids to see information for.

device_types:

  * type:
  * required: true
  * description: The device types to show data for

start_date:

  * type:
  * required: false
  * description: Start date

end_date:

  * type:
  * required: false
  * description: End date



# DABSquared Push Notifications (Deprecated) #

### `POST` /push/device/app_open ###

_Registers that an iOS app opened_

#### Requirements ####


#### Parameters ####

device_identifier:

  * type:
  * required: true
  * description: The device identifier you defined.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.



# DABSquared Push Notifications (GCM) #

### `POST` /push/device/gcm/app_open ###

_Registers that a GCM app opened_

#### Requirements ####


#### Parameters ####

device_token:

  * type:
  * required: true
  * description: The registration id returned from GCM

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.


### `POST` /push/device/gcm/register ###

_Registers an Android GCM Device By Registration ID_

#### Requirements ####


#### Parameters ####

device_token:

  * type:
  * required: true
  * description: The registration id returned from GCM

device_identifier:

  * type:
  * required: true
  * description: The vendor device identifier of the Android device.

is_sandbox:

  * type:
  * required: true
  * description: Whether or not this app is using googles sandbox

device_name:

  * type:
  * required: true
  * description: The name of the registering device.

device_model:

  * type:
  * required: true
  * description: The model of the registering device.

device_version:

  * type:
  * required: true
  * description: The iOS version of the registering device.

app_name:

  * type:
  * required: true
  * description: The name of the app registering.

app_version:

  * type:
  * required: true
  * description: The version of the app that is registering.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.


### `POST` /push/device/gcm/unregister ###

_Unregisters an Android GCM Device_

#### Requirements ####


#### Parameters ####

device_identifier:

  * type:
  * required: true
  * description: The vendor device identifier of the Android device.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.



# DABSquared Push Notifications (Safari) #

### `POST|GET|DELETE` /v1/devices/{deviceToken}/registrations/{websitePushID} ###

_Registers A Safari Device_

#### Requirements ####

**deviceToken**

**websitePushID**



### `POST` /v1/log ###

_Logs a safari registration error to monolog._


### `POST|GET` /v1/pushPackages/{websitePushID} ###

_Download Payload For Safari Push_

#### Requirements ####

**websitePushID**




# DABSquared Push Notifications (iOS) #

### `POST` /push/device/ios/app_backgrounded ###

_Registers that an iOS app backgrounded_

#### Requirements ####


#### Parameters ####

device_identifier:

  * type:
  * required: true
  * description: The device identifier you defined.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.


### `POST` /push/device/ios/app_open ###

_Registers that an iOS app opened_

#### Requirements ####


#### Parameters ####

device_identifier:

  * type:
  * required: true
  * description: The device identifier you defined.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.


### `POST` /push/device/ios/app_terminated ###

_Registers that an iOS app terminated_

#### Requirements ####


#### Parameters ####

device_identifier:

  * type:
  * required: true
  * description: The device identifier you defined.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.


### `POST` /push/device/ios/register ###

_Registers an iOS Device By Device Token_

#### Requirements ####


#### Parameters ####

device_token:

  * type:
  * required: true
  * description: The device token returned from Apple.

is_sandbox:

  * type:
  * required: true
  * description: Whether or not this is using apples sandbox

badge_allowed:

  * type: (0|1|false|true)
  * required: true
  * description: Whether or not the user has allowed badges on the app.

sound_allowed:

  * type: (0|1|false|true)
  * required: true
  * description: Whether or not the user has allowed sounds on the app.

alert_allowed:

  * type: (0|1|false|true)
  * required: true
  * description: Whether or not the user has allowed alerts on the app.

device_name:

  * type:
  * required: true
  * description: The name of the registering device.

device_model:

  * type:
  * required: true
  * description: The model of the registering device.

device_version:

  * type:
  * required: true
  * description: The iOS version of the registering device.

app_name:

  * type:
  * required: false
  * description: The name of the app registering.

app_version:

  * type:
  * required: false
  * description: The version of the app that is registering.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.

device_identifier:

  * type:
  * required: true
  * description: The vendor device identifier of the iOS device.


### `POST` /push/device/ios/unregister ###

_Unregisters an iOS Device_

#### Requirements ####


#### Parameters ####

device_identifier:

  * type:
  * required: true
  * description: The vendor device identifier of the iOS device.

app_id:

  * type:
  * required: true
  * description: The internal app id that is registered in the Symfony 2 config.